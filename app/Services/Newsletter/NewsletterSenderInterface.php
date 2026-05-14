<?php

namespace App\Services\Newsletter;

use App\Models\Newsletter\NewsletterCampaign;

/**
 * Abstraction over how a newsletter campaign gets out the door.
 *
 * Phase 2 only exposes `sendTest()` because AgaresCMS deliberately does not
 * support bulk sending from within the CMS — that responsibility is delegated
 * to the external Agares SaaS API in a later phase.
 *
 * The interface is shaped to accept that future without churn:
 *   - `supportsBulk()` lets the UI decide whether to surface "Send to list".
 *   - `delegateBulk()` exists as a contract for Phase 3 drivers (e.g.
 *     ExternalApiNewsletterSender) that hand the job to a service that owns
 *     the queue. The CMS itself never iterates the subscriber table.
 */
interface NewsletterSenderInterface
{
    /**
     * Human-readable driver key — matches `newsletter_sending_driver` setting.
     */
    public function driver(): string;

    /**
     * Whether the driver is allowed to perform any send action.
     */
    public function isEnabled(): bool;

    /**
     * Whether the driver supports bulk delivery to a subscriber list.
     * Phase 2 drivers all return false.
     */
    public function supportsBulk(): bool;

    /**
     * Send a single test email synchronously. Must not enqueue.
     *
     * @return array{success: bool, message: string}
     */
    public function sendTest(NewsletterCampaign $campaign, string $recipient): array;

    /**
     * Hand bulk delivery to whichever system owns the queue.
     * Phase 2 implementations throw — Phase 3 ExternalApiSender will implement.
     *
     * @return array{success: bool, message: string}
     */
    public function delegateBulk(NewsletterCampaign $campaign): array;
}
