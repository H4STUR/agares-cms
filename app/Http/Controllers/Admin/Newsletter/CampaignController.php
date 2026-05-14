<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Newsletter\NewsletterList;
use App\Models\Newsletter\NewsletterTemplate;
use App\Models\Setting;
use App\Services\Newsletter\AgaresNewsletterApiClient;
use App\Services\Newsletter\ExternalApiNewsletterSender;
use App\Services\Newsletter\NewsletterSenderFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CampaignController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter campaigns',         only: ['index', 'edit']),
            new Middleware('can:preview newsletter campaigns',      only: ['preview']),
            new Middleware('can:manage newsletter campaigns',       only: ['create', 'store', 'update', 'destroy', 'cancel']),
            new Middleware('can:send test newsletter campaigns',    only: ['testSendForm', 'testSend']),
            new Middleware('can:delegate newsletter campaigns',     only: ['delegate', 'cancelExternal']),
            new Middleware('can:sync newsletter campaigns',         only: ['syncStatus']),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->toString() ?: null;

        $campaigns = NewsletterCampaign::query()
            ->with(['template:id,name', 'creator:id,name', 'lists:id,name'])
            ->status($status)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('pages.admin.newsletter.campaigns.index', [
            'campaigns'  => $campaigns,
            'status'     => $status,
            'statuses'   => NewsletterCampaign::STATUSES,
            'driverKey'  => Setting::str('newsletter_sending_driver', 'disabled'),
        ]);
    }

    public function create(Request $request)
    {
        $templates = NewsletterTemplate::active()->orderBy('name')->get();
        $lists     = NewsletterList::orderBy('name')->get();

        $template = null;
        if ($request->filled('template_id')) {
            $template = NewsletterTemplate::find($request->integer('template_id'));
        }

        return view('pages.admin.newsletter.campaigns.create', [
            'templates' => $templates,
            'lists'     => $lists,
            'template'  => $template,
            'statuses'  => NewsletterCampaign::STATUSES_EDITABLE,
            'defaults'  => $this->fromDefaults(),
        ]);
    }

    public function store(Request $request)
    {
        [$validated, $listIds] = $this->validateData($request);

        $campaign = NewsletterCampaign::create($validated);
        $campaign->lists()->sync($listIds);

        return redirect()
            ->route('admin.newsletter.campaigns.index')
            ->with('success', __('Campaign saved.'));
    }

    public function edit(NewsletterCampaign $campaign)
    {
        $templates = NewsletterTemplate::active()->orderBy('name')->get();
        $lists     = NewsletterList::orderBy('name')->get();

        return view('pages.admin.newsletter.campaigns.edit', [
            'campaign'  => $campaign->load('lists:id'),
            'templates' => $templates,
            'lists'     => $lists,
            'statuses'  => NewsletterCampaign::STATUSES_EDITABLE,
            'defaults'  => $this->fromDefaults(),
        ]);
    }

    public function update(Request $request, NewsletterCampaign $campaign)
    {
        if ($campaign->isLocked()) {
            return back()->with('error', __('This campaign is locked because it has been delegated to the external sender.'));
        }

        [$validated, $listIds] = $this->validateData($request);

        $campaign->update($validated);
        $campaign->lists()->sync($listIds);

        return redirect()
            ->route('admin.newsletter.campaigns.index')
            ->with('success', __('Campaign updated.'));
    }

    public function destroy(NewsletterCampaign $campaign)
    {
        if ($campaign->isLocked()) {
            return back()->with('error', __('Delegated campaigns cannot be deleted from CMS.'));
        }

        $campaign->delete();

        return redirect()
            ->route('admin.newsletter.campaigns.index')
            ->with('success', __('Campaign deleted.'));
    }

    public function cancel(NewsletterCampaign $campaign)
    {
        if ($campaign->isLocked()) {
            return back()->with('error', __('Delegated campaigns cannot be cancelled from CMS — manage them in the external sender.'));
        }

        $campaign->update(['status' => NewsletterCampaign::STATUS_CANCELLED]);

        return back()->with('success', __('Campaign cancelled.'));
    }

    public function preview(NewsletterCampaign $campaign)
    {
        return view('pages.admin.newsletter.campaigns.preview', [
            'campaign'  => $campaign->load(['template:id,name', 'lists:id,name']),
            'driverKey' => Setting::str('newsletter_sending_driver', 'disabled'),
        ]);
    }

    public function testSendForm(NewsletterCampaign $campaign)
    {
        $sender = NewsletterSenderFactory::make();

        return view('pages.admin.newsletter.campaigns.test-send', [
            'campaign'  => $campaign,
            'sender'    => $sender,
            'driverKey' => Setting::str('newsletter_sending_driver', 'disabled'),
            'defaultTo' => optional($campaign->creator)->email
                          ?: (auth()->user()?->email ?? ''),
        ]);
    }

    public function testSend(Request $request, NewsletterCampaign $campaign)
    {
        $validated = $request->validate([
            'recipient' => ['required', 'email:rfc', 'max:255'],
        ]);

        $sender = NewsletterSenderFactory::make();

        if (!$sender->isEnabled()) {
            return back()->with('error', __('Newsletter sending is disabled. Set the newsletter sending driver in Settings to enable test emails.'));
        }

        $result = $sender->sendTest($campaign, $validated['recipient']);

        Log::info('Newsletter test send attempt', [
            'campaign_id' => $campaign->id,
            'recipient'   => $validated['recipient'],
            'driver'      => $sender->driver(),
            'success'     => $result['success'] ?? false,
        ]);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Hand the campaign off to the configured sender. With external_api driver,
     * this POSTs the campaign + recipients to the Agares SaaS newsletter API.
     */
    public function delegate(NewsletterCampaign $campaign)
    {
        $sender = NewsletterSenderFactory::make();

        if (!$sender->supportsBulk()) {
            return back()->with('error', __('The current newsletter driver does not support delegation. Switch to "external_api".'));
        }

        $result = $sender->delegateBulk($campaign);

        Log::info('Newsletter campaign delegate attempt', [
            'campaign_id'          => $campaign->id,
            'driver'               => $sender->driver(),
            'success'              => $result['success'] ?? false,
            'external_campaign_id' => $result['external_campaign_id'] ?? null,
        ]);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Pull the latest status for a delegated campaign from the SaaS.
     */
    public function syncStatus(NewsletterCampaign $campaign, AgaresNewsletterApiClient $client)
    {
        if (!$campaign->hasExternalReference()) {
            return back()->with('error', __('Campaign has not been delegated yet — nothing to sync.'));
        }

        if (!$client->isConfigured()) {
            return back()->with('error', __('External newsletter API is not configured.'));
        }

        $result = $client->getCampaignStatus((string) $campaign->external_campaign_id);

        if (!($result['ok'] ?? false)) {
            $campaign->forceFill([
                'external_last_synced_at' => now(),
                'external_last_error'     => $result['message'] ?? 'Unknown error',
            ])->save();

            return back()->with('error', $result['message'] ?? __('Status sync failed.'));
        }

        $patch = [
            'external_last_synced_at' => now(),
            'external_last_error'     => null,
        ];
        if (!is_null($result['status'] ?? null)) {
            $patch['external_status'] = $result['status'];
            $patch['status']          = self::mapExternalStatus((string) $result['status'], $campaign->status);
        }
        if (!is_null($result['sent']     ?? null)) $patch['external_sent_count']     = (int) $result['sent'];
        if (!is_null($result['failed']   ?? null)) $patch['external_failed_count']   = (int) $result['failed'];
        if (!is_null($result['skipped']  ?? null)) $patch['external_skipped_count']  = (int) $result['skipped'];
        if (!is_null($result['accepted'] ?? null)) $patch['external_accepted_count'] = (int) $result['accepted'];
        if (!is_null($result['opens']    ?? null)) $patch['external_open_count']     = (int) $result['opens'];
        if (!is_null($result['clicks']   ?? null)) $patch['external_click_count']    = (int) $result['clicks'];

        $campaign->forceFill($patch)->save();

        return back()->with('success', __('Campaign status synced from external API.'));
    }

    /**
     * Cancel a delegated campaign on the SaaS.
     */
    public function cancelExternal(NewsletterCampaign $campaign, AgaresNewsletterApiClient $client)
    {
        if (!$campaign->hasExternalReference()) {
            return back()->with('error', __('Campaign has not been delegated yet — nothing to cancel externally.'));
        }

        if (!in_array($campaign->status, NewsletterCampaign::STATUSES_EXTERNAL_CANCELLABLE, true)) {
            return back()->with('error', __('External campaign cannot be cancelled in its current status (:status).', ['status' => $campaign->status]));
        }

        if (!$client->isConfigured()) {
            return back()->with('error', __('External newsletter API is not configured.'));
        }

        $result = $client->cancelCampaign((string) $campaign->external_campaign_id);

        if (!($result['ok'] ?? false)) {
            $campaign->forceFill([
                'external_last_synced_at' => now(),
                'external_last_error'     => $result['message'] ?? 'Unknown error',
            ])->save();

            Log::info('Newsletter external cancel failed', [
                'campaign_id'          => $campaign->id,
                'external_campaign_id' => $campaign->external_campaign_id,
                'message'              => $result['message'] ?? null,
            ]);

            return back()->with('error', $result['message'] ?? __('External cancel failed.'));
        }

        $remoteStatus = $result['status'] ?? 'cancelled';
        $campaign->forceFill([
            'status'                  => self::mapExternalStatus((string) $remoteStatus, NewsletterCampaign::STATUS_CANCELLED),
            'external_status'         => $remoteStatus,
            'external_last_synced_at' => now(),
            'external_last_error'     => null,
        ])->save();

        Log::info('Newsletter external cancel succeeded', [
            'campaign_id'          => $campaign->id,
            'external_campaign_id' => $campaign->external_campaign_id,
            'remote_status'        => $remoteStatus,
        ]);

        return back()->with('success', __('External campaign cancelled.'));
    }

    /**
     * Translate a SaaS-side status string into the CMS-side local enum.
     * Kept public-static so the webhook handler can call it too.
     */
    public static function mapExternalStatus(string $external, string $fallback): string
    {
        return match (strtolower(trim($external))) {
            'received'                          => NewsletterCampaign::STATUS_EXTERNAL_PENDING,
            'queued', 'pending'                 => NewsletterCampaign::STATUS_EXTERNAL_QUEUED,
            'sending', 'in_progress'            => NewsletterCampaign::STATUS_EXTERNAL_SENDING,
            'sent', 'completed'                 => NewsletterCampaign::STATUS_EXTERNAL_SENT,
            'partially_failed', 'partial'       => NewsletterCampaign::STATUS_EXTERNAL_PARTIALLY_FAILED,
            'failed', 'error'                   => NewsletterCampaign::STATUS_EXTERNAL_FAILED,
            'cancelled', 'canceled'             => NewsletterCampaign::STATUS_CANCELLED,
            default                             => $fallback,
        };
    }

    /**
     * @return array{0: array, 1: array}  [validated attributes, list ids]
     */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'subject'     => ['required', 'string', 'max:255'],
            'body'        => ['nullable', 'string'],
            'template_id' => ['nullable', 'integer', 'exists:newsletter_templates,id'],
            'status'      => ['required', Rule::in(NewsletterCampaign::STATUSES_EDITABLE)],
            'from_name'   => ['nullable', 'string', 'max:255'],
            'from_email'  => ['nullable', 'email:rfc', 'max:255'],
            'reply_to'    => ['nullable', 'email:rfc', 'max:255'],
            'lists'       => ['nullable', 'array'],
            'lists.*'     => ['integer', 'exists:newsletter_lists,id'],
        ]);

        $listIds = $validated['lists'] ?? [];
        unset($validated['lists']);

        return [$validated, $listIds];
    }

    private function fromDefaults(): array
    {
        return [
            'from_name'  => Setting::str('newsletter_from_name', ''),
            'from_email' => Setting::str('newsletter_from_email', ''),
            'reply_to'   => Setting::str('newsletter_reply_to', ''),
        ];
    }
}
