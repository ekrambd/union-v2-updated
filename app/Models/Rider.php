<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    public function riderdoc()
    {
    	return $this->hasOne(Riderdoc::class);
    }

    public function riderarea()
    {
    	return $this->belongsTo(Riderarea::class);
    }

    public function regseries(){
    	return $this->belongsTo(Regseries::class);
    }
}
