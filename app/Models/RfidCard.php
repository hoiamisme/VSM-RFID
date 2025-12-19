<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Model RfidCard
 * 
 * Model untuk mengelola data kartu RFID
 * 
 * @property int $id
 * @property string $uid
 * @property int $user_id
 * @property string $card_number
 * @property string $status (active|inactive|blocked|lost)
 * @property Carbon $registered_at
 * @property Carbon $expired_at
 * @property Carbon $last_used_at
 * @property string $notes
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class RfidCard extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rfid_cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'card_number',
        'status',
        'registered_at',
        'expired_at',
        'last_used_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'expired_at' => 'datetime',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Konstanta untuk card status
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_LOST = 'lost';

    /**
     * Get the user that owns the RFID card
     * Relasi: RFID Card belongs to User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all tracking logs for this card
     * Relasi: One RFID Card has Many Tracking Logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }

    /**
     * Scope untuk filter kartu berdasarkan status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter kartu aktif
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope untuk filter kartu yang belum expired
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expired_at')
              ->orWhere('expired_at', '>', now());
        });
    }

    /**
     * Scope untuk cari kartu berdasarkan UID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $uid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUid($query, string $uid)
    {
        return $query->where('uid', $uid);
    }

    /**
     * Check if card is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if card is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expired_at) {
            return false; // Tidak ada expiry date
        }

        return $this->expired_at->isPast();
    }

    /**
     * Check if card is valid (active and not expired)
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Update last used timestamp
     *
     * @return void
     */
    public function updateLastUsed(): void
    {
        $this->last_used_at = now();
        $this->save();
    }

    /**
     * Block this card
     *
     * @param string|null $reason
     * @return void
     */
    public function block(?string $reason = null): void
    {
        $this->status = self::STATUS_BLOCKED;
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                           "Blocked: " . $reason . " at " . now();
        }
        $this->save();
    }

    /**
     * Activate this card
     *
     * @return void
     */
    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Deactivate this card
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * Mark card as lost
     *
     * @param string|null $notes
     * @return void
     */
    public function markAsLost(?string $notes = null): void
    {
        $this->status = self::STATUS_LOST;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                           "Lost: " . $notes . " at " . now();
        }
        $this->save();
    }

    /**
     * Get status name for display
     *
     * @return string
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_BLOCKED => 'Diblokir',
            self::STATUS_LOST => 'Hilang',
            default => 'Unknown',
        };
    }

    /**
     * Get status color for UI
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_BLOCKED => 'danger',
            self::STATUS_LOST => 'warning',
            default => 'dark',
        };
    }

    /**
     * Get days until expiration
     *
     * @return int|null
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expired_at) {
            return null;
        }

        return now()->diffInDays($this->expired_at, false);
    }
}
