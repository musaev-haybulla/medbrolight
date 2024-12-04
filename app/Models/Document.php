<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\{DocumentStatus, FileType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'telegram_message_id',
        'user_id',
        'status',
        'new_name',
        'yandex_disk_url',
        'error_message',
        'retry_count'
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'retry_count' => 'integer'
    ];

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
