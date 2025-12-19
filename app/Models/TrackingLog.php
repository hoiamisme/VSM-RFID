<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Model TrackingLog
 * 
 * Model untuk mencatat semua aktivitas scan RFID
 * 
 * @property int $id
 * @property int $rfid_card_id
 * @property int $user_id
 * @property int $location_id
 * @property string $action_type (entry|exit|move|denied)
 * @property string $status (accepted|denied)
 * @property Carbon $scanned_at
 * @property string $rfid_uid
 * @property string $location_code
 * @property string $ip_address
 * @property string $user_agent
 * @property string $denial_reason
 * @property float $temperature
 * @property string $notes
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class TrackingLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracking_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rfid_card_id',
        'user_id',
        'location_id',
        'action_type',
        'status',
        'scanned_at',
        'rfid_uid',
        'location_code',
        'ip_address',
        'user_agent',
        'denial_reason',
        'temperature',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scanned_at' => 'datetime',
        'temperature' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Konstanta untuk action types
     */
    const ACTION_ENTRY = 'entry';
    const ACTION_EXIT = 'exit';
    const ACTION_MOVE = 'move';
    const ACTION_DENIED = 'denied';

    /**
     * Konstanta untuk status
     */
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DENIED = 'denied';

    /**
     * Indicates if the model should be timestamped.
     * We use scanned_at as the primary timestamp
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the RFID card associated with this log
     * Relasi: Tracking Log belongs to RFID Card
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class);
    }

    /**
     * Get the user associated with this log
     * Relasi: Tracking Log belongs to User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location associated with this log
     * Relasi: Tracking Log belongs to Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope untuk filter berdasarkan action type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAction($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope untuk filter berdasarkan status
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
     * Scope untuk filter logs yang diterima
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope untuk filter logs yang ditolak
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDenied($query)
    {
        return $query->where('status', self::STATUS_DENIED);
    }

    /**
     * Scope untuk filter logs hari ini
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scanned_at', today());
    }

    /**
     * Scope untuk filter logs dalam periode tertentu
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Carbon $from
     * @param Carbon $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetween($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('scanned_at', [$from, $to]);
    }

    /**
     * Scope untuk entry logs (masuk)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEntries($query)
    {
        return $query->where('action_type', self::ACTION_ENTRY);
    }

    /**
     * Scope untuk exit logs (keluar)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExits($query)
    {
        return $query->where('action_type', self::ACTION_EXIT);
    }

    /**
     * Check if this is an entry log
     *
     * @return bool
     */
    public function isEntry(): bool
    {
        return $this->action_type === self::ACTION_ENTRY;
    }

    /**
     * Check if this is an exit log
     *
     * @return bool
     */
    public function isExit(): bool
    {
        return $this->action_type === self::ACTION_EXIT;
    }

    /**
     * Check if this is a move log
     *
     * @return bool
     */
    public function isMove(): bool
    {
        return $this->action_type === self::ACTION_MOVE;
    }

    /**
     * Check if this log was accepted
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if this log was denied
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    /**
     * Get action type name for display
     *
     * @return string
     */
    public function getActionTypeNameAttribute(): string
    {
        return match($this->action_type) {
            self::ACTION_ENTRY => 'Masuk',
            self::ACTION_EXIT => 'Keluar',
            self::ACTION_MOVE => 'Pindah Lokasi',
            self::ACTION_DENIED => 'Ditolak',
            default => 'Unknown',
        };
    }

    /**
     * Get status name for display
     *
     * @return string
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACCEPTED => 'Diterima',
            self::STATUS_DENIED => 'Ditolak',
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
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_DENIED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get action color for UI
     *
     * @return string
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action_type) {
            self::ACTION_ENTRY => 'primary',
            self::ACTION_EXIT => 'secondary',
            self::ACTION_MOVE => 'info',
            self::ACTION_DENIED => 'danger',
            default => 'dark',
        };
    }

    /**
     * Get formatted scan time
     *
     * @return string
     */
    public function getFormattedScanTimeAttribute(): string
    {
        return $this->scanned_at->format('d/m/Y H:i:s');
    }

    /**
     * Get human-readable time ago
     *
     * @return string
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->scanned_at->diffForHumans();
    }
}
