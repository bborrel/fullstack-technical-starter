<?php

namespace App;

enum RecurringDonationStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case PAUSED = 'paused';
}
