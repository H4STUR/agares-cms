<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterList;
use App\Models\Newsletter\NewsletterSubscriber;
use App\Models\Setting;
use App\Services\Newsletter\AgaresNewsletterApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Public newsletter signup endpoint.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email'   => ['required', 'email:rfc', 'max:255'],
            'name'    => ['nullable', 'string', 'max:255'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => __('You must accept the newsletter terms to subscribe.'),
        ]);

        $email = strtolower(trim($validated['email']));

        $existing = NewsletterSubscriber::where('email', $email)->first();

        // Already subscribed and active — block duplicate.
        if ($existing && $existing->status === NewsletterSubscriber::STATUS_ACTIVE) {
            return $this->redirectBack($request, 'success', __('You are already subscribed.'));
        }

        $consentText = (string) $request->input(
            'consent_text',
            __('I agree to receive the newsletter and accept the privacy policy.')
        );

        $now = now();

        if ($existing) {
            // Resubscribe / refresh consent for any non-active record (pending, unsubscribed, bounced, complained).
            $existing->fill([
                'name'               => $validated['name'] ?? $existing->name,
                'status'             => NewsletterSubscriber::STATUS_ACTIVE,
                'source'             => $existing->source ?: NewsletterSubscriber::SOURCE_WEBSITE,
                'consent_text'       => $consentText,
                'consent_ip'         => $request->ip(),
                'consent_user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'subscribed_at'      => $now,
                'confirmed_at'       => $now,
                'unsubscribed_at'    => null,
            ]);

            if (empty($existing->unsubscribe_token)) {
                $existing->unsubscribe_token = NewsletterSubscriber::generateToken();
            }

            $existing->save();
            $subscriber = $existing;
        } else {
            $subscriber = NewsletterSubscriber::create([
                'email'              => $email,
                'name'               => $validated['name'] ?? null,
                'status'             => NewsletterSubscriber::STATUS_ACTIVE,
                'source'             => NewsletterSubscriber::SOURCE_WEBSITE,
                'consent_text'       => $consentText,
                'consent_ip'         => $request->ip(),
                'consent_user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'subscribed_at'      => $now,
                'confirmed_at'       => $now,
            ]);
        }

        // Attach to default list if one is configured.
        if ($defaultList = NewsletterList::defaultList()) {
            $subscriber->lists()->syncWithoutDetaching([$defaultList->id]);
        }

        return $this->redirectBack($request, 'success', __('Thanks! You are now subscribed to our newsletter.'));
    }

    /**
     * Public unsubscribe — token-based (no auth needed).
     */
    public function unsubscribe(Request $request, string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return response()->view('pages.frontend.newsletter.unsubscribe', [
                'subscriber' => null,
                'success'    => false,
                'message'    => __('This unsubscribe link is invalid or has expired.'),
            ], 404);
        }

        if ($subscriber->status !== NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->status          = NewsletterSubscriber::STATUS_UNSUBSCRIBED;
            $subscriber->unsubscribed_at = now();
            $subscriber->save();
        }

        // Phase 4.4 — fire-and-forget push to SaaS suppression list.
        // Failures NEVER block the local unsubscribe — the user is already unsubscribed in CMS.
        $this->pushSuppressionToSaaS($subscriber);

        return view('pages.frontend.newsletter.unsubscribe', [
            'subscriber' => $subscriber,
            'success'    => true,
            'message'    => __('You have been unsubscribed.'),
        ]);
    }

    /**
     * Best-effort suppression sync to SaaS. Stores the outcome on the subscriber
     * row but NEVER throws — local unsubscribe is the source of truth.
     */
    private function pushSuppressionToSaaS(NewsletterSubscriber $subscriber): void
    {
        if (Setting::str('newsletter_sending_driver', 'disabled') !== 'external_api') {
            return;
        }

        try {
            /** @var AgaresNewsletterApiClient $client */
            $client = app(AgaresNewsletterApiClient::class);
            if (!$client->isConfigured()) {
                return;
            }

            $result = $client->syncSuppression(
                email:              $subscriber->email,
                reason:             'unsubscribed',
                sourceSubscriberId: (string) $subscriber->id,
                metadata:           [
                    'cms_url'           => config('app.url'),
                    'unsubscribed_at'   => optional($subscriber->unsubscribed_at)->toIso8601String(),
                    'unsubscribe_token' => $subscriber->unsubscribe_token,
                ],
            );

            // Bookkeeping fields are optional — only write if the migration has run.
            if (\Illuminate\Support\Facades\Schema::hasColumn('newsletter_subscribers', 'external_suppression_synced_at')) {
                $subscriber->forceFill([
                    'external_suppression_synced_at'  => now(),
                    'external_suppression_sync_error' => $result['ok'] ? null : mb_substr((string) ($result['message'] ?? 'unknown'), 0, 500),
                ])->save();
            }
        } catch (\Throwable $e) {
            Log::warning('CMS suppression sync threw', [
                'subscriber_id' => $subscriber->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    private function redirectBack(Request $request, string $key, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                $key      => $key === 'success',
                'message' => $message,
            ]);
        }

        return back()->with($key, $message);
    }
}
