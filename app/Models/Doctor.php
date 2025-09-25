<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use DB;

class Doctor extends Authenticatable
{   

    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['total_experiences','total_ratings','total_patients'];


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

    public function getTotalExperiencesAttribute()
    {
        $totalMonths = 0;

        foreach ($this->doctorexperiences as $exp) {
            $start = new \DateTime($exp->start_time);
            $end   = $exp->is_continue ? new \DateTime() : new \DateTime($exp->end_time);

            $diff = $start->diff($end);

            // Convert years & months to total months
            $totalMonths += ($diff->y * 12) + $diff->m;
        }

        // Convert back to years and months
        $years  = floor($totalMonths / 12);
        $months = $totalMonths % 12;

        $result = '';
        if ($years > 0) {
            $result .= $years . ' year' . ($years > 1 ? 's ' : ' ');
        }
        if ($months > 0) {
            $result .= $months . ' month' . ($months > 1 ? 's' : '');
        }

        return trim($result) ?: '0 month';
    }

    public function getTotalRatingsAttribute()
    {
        $sum = DB::table('doctorratings')
            ->where('doctor_id', $this->id)
            ->sum('rating');

        return ceil($sum);
    }

    public function getTotalPatientsAttribute()
    {
        $count = DB::table('doctorappointments')
            ->where('doctor_id', $this->id)
            ->where('status', 'Completed')
            ->count();

        return $count;
    }


    protected $hidden = [
        'password',
    ];
}
