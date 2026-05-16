<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyerappointment extends Model
{
    use HasFactory;

    //protected $appends = ['chat_id'];

    protected $appends = ['user_chat_id','lawyer_chat_id']; 

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

    // public function getChatIdAttribute()
    // {
    //     $getUser = \DB::table('lawyers')->where('id',$this->lawyer_id)->first();
    //     $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','lawyer')->first();
    //     return $user?strval($user->id):"";
    // }

    public function getLawyerChatIdAttribute()
    {
        $getUser = \DB::table('lawyers')->where('id',$this->lawyer_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->phone)->where('role','lawyer')->first();
        return $user?strval($user->id):"";
    }

    public function getUserChatIdAttribute()
    {
        $getUser = \DB::table('users')->where('id',$this->user_id)->first();
        $user = \DB::connection('mysql_second')->table('users')->where('phone',$getUser->mobile)->where('role','user')->first();
        return $user?strval($user->id):"";
    }


}
