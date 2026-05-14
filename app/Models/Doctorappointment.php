<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctorappointment extends Model 
{
    use HasFactory;
    

    protected $appends = ['user_chat_id','doctor_chat_id'];

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

    public function doctorconversation()
    {
        return $this->hasOne(Doctorconversation::class);
    }
    
    public function getDoctorChatIdAttribute()
    {
        $getUser = \DB::table('doctors')->where('id',$this->doctor_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','doctor')->first();
        return $user?strval($user->id):"";
    }

    public function getUserChatIdAttribute()
    {
        $getUser = \DB::table('users')->where('id',$this->user_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->mobile)->where('role','user')->first();
        return $user?strval($user->id):"";
    }

}
