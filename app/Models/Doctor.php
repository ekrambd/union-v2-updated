<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable
{   

    use HasApiTokens, HasFactory, Notifiable;

    public function doctoravailability() 
    {
    	return $this->hasOne(Doctoravailability::class);
    }

    public function doctordegrees()
    {
    	return $this->hasMany(Doctordegree::class);
    }

    public function doctorexperiences()
    {
    	return $this->hasMany(Doctorexperience::class);
    }

    public function doctordoc()
    {
    	return $this->hasOne(Doctordoc::class);
    }

    public function doctorfee()
    {
    	return $this->hasOne(Doctorfee::class);
    }
    
    public function getExpertiseAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    
    
    public function getTypeAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    
    public function doctorappointments()
    {
        return $this->hasMay(Doctorappointment::class);
    }

    protected $hidden = [
        'password',
    ];
}
