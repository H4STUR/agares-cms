<?php

namespace Tests\Feature\Newsletter;

use App\Models\Setting;
use App\Services\Newsletter\AgaresNewsletterApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AgaresNewsletterApiClientTest extends TestCase
{
    use RefreshDatabase;

    private function configure(string $url, string $key = 'agr_test_key', string $projectId = ''): void
    {
        Setting::updateOrCreate(['key' => 'newsletter_external_api_url'], ['value' => $url, 'type' => 'string', 'category' => 'newsletter']);
        Setting::updateOrCreate(['key' => 'newsletter_external_api_key'], ['value' => $key, 'type' => 'secret', 'category' => 'newsletter']);
        Setting::updateOrCreate(['key' => 'newsletter_external_project_id'], ['value' => $projectId, 'type' => 'string', 'category' => 'newsletter']);
    }

    public function test_base_url_strips_trailing_slash(): void
    {
        $this->configure('https://saas.example.com/');
        $this->assertSame('https://saas.example.com', app(AgaresNewsletterApiClient::class)->baseUrl());
    }

    public function test_base_url_strips_legacy_api_suffix(): void
    {
        $this->configure('https://saas.example.com/api');
        $this->assertSame('https://saas.example.com', app(AgaresNewsletterApiClient::class)->baseUrl());
    }

    public function test_base_url_strips_legacy_api_suffix_with_trailing_slash(): void
    {
        $this->configure('https://saas.example.com/api/');
        $this->assertSame('https://saas.example.com', app(AgaresNewsletterApiClient::class)->baseUrl());
    }

    public function test_test_connection_hits_v1_prefix_and_reports_enabled(): void
    {
        $this->configure('https://saas.example.com');

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/health' => Http::response([
                'success'            => true,
                'service'            => 'agares-newsletter',
                'newsletter_enabled' => true,
                'tenant'             => ['id' => 1, 'name' => 'Acme'],
                'limits'             => ['campaign_recipient_limit' => 5000],
            ], 200),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->testConnection();

        $this->assertTrue($result['ok']);
        $this->assertSame('Acme', $result['tenant']['name']);
        $this->assertTrue($result['newsletter_enabled']);
    }

    public function test_test_connection_reports_disabled_when_saas_says_so(): void
    {
        $this->configure('https://saas.example.com');

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/health' => Http::response([
                'success'            => true,
                'newsletter_enabled' => false,
            ], 200),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->testConnection();
        $this->assertFalse($result['ok']);
    }

    public function test_send_campaign_reads_external_campaign_id_field(): void
    {
        $this->configure('https://saas.example.com');

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns' => Http::response([
                'success'                   => true,
                'external_campaign_id'      => '550e8400-e29b-41d4-a716-446655440000',
                'status'                    => 'queued',
                'requested_recipient_count' => 2,
                'accepted_recipient_count'  => 2,
                'skipped_count'             => 0,
            ], 201),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->sendCampaign([
            'subject' => 'x', 'body' => 'x',
            'recipients' => [['email' => 'a@b.com']],
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result['external_campaign_id']);
        $this->assertSame('queued', $result['external_status']);
        $this->assertSame(2, $result['accepted_recipient_count']);
    }

    public function test_send_campaign_friendly_error_for_401(): void
    {
        $this->configure('https://saas.example.com');

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns' => Http::response(['message' => 'bad'], 401),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->sendCampaign(['subject' => 'x', 'body' => 'x', 'recipients' => [['email' => 'a@b.com']]]);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Invalid external API key', $result['message']);
    }

    public function test_get_campaign_status_reads_phase_4_2_field_names(): void
    {
        $this->configure('https://saas.example.com');
        $id = 'abc-uuid';

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns/abc-uuid' => Http::response([
                'success'                   => true,
                'external_campaign_id'      => $id,
                'status'                    => 'sent',
                'sent_count'                => 100,
                'failed_count'              => 2,
                'skipped_count'             => 5,
                'accepted_recipient_count'  => 102,
                'opened_count'              => 30,
                'clicked_count'             => 10,
            ], 200),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->getCampaignStatus($id);
        $this->assertTrue($result['ok']);
        $this->assertSame('sent', $result['status']);
        $this->assertSame(100, $result['sent']);
        $this->assertSame(2, $result['failed']);
        $this->assertSame(5, $result['skipped']);
        $this->assertSame(102, $result['accepted']);
        $this->assertSame(30, $result['opens']);
        $this->assertSame(10, $result['clicks']);
    }

    public function test_get_campaign_status_falls_back_to_legacy_open_count(): void
    {
        $this->configure('https://saas.example.com');
        $id = 'legacy-id';

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns/legacy-id' => Http::response([
                'status'      => 'sent',
                'sent_count'  => 1,
                'open_count'  => 5,
                'click_count' => 2,
            ], 200),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->getCampaignStatus($id);
        $this->assertSame(5, $result['opens']);
        $this->assertSame(2, $result['clicks']);
    }

    public function test_cancel_campaign_posts_to_cancel_endpoint(): void
    {
        $this->configure('https://saas.example.com');
        $id = 'to-cancel';

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns/to-cancel/cancel' => Http::response([
                'success'              => true,
                'external_campaign_id' => $id,
                'status'               => 'cancelled',
                'cancelled_at'         => '2026-05-14T12:00:00+00:00',
            ], 200),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->cancelCampaign($id);
        $this->assertTrue($result['ok']);
        $this->assertSame('cancelled', $result['status']);
    }

    public function test_cancel_campaign_reports_422_when_already_terminal(): void
    {
        $this->configure('https://saas.example.com');
        $id = 'already-sent';

        Http::fake([
            'https://saas.example.com/api/v1/services/newsletter/campaigns/already-sent/cancel' => Http::response([
                'success' => false,
                'message' => "Campaign cannot be cancelled in status 'sent'.",
                'status'  => 'sent',
            ], 422),
        ]);

        $result = app(AgaresNewsletterApiClient::class)->cancelCampaign($id);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString("status 'sent'", $result['message']);
    }
}
