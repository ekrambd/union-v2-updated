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

    public function getTotalExperiences($value)
    {
        $start_time = $value->start_time;
        if($value->is_continue == 1){
            $end_time = date('Y-m-d');
        }else{
            $end_time = $value->end_time;
        }
        $diffInSeconds = strtotime($end_time) - strtotime($start_time);
        $diffInYears = floor($diffInSeconds / (365 * 24 * 60 * 60));
        return $diffInYears;
    }

    protected $hidden = [
        'password',
    ];
}
