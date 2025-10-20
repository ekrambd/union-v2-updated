<?php
 use App\Models\Smsbalance;
 use App\Models\User;
 use App\Models\Couriercharge;

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

 function courierChargeCal($request)
 {
    $charge = Couriercharge::find(1);
    $total = 0;
    if($request->area_type == 'inside_city')
    {
        $total+=$charge->inside_delivery_charge;
    }
    if($request->area_type == 'outside_city')
    {
        $total+=$charge->outsider_delivery_charge;
    }
    if($request->has('pickup_type') && $request->pickup_type == 'home')
    {
        $total+=$charge->home_pickup_charge;
    }
    $total+=$request->weight*$charge->per_weight_charge;
    return $total;
 }