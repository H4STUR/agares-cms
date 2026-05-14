<?php

namespace App\Models\Newsletter;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class NewsletterCampaign extends Model
{
    protected $table = 'newsletter_campaigns';

    protected $guarded = [];

    public const STATUS_DRAFT                       = 'draft';
    public const STATUS_READY                       = 'ready';
    public const STATUS_TEST_SENT                   = 'test_sent';
    public const STATUS_DELEGATED                   = 'delegated';
    public const STATUS_EXTERNAL_PENDING            = 'external_pending';
    public const STATUS_EXTERNAL_QUEUED             = 'external_queued';
    public const STATUS_EXTERNAL_SENDING            = 'external_sending';
    public const STATUS_EXTERNAL_SENT               = 'external_sent';
    public const STATUS_EXTERNAL_PARTIALLY_FAILED   = 'external_partially_failed';
    public const STATUS_EXTERNAL_FAILED             = 'external_failed';
    public const STATUS_CANCELLED                   = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_TEST_SENT,
        self::STATUS_DELEGATED,
        self::STATUS_EXTERNAL_PENDING,
        self::STATUS_EXTERNAL_QUEUED,
        self::STATUS_EXTERNAL_SENDING,
        self::STATUS_EXTERNAL_SENT,
        self::STATUS_EXTERNAL_PARTIALLY_FAILED,
        self::STATUS_EXTERNAL_FAILED,
        self::STATUS_CANCELLED,
    ];

    /** Statuses an admin may set manually from the UI. */
    public const STATUSES_EDITABLE = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_CANCELLED,
    ];

    /** Statuses a campaign may be in when it's eligible to be delegated. */
    public const STATUSES_DELEGATABLE = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_TEST_SENT,
        self::STATUS_EXTERNAL_FAILED,
    ];

    /**
     * Statuses where the SaaS owns the lifecycle. Local edits are blocked.
     * external_failed and external_partially_failed are intentionally NOT locked
     * so admins can re-delegate after a failure.
     */
    public const STATUSES_LOCKED = [
        self::STATUS_DELEGATED,
        self::STATUS_EXTERNAL_PENDING,
        self::STATUS_EXTERNAL_QUEUED,
        self::STATUS_EXTERNAL_SENDING,
        self::STATUS_EXTERNAL_SENT,
    ];

    /**
     * Statuses where an admin can cancel the external campaign on the SaaS.
     * Maps to SaaS-side `isCancellable()`: received / queued / sending.
     */
    public const STATUSES_EXTERNAL_CANCELLABLE = [
        self::STATUS_EXTERNAL_PENDING,
        self::STATUS_EXTERNAL_QUEUED,
        self::STATUS_EXTERNAL_SENDING,
    ];

    protected $casts = [
        'test_sent_at'             => 'datetime',
        'delegated_at'             => 'datetime',
        'external_last_synced_at'  => 'datetime',
        'external_sent_count'      => 'integer',
        'external_failed_count'    => 'integer',
        'external_skipped_count'   => 'integer',
        'external_accepted_count'  => 'integer',
        'external_open_count'      => 'integer',
        'external_click_count'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $campaign) {
            if (empty($campaign->created_by)) {
                $campaign->created_by = Auth::id();
            }
            if (empty($campaign->status)) {
                $campaign->status = self::STATUS_DRAFT;
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NewsletterTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            NewsletterList::class,
            'newsletter_campaign_list',
            'newsletter_campaign_id',
            'newsletter_list_id'
        )->withTimestamps();
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeDraft(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_DRAFT);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, self::STATUSES_LOCKED, true);
    }

    public function isDelegatable(): bool
    {
        return in_array($this->status, self::STATUSES_DELEGATABLE, true);
    }

    public function hasExternalReference(): bool
    {
        return filled($this->external_campaign_id);
    }

    public function isExternallyCancellable(): bool
    {
        return $this->hasExternalReference()
            && in_array($this->status, self::STATUSES_EXTERNAL_CANCELLABLE, true);
    }

    /**
     * Return the active subscribers eligible for this campaign — only `active`
     * status and only those attached to one of the campaign's selected lists.
     * Pending / unsubscribed / bounced / complained are always excluded.
     *
     * Used to build the delegation payload. AgaresCMS itself never iterates
     * this set to send mail.
     */
    public function activeRecipientsQuery()
    {
        return NewsletterSubscriber::query()
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->whereHas('lists', function (Builder $q) {
                $q->whereIn(
                    'newsletter_lists.id',
                    $this->lists()->pluck('newsletter_lists.id')
                );
            });
    }
}
