<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riderdoc extends Model
{
    use HasFactory;
    
    public function rider()
    {
    	return $this->belongsTo(Rider::class);
    }
}
