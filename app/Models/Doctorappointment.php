<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctorappointment extends Model
{
    use HasFactory;
    
    public function doctor()
    {
    	return $this->belongsTo(Doctor::class);
    }

    public function patientinfo()
    {
    	return $this->hasOne(Patientinfo::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }
    
}
