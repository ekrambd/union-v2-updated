<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rideorder extends Model
{
    use HasFactory;

    public function getCancelRiderIdsAttribute($value)
    {
        return $value == NULL?[]:json_decode($value);
    }

    public function rider()
    {
    	return $this->belongsTo(Rider::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
    
}
