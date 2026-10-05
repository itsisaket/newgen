<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    // Eloquent's default table-name pluralizer treats "evidence" as an
    // uncountable/mass noun, so it resolves to "evidence" (singular) instead
    // of the real table "evidences" -- caused "Table 'evidence' doesn't
    // exist" errors wherever a morphTo/morphMany touched this model. Pin the
    // table name explicitly instead of relying on the pluralizer.
    protected $table = 'evidences';

    protected $fillable = [
        'evidenceable_type', 'evidenceable_id', 'file_path', 'thumbnail_path', 'file_type',
        'file_size', 'gps_lat', 'gps_lng', 'captured_at', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    public function evidenceable()
    {
        return $this->morphTo();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
