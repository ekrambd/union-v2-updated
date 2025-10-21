<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courierorder extends Model
{
    use HasFactory;

    public function upazila()
    {
    	return $this->belongsTo(Upazila::class);
    }

    public function division()
    {
    	return $this->belongsTo(Division::class);
    }

    public function district()
    {
    	return $this->belongsTo(District::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }
}
