<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riderarea extends Model
{
    use HasFactory;

    public function riders()
    {
    	return $this->hasMany(Rider::class);
    }
}
