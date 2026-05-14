<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctorappointment extends Model 
{
    use HasFactory;
    

    protected $appends = ['chat_id'];

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
    
    public function getChatIdAttribute()
    {
        $getUser = \DB::table('doctors')->where('id',$this->doctor_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','doctor')->first();
        return $user?strval($user->id):"";
    }

}
