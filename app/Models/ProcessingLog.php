<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ProcessingStatus;

class ProcessingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'status',
        'response',
        'attempted_at'
    ];

    protected $casts = [
        'status' => ProcessingStatus::class,
        'attempted_at' => 'datetime'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
