<?php

namespace App\Models;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['job_type', 'status', 'message', 'payload'])]
class SyncLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status' => SyncStatus::class,
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
