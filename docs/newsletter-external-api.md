# Newsletter External API Integration — Agares CMS ↔ Agares SaaS

This document describes how AgaresCMS delegates newsletter bulk sending to Agares SaaS.

> **Important architecture decision:** Agares CMS is and remains queue-free. All bulk sending — queue jobs, batching, retries, Mailables, callbacks — lives on Agares SaaS. The CMS only stores subscribers/lists/campaign drafts and calls the SaaS API.

---

## Prerequisites

**On AgaresCMS:**
- Newsletter module enabled (`enable_newsletter` setting = `true`).
- Permissions assigned: `view newsletter campaigns`, `manage newsletter campaigns`, `delegate newsletter campaigns`, `sync newsletter campaigns`, `view/edit newsletter settings`, `test newsletter integration`.

**On Agares SaaS:**
- A tenant exists.
- The newsletter service is enabled for that tenant in `tenant_services` (`is_enabled = true`).
- An `agr_` API key has been created for the tenant with all four newsletter scopes:
  - `newsletter.health`
  - `newsletter.campaigns.create`
  - `newsletter.campaigns.read`
  - `newsletter.campaigns.cancel`
- A queue worker is draining the `newsletter` queue (see SaaS docs).
- Mail driver is configured (`MAIL_MAILER=smtp` in production, or `log`/Mailpit in dev).

---

## Configuring the integration

1. In CMS admin, go to **Newsletter → Settings**.
2. Set the **driver** to `external_api`.
3. Set the **SaaS API URL** to the SaaS base URL. Both forms are accepted — the client normalizes:
   - `https://www.api.agares.co.uk`
   - `https://www.api.agares.co.uk/` (trailing slash ignored)
   - `https://www.api.agares.co.uk/api` (legacy form — `/api` is stripped to avoid `/api/api/v1/...`)
4. Paste the `agr_` **API key**. The form never re-renders the saved key. To clear, submit the literal value `_clear`.
5. (Optional) Set a **project ID** — sent as the `X-Agares-Project` header for routing/multi-site contexts.
6. (Optional) Paste a **webhook secret** if callbacks should auto-update CMS state. The SaaS signs callback bodies with `hash_hmac('sha256', $body, $secret)` and sends the result as `X-Agares-Signature`. If the secret is set on CMS but not on SaaS (or vice versa), the webhook hard-fails 503/401 — never silently accept unsigned data.
7. Click **Test connection**.

### `Test connection` outcomes

| Result | Meaning |
|--------|---------|
| `Connection OK. Newsletter service is enabled.` | Healthy. Ready to delegate. |
| `Connected, but the newsletter service is NOT enabled for this tenant on the SaaS.` | API key + URL fine; tenant feature toggle off. |
| `Invalid external API key (401 Unauthorized).` | Wrong/revoked key. |
| `External API rejected the request — newsletter may be disabled for this tenant (403 Forbidden).` | Scope missing or service off. |
| `External API server error (HTTP 5xx).` | SaaS down/queue worker down. |
| `Could not reach external newsletter API: …` | Network/SSL/timeout. |

Never shows raw stack traces; the SaaS-side message is included verbatim when safe.

---

## Delegating a campaign

1. **Newsletter → Campaigns → Create**: pick a list, set subject/body (TinyMCE), save.
2. **Preview** the campaign.
3. (Optional) Send a **test email** to yourself.
4. Hit **Delegate**.

What happens under the hood:

