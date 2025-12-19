<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Location
 * 
 * Model untuk mengelola data lokasi virtual
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property string $floor
 * @property string $building
 * @property int $capacity
 * @property bool $requires_special_access
 * @property bool $is_active
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'floor',
        'building',
        'capacity',
        'requires_special_access',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'integer',
        'requires_special_access' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all access rights for this location
     * Relasi: One Location has Many Access Rights
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessRights(): HasMany
    {
        return $this->hasMany(AccessRight::class);
    }

    /**
     * Get all tracking logs for this location
     * Relasi: One Location has Many Tracking Logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }

    /**
     * Scope untuk filter lokasi aktif
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk cari lokasi berdasarkan kode
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope untuk filter berdasarkan gedung
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $building
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInBuilding($query, string $building)
    {
        return $query->where('building', $building);
    }

    /**
     * Scope untuk lokasi yang butuh akses khusus
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresSpecialAccess($query)
    {
        return $query->where('requires_special_access', true);
    }

    /**
     * Get current number of visitors in this location
     *
     * @return int
     */
    public function getCurrentVisitorCount(): int
    {
        // Get all users who entered this location
        $entries = TrackingLog::where('location_id', $this->id)
            ->where('status', 'accepted')
            ->whereIn('action_type', ['entry', 'move'])
            ->pluck('user_id', 'scanned_at');

        // Get all users who exited this location
        $exits = TrackingLog::where('location_id', $this->id)
            ->where('action_type', 'exit')
            ->where('status', 'accepted')
            ->pluck('user_id', 'scanned_at');

        // Users currently inside: users yang entry tapi belum exit
        $currentUsers = [];
        
        foreach ($entries as $time => $userId) {
            // Check if user has exited after this entry
            $hasExited = false;
            foreach ($exits as $exitTime => $exitUserId) {
                if ($exitUserId == $userId && $exitTime > $time) {
                    $hasExited = true;
                    break;
                }
            }
            
            if (!$hasExited) {
                $currentUsers[$userId] = true;
            }
        }

        return count($currentUsers);
    }

    /**
     * Get list of users currently in this location
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrentVisitors()
    {
        $userIds = [];
        
        // Get latest log for each user in this location
        $latestLogs = TrackingLog::where('location_id', $this->id)
            ->where('status', 'accepted')
            ->whereIn('action_type', ['entry', 'move'])
            ->get()
            ->groupBy('user_id');

        foreach ($latestLogs as $userId => $logs) {
            $latestEntry = $logs->sortByDesc('scanned_at')->first();
            
            // Check if user has exited after latest entry
            $hasExited = TrackingLog::where('location_id', $this->id)
                ->where('user_id', $userId)
                ->where('action_type', 'exit')
                ->where('scanned_at', '>', $latestEntry->scanned_at)
                ->exists();
            
            if (!$hasExited) {
                $userIds[] = $userId;
            }
        }

        return User::whereIn('id', $userIds)->get();
    }

    /**
     * Check if location is at capacity
     *
     * @return bool
     */
    public function isAtCapacity(): bool
    {
        if (!$this->capacity) {
            return false; // No capacity limit
        }

        return $this->getCurrentVisitorCount() >= $this->capacity;
    }

    /**
     * Get capacity percentage
     *
     * @return float
     */
    public function getCapacityPercentageAttribute(): float
    {
        if (!$this->capacity) {
            return 0;
        }

        return ($this->getCurrentVisitorCount() / $this->capacity) * 100;
    }

    /**
     * Get tracking statistics for this location
     *
     * @param string $period (today|week|month)
     * @return array
     */
    public function getStatistics(string $period = 'today'): array
    {
        $query = $this->trackingLogs();

        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $query->where('scanned_at', '>=', $startDate);

        return [
            'total_scans' => $query->count(),
            'accepted' => $query->where('status', 'accepted')->count(),
            'denied' => $query->where('status', 'denied')->count(),
            'entries' => $query->where('action_type', 'entry')->count(),
            'exits' => $query->where('action_type', 'exit')->count(),
            'moves' => $query->where('action_type', 'move')->count(),
            'unique_visitors' => $query->distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Get full location name with building and floor
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        
        if ($this->floor) {
            $parts[] = $this->floor;
        }
        
        if ($this->building) {
            $parts[] = $this->building;
        }

        return implode(', ', $parts);
    }
}
