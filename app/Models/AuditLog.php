<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /**
     * Audit log bersifat Append-Only (Immutable).
     * Tidak boleh ada kolom updated_at untuk mencegah pemalsuan forensik.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'status',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
