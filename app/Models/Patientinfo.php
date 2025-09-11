<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patientinfo extends Model
{
    use HasFactory;

    public function doctorappointment()
    {
    	return $this->belongsTo(Doctorappointment::class);
    }

    public function getPreviousDocumentsAttribute($value)
    {
        return $value ? json_decode($value) : [];
    }
}