- `ExternalApiNewsletterSender::delegateBulk()` validates the campaign (status delegatable, subject+body present, at least one list, at least one active recipient).
- `CampaignPayloadBuilder` builds the SaaS-shaped payload:
  ```json
  {
    "source": "agares_cms",
    "project_id": "...",
    "source_campaign_id": "123",
    "title": "...",
    "subject": "...",
    "body": "<html>…</html>",
    "from_name": "...",
    "from_email": "...",
    "reply_to": "...",
    "callback_url": "https://cms.example.com/newsletter/external/webhook",
    "recipients": [
      {
        "email": "a@b.com",
        "name": "Alice",
        "source_subscriber_id": "1",
        "unsubscribe_token": "abc",
        "unsubscribe_url": "https://cms.example.com/newsletter/unsubscribe/abc"
      }
    ],
    "metadata": {
      "cms_url": "...",
      "cms_app_name": "...",
      "cms_campaign_id": 123,
      "cms_campaign_url": "...",
      "cms_lists": [{"id": 1, "name": "...", "slug": "..."}]
    }
  }
  ```
- Recipient rules: **only `active` subscribers** in selected lists. `pending`/`unsubscribed`/`bounced`/`complained` always excluded. No "send to everyone" path.
- `AgaresNewsletterApiClient::sendCampaign()` POSTs to `{base}/api/v1/services/newsletter/campaigns`.
- On success (`201 Created`), CMS stores:
  - `external_campaign_id` (UUID returned by SaaS)
  - `external_status` (`queued` — SaaS already kicked off the job)
  - `delegated_at = now()`
  - `status = external_pending`
- On 422 (recipient limit / payload error), `status = external_failed` and `external_last_error` is set to the SaaS message.

---

## Syncing status

Press **Sync** on a delegated campaign to pull the current state:
- `GET {base}/api/v1/services/newsletter/campaigns/{externalCampaignId}`
- Updates `external_status`, `status` (mapped to local enum), counters (`external_sent_count`, `external_failed_count`, `external_skipped_count`, `external_accepted_count`, `external_open_count`, `external_click_count`), `external_last_synced_at`.

If a callback webhook is configured + signed correctly, this happens automatically — Sync remains available as a manual fallback.

### Status mapping

| SaaS status | CMS local status |
|-------------|------------------|
| `received` | `external_pending` |
| `queued` | `external_queued` |
| `sending` | `external_sending` |
| `sent` | `external_sent` |
| `partially_failed` | `external_partially_failed` |
| `failed` | `external_failed` |
| `cancelled` | `cancelled` |

---

## Cancelling

Press **Cancel external** on a campaign in status `external_pending`/`external_queued`/`external_sending`:
- `POST {base}/api/v1/services/newsletter/campaigns/{externalCampaignId}/cancel`
- CMS flips local status to `cancelled` and stores the SaaS-confirmed `external_status`.

If the SaaS replies 422 (campaign already terminal), the error is shown to the admin and local state is left unchanged.

---

## Webhook (optional, auto-sync)

CMS exposes `POST /newsletter/external/webhook` (CSRF-exempt). SaaS POSTs payloads here on every status transition. CMS verifies the HMAC signature using `newsletter_external_webhook_secret`; missing/invalid signature returns 401, missing config returns 503.

Expected SaaS payload (Phase 4.2 shape):

```json
{
  "external_campaign_id": "550e8400-e29b-41d4-a716-446655440000",
  "status": "sent",
  "sent_count": 118,
  "failed_count": 0,
  "skipped_count": 2,
  "accepted_recipient_count": 118,
  "requested_recipient_count": 120,
  "received_at": "...",
  "queued_at": "...",
  "sending_started_at": "...",
  "sent_at": "...",
  "cancelled_at": null,
  "failed_at": null
}
```

The handler accepts both the new (`opened_count`/`clicked_count`) and legacy (`open_count`/`click_count`) key names.

Webhook failures on the SaaS side **never** break sending — they're logged in `newsletter_delivery_logs` as `callback_failed` events.

---

## Local development

**CMS** runs on one host/port (e.g. `http://localhost:8000`).
**SaaS** runs on a second host/port (e.g. `http://localhost:8001`).
**SaaS queue worker** must be running:
```bash
php artisan queue:work --queue=newsletter --tries=1 --timeout=300
```
**SaaS mail driver** in dev: `MAIL_MAILER=log` (writes to `storage/logs/laravel.log`) or `MAIL_MAILER=smtp` pointing at Mailpit / Mailtrap.

