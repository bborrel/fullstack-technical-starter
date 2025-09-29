<?php

namespace App;

enum DonationStatus: string
{
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case PENDING = 'pending';
    case REFUNDED = 'refunded';
}
