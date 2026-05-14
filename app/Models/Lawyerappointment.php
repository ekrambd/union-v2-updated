<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerappointment extends Model
{
    use HasFactory;

    protected $appends = ['chat_id']; 

    public function userinfo()
    {
    	return $this->hasOne(Userinfo::class);
    }

    public function lawyerconsultation()
    {
        return $this->hasOne(Lawyerconsultation::class);
    }

    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function lawyerconversation()
    {
        return $this->hasOne(Lawyerconversation::class);
    }

    public function getChatIdAttribute()
    {
        $getUser = \DB::table('lawyers')->where('id',$this->lawyer_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','lawyer')->first();
        return $user?strval($user->id):"";
    }
}
