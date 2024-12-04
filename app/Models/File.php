<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\FileType;

class File extends Model
{
    protected $fillable = [
        'document_id',
        'file_id',
        'file_type',
        'file_path',
        'file_order',
        'error_message'
    ];

    protected $casts = [
        'file_type' => FileType::class
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
