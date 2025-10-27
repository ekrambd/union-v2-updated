<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courierrider extends Model
{
    use HasFactory;

    public function courirerorders()
    {
    	return $this->hasMany(Courierorder::class);
    }
}
