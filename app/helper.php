<?php
 use App\Models\Smsbalance;
 use App\Models\User;
 
 function generateRefer()
 {
 	$az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $azr = rand(0, 51);
    $azs = substr($az, $azr, 10);
    $stamp = hash('sha256', time());
    $mt = hash('sha256', mt_rand(5, 20));
    $alpha = hash('sha256', $azs);
    $hash = str_shuffle($stamp . $mt . $alpha);
    $code = strtoupper(substr($hash, $azr, 5));
    return $code;  
 }
 
 function user()
 {
    $user = auth()->user();
    return $user;
 }
 
 function getReferralID($referral_code)
 {
    $user = User::where('referral_unique_id',$referral_code)->orWhere('mobile',$referral_code)->first();
    if(!$user)
    {
        return null;
    }
    return $user->id;
 }

 function smsBalance()
 {
    $data = Smsbalance::find(1);
    if($data)
    {
        return $data->balance;
    }
    return "0";
 }