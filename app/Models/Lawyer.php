<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Lawyer extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['completed_count','total_review','chat_id'];

    public function lawyeravailability()
    {
    	return $this->hasOne(Lawyeravailability::class);
    }

    public function lawyerdoc()
    {
    	return $this->hasOne(Lawyerdoc::class);
    }

    public function lawyerfee()
    {
    	return $this->hasOne(Lawyerfee::class);
    }

    public function getLawyerdegreesAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getTypeAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }
    
    public function lawyerreviews()
    {
        return $this->hasMany(Lawyerreview::class);
    }

    public function lawyerappointments()
    {
        return $this->hasMany(Lawyerappointment::class);
    }

    public function getCompletedCountAttribute()
    {
        $count =  \App\Models\Lawyerappointment::where('lawyer_id', $this->id)
            ->where('status', 'Completed')
            ->count();
        return strval($count);
    }

    public function getTotalReviewAttribute()
    {
        return (string) \App\Models\Lawyerreview::where('lawyer_id', $this->id)->count();
    }

    public function getChatIdAttribute()
    {
        $getUser = \DB::table('lawyers')->where('id',$this->id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','lawyer')->first();
        return $user?strval($user->id):"";
    }

    // public function getChatIdAttribute()
    // {
    //     $getUser = \DB::table('users')->where('id',$this->id)->first();
    //     $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->mobile)->where('role','lawyer')->first();
    //     return $user?strval($user->id):"";
    // }

}
