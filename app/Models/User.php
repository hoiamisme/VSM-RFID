<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model User
 * 
 * Model untuk mengelola data pengguna sistem (tamu dan pegawai)
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $user_type (guest|employee)
 * @property string $address
 * @property string $institution
 * @property string $employee_id
 * @property string $photo
 * @property bool $is_active
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class User extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_type',
        'address',
        'institution',
        'employee_id',
        'photo',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Konstanta untuk user types
     */
    const TYPE_GUEST = 'guest';
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_KADET = 'kadet';

    /**
     * Get all RFID cards belonging to this user
     * Relasi: One User has Many RFID Cards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rfidCards(): HasMany
    {
        return $this->hasMany(RfidCard::class);
    }

    /**
     * Get active RFID card for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeRfidCards(): HasMany
    {
        return $this->rfidCards()->where('status', 'active');
    }

    /**
     * Get all access rights for this user
     * Relasi: One User has Many Access Rights
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessRights(): HasMany
    {
        return $this->hasMany(AccessRight::class);
    }

    /**
     * Get all tracking logs for this user
     * Relasi: One User has Many Tracking Logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }

    /**
     * Get access rights that have been granted by this user
     * (untuk user yang memberikan izin akses)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grantedAccessRights(): HasMany
    {
        return $this->hasMany(AccessRight::class, 'granted_by');
    }

    /**
     * Scope untuk filter user berdasarkan tipe
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope untuk filter user aktif
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter tamu (guest)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGuests($query)
    {
        return $query->where('user_type', self::TYPE_GUEST);
    }

    /**
     * Scope untuk filter pegawai (employee)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmployees($query)
    {
        return $query->where('user_type', self::TYPE_EMPLOYEE);
    }

    /**
     * Scope untuk filter kadet
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeKadets($query)
    {
        return $query->where('user_type', self::TYPE_KADET);
    }

    /**
     * Check if user is a guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->user_type === self::TYPE_GUEST;
    }

    /**
     * Check if user is an employee
     *
     * @return bool
     */
    public function isEmployee(): bool
    {
        return $this->user_type === self::TYPE_EMPLOYEE;
    }

    /**
     * Check if user is a kadet
     *
     * @return bool
     */
    public function isKadet(): bool
    {
        return $this->user_type === self::TYPE_KADET;
    }

    /**
     * Check if user has access to a specific location
     *
     * @param int $locationId
     * @return bool
     */
    public function hasAccessTo(int $locationId): bool
    {
        return $this->accessRights()
            ->where('location_id', $locationId)
            ->where('can_access', true)
            ->exists();
    }

    /**
     * Get user's current location based on last tracking log
     *
     * @return \App\Models\Location|null
     */
    public function getCurrentLocation()
    {
        $lastLog = $this->trackingLogs()
            ->where('status', 'accepted')
            ->whereIn('action_type', ['entry', 'move'])
            ->latest('scanned_at')
            ->first();

        if (!$lastLog) {
            return null;
        }

        // Check if user has exited since last entry
        $exitLog = $this->trackingLogs()
            ->where('location_id', $lastLog->location_id)
            ->where('action_type', 'exit')
            ->where('scanned_at', '>', $lastLog->scanned_at)
            ->exists();

        if ($exitLog) {
            return null; // User has exited
        }

        return $lastLog->location;
    }

    /**
     * Check if user is currently inside any location
     *
     * @return bool
     */
    public function isCurrentlyInside(): bool
    {
        return $this->getCurrentLocation() !== null;
    }

    /**
     * Get photo URL
     *
     * @return string
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        
        // Default avatar
        return asset('images/default-avatar.png');
    }

    /**
     * Get formatted user type for display
     *
     * @return string
     */
    public function getUserTypeNameAttribute(): string
    {
        return match($this->user_type) {
            self::TYPE_GUEST => 'Tamu',
            self::TYPE_EMPLOYEE => 'Pegawai',
            self::TYPE_KADET => 'Kadet',
            default => 'Unknown',
        };
    }
}