**CMS configuration:**
- `newsletter_external_api_url` = `http://localhost:8001`
- `newsletter_external_api_key` = an `agr_` key from the SaaS tenant

**CMS does NOT need a queue worker for newsletter** — never did, never will. Only the SaaS side requires it.

---

## Production (DirectAdmin / shared hosting)

CMS: no special configuration. Just point `newsletter_external_api_url` at the SaaS API base.

SaaS only: one-line cron entry to drain the queue every minute:
```
* * * * * cd /path/to/agares-saas && php84 artisan queue:work --queue=newsletter --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

---

## End-to-end smoke test

1. CMS: enable newsletter, set driver to `external_api`, save URL + key, **Test connection** → `Connection OK. Newsletter service is enabled.`
2. CMS: create a list, add a few `active` subscribers, create a campaign attached to that list.
3. CMS: Delegate. Campaign goes to `external_pending` with an `external_campaign_id`.
4. SaaS admin (`/admin/newsletter`): the campaign appears in the tenant's list with the right `cms_url` metadata.
5. SaaS worker picks up `QueueNewsletterCampaignJob` → flips campaign to `sending` → dispatches batch jobs.
6. SaaS log / mailpit shows emails sent. Recipient rows flip to `sent`.
7. CMS: hit **Sync**. Counters appear (`sent_count`, etc.) and status moves to `external_sent`.
8. If a webhook secret is configured on both sides, status updates land in CMS without clicking Sync.
9. To test cancellation: delegate another campaign, immediately hit **Cancel external** on CMS → status flips to `cancelled` on both sides.

---

## Suppression sync (Phase 4.4)

When a subscriber unsubscribes via `/newsletter/unsubscribe/{token}` (or via the admin), CMS makes a best-effort push to the SaaS suppression list so future SaaS campaigns skip that email:

```
POST {base}/api/v1/services/newsletter/suppressions
Authorization: Bearer agr_...
```

Payload:
```json
{
  "email": "person@example.com",
  "reason": "unsubscribed",
  "source": "agares_cms",
  "source_subscriber_id": "123",
  "metadata": {
    "cms_url": "https://example.com",
    "unsubscribed_at": "...",
    "unsubscribe_token": "..."
  }
}
```

**The local CMS unsubscribe is the source of truth.** If the SaaS push fails (network error, key revoked, tenant disabled, SaaS down), the user is still successfully unsubscribed in CMS — the failure is logged + persisted on `newsletter_subscribers.external_suppression_sync_error` and the row keeps `external_suppression_synced_at` null. An admin can re-trigger the sync from the subscriber edit page via **Sync suppression to SaaS** (`POST /admin/newsletter/subscribers/{subscriber}/sync-suppression`, gated by `manage newsletter subscribers`).

The API key never appears in any of these payloads or response logs.

CMS still does **not** require a queue worker — the sync is a single synchronous HTTP call wrapped in a try/catch.

## Troubleshooting

| Symptom | Likely cause |
|---------|--------------|
| `External API endpoint or campaign not found (404)` after delegate | Wrong base URL (probably missing host/port or wrong prefix). The CMS client builds `{base}/api/v1/services/newsletter/...` — make sure `{base}` is the SaaS host root, not a path under `/api`. |
| `Invalid external API key (401 Unauthorized)` | Key revoked or pasted with whitespace. Re-generate on SaaS tenant page and paste fresh. |
| `External API rejected the request (403)` | Tenant has newsletter disabled or the key is missing the required scope. |
| Delegate succeeds, but campaign stays `external_pending`/`queued` forever | SaaS queue worker isn't running. |
| Webhook hits return 401 | Secret mismatch between CMS and SaaS. Re-paste on both sides. |
| Webhook hits return 503 | Secret is empty on CMS — set it. |
| Sync shows old counters | Worker hasn't processed yet, or `Mail::to(...)` failed at SaaS level (check `newsletter_delivery_logs`). |
