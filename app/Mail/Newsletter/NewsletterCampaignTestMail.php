<?php

namespace App\Mail\Newsletter;

use App\Models\Newsletter\NewsletterCampaign;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Single test email for a newsletter campaign.
 * NOT a queued mailable — Phase 2 must work without queue workers.
 */
class NewsletterCampaignTestMail extends Mailable
{
    use SerializesModels;

    public function __construct(public NewsletterCampaign $campaign) {}

    public function build(): static
    {
        $subject = '[TEST] ' . ($this->campaign->subject ?: __('Newsletter preview'));

        return $this
            ->subject($subject)
            ->view('emails.newsletter.campaign-test')
            ->with([
                'campaign' => $this->campaign,
            ]);
    }
}
