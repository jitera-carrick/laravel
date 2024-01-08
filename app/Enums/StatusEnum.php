
<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CANCELED = 'canceled';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
