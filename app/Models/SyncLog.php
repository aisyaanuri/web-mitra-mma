<?php

namespace App\Models;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $job_type
 * @property SyncStatus $status
 * @property string|null $message
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $created_at
 */
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
