<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterList;
use App\Models\Newsletter\NewsletterSubscriber;
use App\Services\Newsletter\AgaresNewsletterApiClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SubscriberController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter subscribers', only: ['index', 'edit']),
            new Middleware('can:manage newsletter subscribers', only: ['create', 'store', 'update', 'destroy', 'syncSuppression']),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->toString() ?: null;
        $q      = $request->string('q')->toString() ?: null;

        $subscribers = NewsletterSubscriber::query()
            ->with('lists:id,name')
            ->status($status)
            ->search($q)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('pages.admin.newsletter.subscribers.index', [
            'subscribers' => $subscribers,
            'status'      => $status,
            'q'           => $q,
            'statuses'    => NewsletterSubscriber::STATUSES,
        ]);
    }

    public function create()
    {
        $lists = NewsletterList::orderBy('name')->get();

        return view('pages.admin.newsletter.subscribers.create', [
            'lists'    => $lists,
            'statuses' => NewsletterSubscriber::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'      => ['required', 'email:rfc', 'max:255', 'unique:newsletter_subscribers,email'],
            'name'       => ['nullable', 'string', 'max:255'],
            'status'     => ['required', Rule::in(NewsletterSubscriber::STATUSES)],
            'lists'      => ['nullable', 'array'],
            'lists.*'    => ['integer', 'exists:newsletter_lists,id'],
        ]);

        $subscriber = new NewsletterSubscriber();
        $subscriber->email  = strtolower(trim($validated['email']));
        $subscriber->name   = $validated['name'] ?? null;
        $subscriber->status = $validated['status'];
        $subscriber->source = NewsletterSubscriber::SOURCE_ADMIN;

        if ($subscriber->status === NewsletterSubscriber::STATUS_ACTIVE) {
            $subscriber->subscribed_at = now();
            $subscriber->confirmed_at  = now();
        } elseif ($subscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->unsubscribed_at = now();
        }

        $subscriber->save();

        if (!empty($validated['lists'])) {
            $subscriber->lists()->sync($validated['lists']);
        }

        return redirect()
            ->route('admin.newsletter.subscribers.index')
            ->with('success', __('Subscriber created.'));
    }

    public function edit(NewsletterSubscriber $subscriber)
    {
        $lists = NewsletterList::orderBy('name')->get();

        return view('pages.admin.newsletter.subscribers.edit', [
            'subscriber' => $subscriber->load('lists:id'),
            'lists'      => $lists,
            'statuses'   => NewsletterSubscriber::STATUSES,
        ]);
    }

    public function update(Request $request, NewsletterSubscriber $subscriber)
    {
        $validated = $request->validate([
            'email'   => ['required', 'email:rfc', 'max:255', Rule::unique('newsletter_subscribers', 'email')->ignore($subscriber->id)],
            'name'    => ['nullable', 'string', 'max:255'],
            'status'  => ['required', Rule::in(NewsletterSubscriber::STATUSES)],
            'lists'   => ['nullable', 'array'],
            'lists.*' => ['integer', 'exists:newsletter_lists,id'],
        ]);

        $previousStatus = $subscriber->status;

        $subscriber->email = strtolower(trim($validated['email']));
        $subscriber->name  = $validated['name'] ?? null;
        $subscriber->status = $validated['status'];

        // Resubscribe transition: unsubscribed -> active
        if ($previousStatus === NewsletterSubscriber::STATUS_UNSUBSCRIBED
            && $subscriber->status === NewsletterSubscriber::STATUS_ACTIVE) {
            $subscriber->unsubscribed_at = null;
            if (!$subscriber->subscribed_at) {
                $subscriber->subscribed_at = now();
            }
            if (!$subscriber->confirmed_at) {
                $subscriber->confirmed_at = now();
            }
        }

        if ($subscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED
            && !$subscriber->unsubscribed_at) {
            $subscriber->unsubscribed_at = now();
        }

        if ($subscriber->status === NewsletterSubscriber::STATUS_ACTIVE
            && !$subscriber->subscribed_at) {
            $subscriber->subscribed_at = now();
        }

        $subscriber->save();

        $subscriber->lists()->sync($validated['lists'] ?? []);

        return redirect()
            ->route('admin.newsletter.subscribers.index')
            ->with('success', __('Subscriber updated.'));
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()
            ->route('admin.newsletter.subscribers.index')
            ->with('success', __('Subscriber deleted.'));
    }

    /**
     * Phase 4.4 — manually push this subscriber to the SaaS suppression list.
     * Reuses the same client method as the public unsubscribe flow.
     */
    public function syncSuppression(NewsletterSubscriber $subscriber, AgaresNewsletterApiClient $client)
    {
        if (!$client->isConfigured()) {
            return back()->with('error', __('External newsletter API is not configured.'));
        }

        $result = $client->syncSuppression(
            email:              $subscriber->email,
            reason:             $subscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED ? 'unsubscribed' : 'manual',
            sourceSubscriberId: (string) $subscriber->id,
            metadata:           [
                'cms_url'           => config('app.url'),
                'unsubscribed_at'   => optional($subscriber->unsubscribed_at)->toIso8601String(),
                'unsubscribe_token' => $subscriber->unsubscribe_token,
                'manual_sync'       => true,
            ],
        );

        if (Schema::hasColumn('newsletter_subscribers', 'external_suppression_synced_at')) {
            $subscriber->forceFill([
                'external_suppression_synced_at'  => now(),
                'external_suppression_sync_error' => $result['ok'] ? null : mb_substr((string) ($result['message'] ?? 'unknown'), 0, 500),
            ])->save();
        }

        return back()->with(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? __('Suppression synced to SaaS.')
                : ($result['message'] ?? __('Suppression sync failed.'))
        );
    }
}
