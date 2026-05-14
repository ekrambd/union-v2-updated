<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctorconversation extends Model
{
    use HasFactory;

    public function doctorappointment()
    {
    	return $this->belongsTo(Doctorappointment::class);
    }
}
