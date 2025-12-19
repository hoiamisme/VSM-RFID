<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Model AccessRight
 * 
 * Model untuk mengelola hak akses user ke lokasi
 * 
 * @property int $id
 * @property int $user_id
 * @property int $location_id
 * @property bool $can_access
 * @property string $access_type (permanent|temporary|scheduled)
 * @property Carbon $valid_from
 * @property Carbon $valid_until
 * @property array $time_restrictions
 * @property string $reason
 * @property int $granted_by
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class AccessRight extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'access_rights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'can_access',
        'access_type',
        'valid_from',
        'valid_until',
        'time_restrictions',
        'reason',
        'granted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'can_access' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'time_restrictions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Konstanta untuk access types
     */
    const TYPE_PERMANENT = 'permanent';
    const TYPE_TEMPORARY = 'temporary';
    const TYPE_SCHEDULED = 'scheduled';

    /**
     * Get the user that owns the access right
     * Relasi: Access Right belongs to User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location of the access right
     * Relasi: Access Right belongs to Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who granted this access
     * Relasi: Access Right belongs to User (granted_by)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scope untuk filter access rights yang diizinkan
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllowed($query)
    {
        return $query->where('can_access', true);
    }

    /**
     * Scope untuk filter access rights yang ditolak
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDenied($query)
    {
        return $query->where('can_access', false);
    }

    /**
     * Scope untuk filter berdasarkan tipe akses
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('access_type', $type);
    }

    /**
     * Scope untuk access rights yang masih valid (tidak expired)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($q2) {
                // Check valid_from
                $q2->whereNull('valid_from')
                   ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q2) {
                // Check valid_until
                $q2->whereNull('valid_until')
                   ->orWhere('valid_until', '>=', now());
            });
        });
    }

    /**
     * Check if access right is currently valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        // Check if can_access is true
        if (!$this->can_access) {
            return false;
        }

        // Check valid_from
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        // Check valid_until
        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if access is valid at specific time
     *
     * @param Carbon|null $time
     * @return bool
     */
    public function isValidAt(?Carbon $time = null): bool
    {
        $time = $time ?? now();

        if (!$this->isValid()) {
            return false;
        }

        // Check time restrictions if any
        if (!$this->time_restrictions) {
            return true; // No time restrictions
        }

        return $this->checkTimeRestrictions($time);
    }

    /**
     * Check time restrictions
     *
     * @param Carbon $time
     * @return bool
     */
    protected function checkTimeRestrictions(Carbon $time): bool
    {
        $restrictions = $this->time_restrictions;

        // Check day restrictions
        if (isset($restrictions['days'])) {
            $currentDay = strtolower($time->format('l')); // monday, tuesday, etc.
            $allowedDays = array_map('strtolower', $restrictions['days']);
            
            if (!in_array($currentDay, $allowedDays)) {
                return false;
            }
        }

        // Check hour restrictions
        if (isset($restrictions['hours'])) {
            $currentTime = $time->format('H:i');
            $startTime = $restrictions['hours']['start'] ?? '00:00';
            $endTime = $restrictions['hours']['end'] ?? '23:59';

            if ($currentTime < $startTime || $currentTime > $endTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if access right is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Check if access right is permanent
     *
     * @return bool
     */
    public function isPermanent(): bool
    {
        return $this->access_type === self::TYPE_PERMANENT;
    }

    /**
     * Check if access right is temporary
     *
     * @return bool
     */
    public function isTemporary(): bool
    {
        return $this->access_type === self::TYPE_TEMPORARY;
    }

    /**
     * Check if access right is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->access_type === self::TYPE_SCHEDULED;
    }

    /**
     * Get access type name for display
     *
     * @return string
     */
    public function getAccessTypeNameAttribute(): string
    {
        return match($this->access_type) {
            self::TYPE_PERMANENT => 'Permanen',
            self::TYPE_TEMPORARY => 'Sementara',
            self::TYPE_SCHEDULED => 'Terjadwal',
            default => 'Unknown',
        };
    }

    /**
     * Get days until expiration
     *
     * @return int|null
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }

        return now()->diffInDays($this->valid_until, false);
    }

    /**
     * Get human-readable time restrictions
     *
     * @return string|null
     */
    public function getTimeRestrictionsTextAttribute(): ?string
    {
        if (!$this->time_restrictions) {
            return null;
        }

        $parts = [];
        $restrictions = $this->time_restrictions;

        if (isset($restrictions['days'])) {
            $days = implode(', ', $restrictions['days']);
            $parts[] = "Hari: {$days}";
        }

        if (isset($restrictions['hours'])) {
            $start = $restrictions['hours']['start'] ?? '00:00';
            $end = $restrictions['hours']['end'] ?? '23:59';
            $parts[] = "Jam: {$start} - {$end}";
        }

        return implode(' | ', $parts);
    }
}
