<?php
namespace App\Enums;

enum ProcessingStatus: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case RATE_LIMITED = 'rate_limited';
    case ERROR = 'error';
    case WARNING = 'warning';
}
