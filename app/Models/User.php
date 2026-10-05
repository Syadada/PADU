<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_login',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'two_factor_secret',
        'two_factor_recovery_hash',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_login' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    /**
     * Cek apakah pengguna adalah Super Administrator (Pimpinan)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Cek apakah pengguna adalah Operator Staf
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Cek apakah akun sedang terkunci karena pelanggaran batas percobaan login
     */
    public function isLocked(): bool
    {
        if ($this->locked_until && Carbon::now()->lessThan($this->locked_until)) {
            return true;
        }
        return false;
    }

    /**
     * Dapatkan sisa waktu kunci akun dalam detik
     */
    public function remainingLockoutSeconds(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }
        return (int)Carbon::now()->diffInSeconds($this->locked_until, false);
    }
}
