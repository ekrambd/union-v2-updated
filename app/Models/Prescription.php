<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }

    public function prescriptiontests()
    {
        return $this->hasMany(Prescriptiontest::class);
    }

    public function doctorappointment()
    {
    	return $this->belongsTo(Doctorappointment::class);
    }
}
