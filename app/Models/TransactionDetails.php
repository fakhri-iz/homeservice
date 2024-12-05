<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetails extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'price',
        'home_service_id',
        'booking_transaction_id',
    ];

    //relations
    public function bookingTransaction(): BelongsTo
    {
        return $this->belongsTo(BookingTransaction::class, 'booking_transaction_id');
    }

    public function homeService(): BelongsTo
    {
        return $this->belongsTo(HomeService::class, 'home_service_id');
    }
}
