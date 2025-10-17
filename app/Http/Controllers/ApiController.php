<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Doctordegree;
use App\Models\Doctorexperience;
use App\Models\Doctordoc;
use App\Models\Doctoravailability;
use App\Models\Doctorfee;
use App\Models\Provider;
use Validator;
use Auth;
use DB;
use App\Models\Smsbalance;
use App\Models\Smslog;
use Carbon\Carbon;
use App\Models\Dcinfo;
use App\Models\Doctorappointment;
use Hash;
use App\Models\Patientinfo;
use App\Models\Doctorrating;
use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\Prescriptiontest;
use App\Models\Rider;
use App\Models\Riderarea;
use App\Models\Riderdoc;
use App\Models\Regseries;
use App\Models\Lawyer;
use App\Models\Lawyeravailability;
use App\Models\Lawyerdegree;
use App\Models\Lawyerdoc;
use App\Models\Lawyerfee;
use App\Models\Lawyerappointment;
use App\Models\Userinfo; 
use App\Models\Appnotify;
use App\Models\Lawyerreview;
use App\Models\Lawyerconsultation;

class ApiController extends Controller
{   

    public function login()
    {
        return response()->json(['status'=>false, 'message'=>'Please Logged In First'], 401);
    }

    public function userSignup(Request $request)
    {
    	try
    	{
    		$validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|string|unique:users',
                'district_id' => 'nullable|integer',
                'upazila_id' => 'nullable|integer',
                'union_id' => 'nullable|integer',
                'latitude' => 'required',
                'longitude' => 'required',
                'device_token' => 'required|string|unique:users',
                'referral_code' => 'nullable|string',
                'country' => 'nullable|string',
                'country_code' => 'nullable|string',
                'password' => 'required|string',
                'confirm_password' => 'required|string|same:password',
                'dob' => 'required',
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'address' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Duplicate Value found or Invalid Information Provided', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $count = User::count();
            $count+=1;

            if($request->file('image'))
            {   
                $file = $request->file('image');
                $name = time().$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/users/', $name); 
                $path = 'uploads/users/'.$name;
            }
            else
            {
                $path = NULL; 
            }

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->payment_mode = 'CASH';
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->district_id = $request->district_id;
            $user->upazila_id = $request->upazila_id;
            $user->union_id = $request->union_id;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->device_token = $request->device_token;
            $user->referral_id = $request->has('referral_code')?getReferralID($request->referral_code):null;
            $user->password = bcrypt($request->password);
            $user->password_two = md5($request->password);
            $user->picture = $path;
            $user->referral_unique_id = $request->mobile;
            $user->marketing_officer = $request->marketing_officer;
            $user->sales_supervisor = $request->sales_supervisor;
            $user->sales_officer = $request->sales_officer;
            $user->country = $request->country;
            $user->country_code = $request->country_code;
            $user->address = $request->address;
            $user->dob = $request->dob;
            $user->gender = $request->gender;
            $user->save();


            return response()->json(['status'=>true, 'message'=>'Successfully an user has been added', 'user'=>$user]);

    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function userSignin(Request $request)
    {
    	try
    	{  

    		$validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

    		$login = $request->input('login');
		    $password = $request->input('password');

		    $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

            $user = User::where('email',$login)->orWhere('mobile',$login)->first();

            //return $user;
            if($user)
            {
                $profile = DB::table('profile')->where('email',$user->email)->first();

            //return $profile;

                $user_type = $profile?$profile->type:"0";
            }
            

            // if(!$user){
            //     return "nei";
            // }

		    if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
		        $user = auth()->user();
		        $token = $user->createToken('MyApp')->plainTextToken;
		        return response()->json(['status'=>true, 'is_agent'=>strval($user_type), 'message'=>'Successfully Logged IN', 'token'=>$token, 'user'=>$user]);
		    }
		    return response()->json(['status'=>false, 'is_agent'=>strval(0), 'message'=>'Invalid Email/Mobile or Password', 'token'=>"", 'user'=> new \stdClass()],400);

    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function districts()
    {
    	try
    	{
    		$districts = DB::table('districts')->get();
    		return response()->json(['status'=>count($districts)>0, 'data'=>$districts]);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function upazilas(Request $request)
    {
    	try
    	{
    		$data = DB::table('upazilas')->where('district_id',$request->district_id)->get();
    		return response()->json(['status'=>count($data)>0, 'data'=>$data]);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function unions(Request $request)
    {
    	try
    	{
    		$unions = DB::table('unions')->where('upazilla_id',$request->upazilla_id)->get();
    		return response()->json(['status'=>count($unions)>0, 'data'=>$unions]);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function userSignOut(Request $request)
    {
    	try
    	{
    		auth()->user()->tokens()->delete();
    		return response()->json(['status'=>true, 'message'=>'Successfully Logged Out']);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function sendOTP(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
            $balance = smsBalance();

            if($balance < 2)
            {
                return response()->json(['status'=>false, 'message'=>'Temporaray message server problem'],503);
            }
            
            
            $mobileNo = substr($request->mobile_no, 2);
            
            $user = User::where('mobile',$mobileNo)->first();

            // if(!$user){
            //     return response()->json("got it");
            // }

            // $count = DB::table('smslogs')->where('mobile_no',$request->mobile_no)->where('status','verified')->count();

            // if($count > 0){
            //     return response()->json(['status'=>false, 'message'=>'Sorry the number already has been taken'],400);
            // }
            
            // if (substr($number, 0, 2) === "88") {
            //     $number = substr($number, 2);
            // }
            
            // if($user){
            //     return response()->json(['status'=>false, 'message'=>'Sorry the number already has been taken'],404);
            // }

            
            // if($user->send_otp == 1)
            // {
            //     return response()->json(['status'=>false, 'message'=>'Sorry the number already has been taken'],400);
            // }
            
            if(!$user){
                $rand = rand(100000,200000);

                $message = "This is your otp verification code: $rand";

                $curl = curl_init();
                $data = [
                    "username"=>"artificialsoft",
                    "password"=>"artisoft@bd#321",
                    "sender"=>"03590001868",
                    "message"=>$message,
                    "to"=>"$request->mobile_no"
                ];
                curl_setopt_array($curl, array(
                CURLOPT_URL => "http://api.icombd.com/api/v2/sendsms/plaintext",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                //return $response;
                if ($err) {
                    //echo "cURL Error #:" . $err;
                    return response()->json(['status'=>false, 'message'=>'Something went wrong'],403);
                } else {
                    $log = new Smslog();
                    $log->mobile_no = $request->mobile_no;
                    $log->otp = $rand;
                    $log->timestamp = time();
                    $log->status = 'pending';
                    $log->save();

                    $bal = Smsbalance::find(1);
                    $bal->balance-=1;
                    $bal->update();

                    // $user->send_otp = 1;
                    // $user->update();

                    DB::commit();

                    return response()->json(['status'=>true, 'message'=>'Verification OTP has been sent'],200);
                }
            }
            
            DB::commit();
            
            return response()->json(['status'=>false, 'message'=>'Sorry the number already has been taken'],400);

        }catch(Exception $e){
            DB::rollback();
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function verifyOTP(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        try
        {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|string',
                'otp' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $data = Smslog::where('mobile_no',$request->mobile_no)->where('otp',$request->otp)->orderBy('id','DESC')->first();

            //return $data;
            
            if(!$data)
            {
                return response()->json(['status'=>false, 'mobile_no'=>$request->mobile_no, 'message'=>'Invalid OTP'],404);
            }

            $timeStamp = time();
            $diff = $timeStamp - $data->timestamp;

            if($diff > 300)
            {
                return response()->json(['status'=>false, 'mobile_no'=>$request->mobile_no, 'message'=>'The otp verification code has been expired'],410);
            }

            $data->status = 'verified';
            $data->update();

            // $user->send_otp = 1;
            // $user->update();
            //return $data;
            return response()->json(['status'=>true, 'mobile_no'=>$request->mobile_no, 'message'=>'Successfully Verified'],200);

        }catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }
    
    public function providerSignup(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function providerSignin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $login = $request->input('login');
            $password = $request->input('password');

            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

            if (Auth::guard('provider')->attempt([$fieldType => $login, 'password' => $password])) {
                $user = Auth::guard('provider')->user();
                $token = $user->createToken('MyApp')->plainTextToken;
                return response()->json([
                    'status' => true, 
                    'message' => 'Successfully Logged IN', 
                    'token' => $token, 
                    'user' => $user
                ]);
            }
            return response()->json(['status'=>false, 'message'=>'Invalid Email/Mobile or Password', 'token'=>"", 'user'=> new \stdClass()],400);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        } 
    }

    public function providerSignout(Request $request)
    {
        try
        {
            // $user = Auth::guard('provider')->user();
            $user = auth()->user();
            $user->tokens()->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully Logged Out']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerSignup(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string',
                'email' => 'required|email|unique:lawyers',
                'phone' => 'required|string|unique:lawyers',
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'dob' => 'required|date_format:Y-m-d',
                'license_number' => 'required|string|unique:lawyers',
                'total_experience' => 'required|string',
                'practice_area' => 'required|string',
                'current_law_firm' => 'nullable|string',
                'start_time' => 'required|string',
                'academic_institute' => 'required|string',
                'lawyerdegrees' => 'required',
                'lawyer_bio' => 'nullable',
                'passing_year' => 'required|numeric',
                'morning_start_time' => 'nullable|string',
                'morning_end_time' => 'nullable|string',
                'morning_shift_days' => 'nullable|string',
                'afternoon_start_time' => 'nullable|string',
                'afternoon_end_time' => 'nullable|string',
                'afternoon_shift_days' => 'nullable|string',
                'evening_start_time' => 'nullable|string',
                'evening_end_time' => 'nullable|string',
                'evening_shift_days' => 'nullable|string',
                'consultation_fee' => 'nullable|numeric',
                'consultation_duration_minutes' => 'nullable|numeric',
                'password' => 'required|string',
                'confirm_password' => 'required|string|same:password'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields or duplicate value have found', 
                    'data' => $validator->errors()
                ], 422);  
            }

            //return response()->json($request->all());

            $lawyer = new Lawyer();
            $lawyer->full_name = $request->full_name;
            $lawyer->email = $request->email;
            $lawyer->phone = $request->phone;
            $lawyer->gender = $request->gender;
            $lawyer->dob = $request->dob;
            $lawyer->license_number = $request->license_number;
            $lawyer->total_experience = $request->total_experience;
            $lawyer->practice_area = $request->practice_area;
            $lawyer->current_law_firm = $request->current_law_firm; 
            $lawyer->start_time = $request->start_time;
            $lawyer->academic_institute = $request->academic_institute;
            $lawyer->lawyerdegrees = $request->lawyerdegrees;
            $lawyer->lawyer_bio = $request->lawyer_bio;
            $lawyer->passing_year = $request->passing_year; 
            $lawyer->refer_code = $request->refer_code;       
            $lawyer->password = bcrypt($request->password); 
            $lawyer->status = 'Active';
            $lawyer->save();

            $schedule = new Lawyeravailability();
            $schedule->lawyer_id = $lawyer->id;
            $schedule->morning_start_time = $request->morning_start_time;
            $schedule->morning_end_time = $request->morning_end_time;
            $schedule->morning_shift_days = $request->morning_shift_days;
            $schedule->afternoon_start_time = $request->afternoon_start_time;
            $schedule->afternoon_end_time = $request->afternoon_end_time;
            $schedule->afternoon_shift_days = $request->afternoon_shift_days;
            $schedule->evening_start_time = $request->evening_start_time;
            $schedule->evening_end_time = $request->evening_end_time;
            $schedule->evening_shift_days = $request->evening_shift_days;
            
            $schedule->save();

            $fees = new Lawyerfee();
            $fees->lawyer_id = $lawyer->id;
            $fees->consultation_fee = $request->consultation_fee;
            // $fees->followup_fees_one = $request->followup_fees_one;
            // $fees->followup_fees_two = $request->followup_fees_two;
            // $fees->discount_amount = $request->discount_amount;
            $fees->consultation_duration_minutes = $request->consultation_duration_minutes;
            //$fees->consultation_duration_month = $request->consultation_duration_month;
            $fees->save();

            DB::commit();

            return response()->json(['status'=>true, 'lawyer_id'=>intval($lawyer->id), 'message'=>'Successfully added']);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function doctorSignup(Request $request)
    {   
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'full_name' => 'required|string',
                'email' => 'required|email|unique:doctors',
                'phone' => 'required|string|unique:doctors',
                'reg_no' => 'required|string|unique:doctors',
                'dob' => 'required|string',
                'nid_passport' => 'required|string',
                'refer_code' => 'nullable|string',
                'expertise' => 'required|string',
                'degrees' => 'required|array|min:1',
                'experiences' => 'required|array|min:1',
                'morning_start_time' => 'nullable|string',
                'morning_end_time' => 'nullable|string',
                'morning_shift_days' => 'nullable|string',
                'afternoon_start_time' => 'nullable|string',
                'afternoon_end_time' => 'nullable|string',
                'afternoon_shift_days' => 'nullable|string',
                'evening_start_time' => 'nullable|string',
                'evening_end_time' => 'nullable|string',
                'evening_shift_days' => 'nullable|string',
                'consultation_fee' => 'nullable|numeric',
                'followup_fees_one' => 'nullable|numeric',
                'followup_fees_two' => 'nullable|numeric',
                'discount_amount' => 'nullable|numeric',
                'password' => 'required|string',
                'confirm_password' => 'required|string|same:password'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields or duplicate value have found', 
                    'data' => $validator->errors()
                ], 422);  
            }

            //return response()->json($request->all());

            $doctor = new Doctor();
            $doctor->title = $request->title;
            $doctor->full_name = $request->full_name;
            $doctor->email = $request->email;
            $doctor->phone = $request->phone;
            $doctor->reg_no = $request->reg_no;
            $doctor->dob = $request->dob;
            $doctor->nid_passport = $request->nid_passport;
            $doctor->refer_code = $request->refer_code;
            $doctor->expertise = $request->expertise;
            $doctor->password = bcrypt($request->password);
            $doctor->status = 'Active';
            $doctor->save();

            foreach($request->degrees as $row)
            {   
                $degree = new Doctordegree();
                $degree->doctor_id = $doctor->id;
                $degree->degree_name = $row['degree_name'];
                $degree->speciality = $row['speciality'];
                $degree->institute_name = $row['institute_name'];
                $degree->country_name = $row['country_name'];
                $degree->passing_year = $row['passing_year'];
                $degree->duration = $row['duration'];
                $degree->save();
            }

            foreach($request->experiences as $row2)
            {   
                $experience = new Doctorexperience();
                $experience->doctor_id = $doctor->id;
                $experience->type = $row2['type'];
                $experience->speciality = $row2['speciality'];
                $experience->hospital_name = $row2['hospital_name'];
                $experience->country = $row2['country'];
                $experience->start_time = $row2['start_time'];
                $experience->end_time = $row2['end_time'];
                $experience->is_continue = $row2['is_continue'];
                $experience->save();
            }

            $schedule = new Doctoravailability();
            $schedule->doctor_id = $doctor->id;
            $schedule->morning_start_time = $request->morning_start_time;
            $schedule->morning_end_time = $request->morning_end_time;
            $schedule->morning_shift_days = $request->morning_shift_days;
            $schedule->afternoon_start_time = $request->afternoon_start_time;
            $schedule->afternoon_end_time = $request->afternoon_end_time;
            $schedule->afternoon_shift_days = $request->afternoon_shift_days;
            $schedule->evening_start_time = $request->evening_start_time;
            $schedule->evening_end_time = $request->evening_end_time;
            $schedule->evening_shift_days = $request->evening_shift_days;
            
            $schedule->save();

            $fees = new Doctorfee();
            $fees->doctor_id = $doctor->id;
            $fees->consultation_fee = $request->consultation_fee;
            $fees->followup_fees_one = $request->followup_fees_one;
            $fees->followup_fees_two = $request->followup_fees_two;
            $fees->discount_amount = $request->discount_amount;
            $fees->consultation_duration_minutes = $request->consultation_duration_minutes;
            $fees->consultation_duration_month = $request->consultation_duration_month;
            $fees->save();

            DB::commit();

            return response()->json(['status'=>true, 'doctor_id'=>intval($doctor->id), 'message'=>'Successfully added']);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function addDoctorDoc(Request $request)
    {
        try
        {   

            $validator = Validator::make($request->all(), [
                'doctor_info' => 'required',
                'nid_passport_photo' => 'required',
                'doctor_photo' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $doctor = Doctor::where('phone',$request->doctor_info)->orWhere('email',$request->doctor_info)->orWhere('id',$request->doctor_info)->first();
            
            //return $doctor;
            
            if(!$doctor)
            {
                return response()->json(['status'=>false, 'doctor_id'=>0, 'message'=>'Invalid Doctor Info'],404);
            }

            if($doctor->doctordoc)
            {
                return response()->json(['status'=>false, 'doctor_id'=>0, 'message'=>'Already doc uploaded'],400);
            }

            if($request->file('nid_passport_photo'))
            {   
                $file = $request->file('nid_passport_photo');
                $name = time().$request->doctor_id.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/doctors/nid_passport', $name); 
                $pathOne = 'uploads/doctors/nid_passport/'.$name;
            }


            if($request->file('doctor_photo'))
            {   
                $file = $request->file('doctor_photo');
                $name = time().$request->doctor_id.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/doctors/doctor_photo', $name); 
                $pathTwo = 'uploads/doctors/doctor_photo/'.$name;
            }

            $doc = new Doctordoc();
            $doc->doctor_id = $doctor->id;
            $doc->nid_passport_photo = $pathOne;
            $doc->doctor_photo = $pathTwo;
            $doc->save();

            return response()->json(['status'=>true, 'doctor_id'=>intval($doc->doctor_id), 'message'=>'Successfully doc uploaded']);
            
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function checkDoctorDoc(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'info' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $doctor = Doctor::where('phone',$request->info)->orWhere('email',$request->info)->orWhere('id',$request->info)->first();

            if(!$doctor)
            {
                return response()->json(['status'=>false, 'doctor_id'=>0, 'message'=>'Invalid Info'],400);
            }

            if(!$doctor->doctordoc)
            {
                return response()->json(['status'=>false, 'doctor_id'=>intval($doctor->id), 'message'=>'No doc found'],200);
            }

            return response()->json(['status'=>false, 'doctor_id'=>0, 'message'=>'Already doc uploaded'],400);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function serviceProviderSignin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
                //'use_for' => 'required|in:doctor,rider'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $login = $request->input('login');
            $password = $request->input('password');

            $doctor = Doctor::where('email',$request->login)->orWhere('phone',$request->login)->first();

            $rider = Rider::where('email',$request->login)->orWhere('phone',$request->login)->first();

            $lawyer = Lawyer::where('email',$request->login)->orWhere('phone',$request->login)->first();

            //return $rider;

            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if($doctor)
            {
                if(Auth::guard('doctor')->attempt([$fieldType => $login, 'password' => $password])) {
                    $doctor = Auth::guard('doctor')->user();
                    $doctor->load('doctoravailability','doctordegrees','doctorexperiences','doctordoc','doctorfee');
                    //return $doctor;
                    if($doctor->status == 'Inactive')
                    {
                        return response()->json(['status'=>false, 'role' => "", 'message'=>'Your account not active yet', 'token'=>"", 'data'=>new \stdClass()],403);
                    }
                    if(!$doctor->doctordoc)
                    {
                        return response()->json(['status'=>false, 'role' => "doctor", 'message'=>'No Documents found', 'token'=>"", 'data'=>new \stdClass()],404);
                    }
                    $token = $doctor->createToken('MyApp')->plainTextToken;
                    return response()->json([
                        'status' => true, 
                        'role' => "doctor",
                        'message' => 'Successfully Logged IN', 
                        'token' => $token, 
                        'data' => $doctor
                    ]);
                }
            }elseif($rider){
                if(Auth::guard('rider')->attempt([$fieldType => $login, 'password' => $password])) {
                    $rider = Auth::guard('rider')->user();
                    $rider->load('riderdoc','riderarea','regseries');
                    //return $doctor;
                    if($rider->status == 'Inactive') 
                    {
                        return response()->json(['status'=>false, 'role' => "", 'message'=>'Your account not active yet', 'token'=>"", 'data'=>new \stdClass()],403);
                    }
                    if(!$rider->riderdoc)
                    {
                        return response()->json(['status'=>false, 'role' => "rider", 'message'=>'No Documents found', 'token'=>"", 'data'=>$rider],404);
                    }
                    $token = $rider->createToken('MyApp')->plainTextToken;
                    return response()->json([
                        'status' => true, 
                        'role' => "rider",
                        'message' => 'Successfully Logged IN', 
                        'token' => $token, 
                        'data' => $rider
                    ]);
                }
            }elseif($lawyer){
                if(Auth::guard('lawyer')->attempt([$fieldType => $login, 'password' => $password])) {
                    $lawyer = Auth::guard('lawyer')->user();
                    $lawyer->load('lawyerdoc','lawyeravailability','lawyerfee');
                    //return $doctor;
                    if($lawyer->status == 'Inactive') 
                    {
                        return response()->json(['status'=>false, 'role' => "", 'message'=>'Your account not active yet', 'token'=>"", 'data'=>new \stdClass()],403);
                    }
                    if(!$lawyer->lawyerdoc)
                    {
                        return response()->json(['status'=>false, 'role' => "lawyer", 'message'=>'No Documents found', 'token'=>"", 'data'=>$lawyer],404);
                    }
                    $token = $lawyer->createToken('MyApp')->plainTextToken;
                    $lawyer->activation_status = 'Online';
                    $lawyer->update();
                    return response()->json([
                        'status' => true, 
                        'role' => "lawyer",
                        'message' => 'Successfully Logged IN', 
                        'token' => $token, 
                        'data' => $lawyer
                    ]);
                }
            }

            
            return response()->json(['status'=>false, 'role'=>"", 'message'=>'Invalid Email/Mobile or Password', 'token'=>"", 'data'=> new \stdClass()],400);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function doctorSignout(Request $request)
    {
        try
        {
            $user = auth()->user();
            $user->tokens()->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully Logged Out']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function doctorInfos(Request $request)
    {
        try
        {
            $query = Dcinfo::query();
            if($request->has('category'))
            {
                $query->where('category',$request->category);
            }
            $data = $query->where('status','Active')->latest()->get();
            return response()->json(['status'=>count($data)>0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function news()
    {
        try
        {
            $news = DB::table('news_urls')->get();
            return response()->json(['status'=>count($news)>0, 'base_url'=>url('/'), 'data'=>$news]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    
    public function doctorDetails()
    {
        try
        {   
            $user = user();
            $doctor = Doctor::with('doctoravailability','doctordegrees','doctorexperiences','doctordoc','doctorfee')->where('id',$user->id)->first();
            return response()->json(['status'=>true, 'data'=>$doctor]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    
    public function editDoctorAccount(Request $request)
    {   
        DB::beginTransaction();
        try
        {   
            $doctor = user();
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'full_name' => 'required|string',
                'email' => 'required|email|unique:doctors,email,' . $doctor->id,
                'phone' => 'required|string|unique:doctors,phone,' . $doctor->id,
                'reg_no' => 'required|string|unique:doctors,reg_no,' . $doctor->id,
                'dob' => 'required|string',
                'nid_passport' => 'required|string|unique:doctors,nid_passport,' . $doctor->id,
                'refer_code' => 'nullable|string',
                'nid_passport_photo' => 'nullable',
                'doctor_photo' => 'nullable',
                'expertise' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
             if($request->file('nid_passport_photo'))
            {   
                $file = $request->file('nid_passport_photo');
                $name = time().$request->doctor_id.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/doctors/nid_passport', $name); 
                $pathOne = 'uploads/doctors/nid_passport/'.$name;
            }else{
                $pathOne = $doctor->doctordoc->nid_passport_photo;
            }


            if($request->file('doctor_photo'))
            {   
                $file = $request->file('doctor_photo');
                $name = time().$request->doctor_id.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/doctors/doctor_photo', $name); 
                $pathTwo = 'uploads/doctors/doctor_photo/'.$name;
            }else{
                $pathTwo = $doctor->doctordoc->doctor_photo;
            }

            $doctor->title = $request->title;
            $doctor->full_name = $request->full_name;
            $doctor->email = $request->email;
            $doctor->phone = $request->phone;
            $doctor->reg_no = $request->reg_no;
            $doctor->dob = $request->dob;
            $doctor->nid_passport = $request->nid_passport;
            $doctor->refer_code = $request->refer_code;
            $doctor->expertise = $request->expertise;
            $doctor->update();

            $doc = $doctor->doctordoc;
            $doc->nid_passport_photo = $pathOne;
            $doc->doctor_photo = $pathTwo;
            $doc->update();

            DB::commit();

            return response()->json(['status'=>true, 'message'=>'Successfully your has been updated']);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function editDoctorEducation(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'degree_id' => 'required|integer|exists:doctordegrees,id',
                'degree_name' => 'required|string',
                'speciality' => 'required|string',
                'institute_name' => 'required|string',
                'country_name' => 'required|string',
                'passing_year' => 'required|numeric',
                'duration' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $degree = Doctordegree::findorfail($request->degree_id);
            $degree->degree_name = $request->degree_name;
            $degree->speciality = $request->speciality;
            $degree->institute_name = $request->institute_name;
            $degree->country_name = $request->country_name;
            $degree->passing_year = $request->passing_year;
            $degree->duration = $request->duration;
            $degree->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your degree info has been updated']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function editDoctorExperience(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'experience_id' => 'required|integer|exists:doctorexperiences,id',
                'type' => 'nullable|string',
                'speciality' => 'required|string',
                'hospital_name' => 'required|string',
                'country' => 'required|string',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'is_continue' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $experience = Doctorexperience::findorfail($request->experience_id);
            $experience->type = $request->type;
            $experience->speciality = $request->speciality;
            $experience->hospital_name = $request->hospital_name;
            $experience->country = $request->country;
            $experience->start_time = $request->start_time;
            $experience->end_time = $request->end_time;
            $experience->is_continue = $request->is_continue;
            $experience->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your experience info has been updated']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function editDoctorSlot(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'slot_id' => 'required|integer|exists:doctoravailabilities,id',
                'morning_start_time' => 'required|string',
                'morning_end_time' => 'required|string',
                'morning_shift_days' => 'required|string',
                'afternoon_start_time' => 'required|string',
                'afternoon_end_time' => 'required|string',
                'afternoon_shift_days' => 'required|string',
                'evening_start_time' => 'required|string',
                'evening_end_time' => 'required|string',
                'evening_shift_days' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $slot = Doctoravailability::findorfail($request->slot_id);
            $slot->morning_start_time = $request->morning_start_time;
            $slot->morning_end_time = $request->morning_end_time;
            $slot->morning_shift_days = $request->morning_shift_days;
            $slot->afternoon_start_time = $request->afternoon_start_time;
            $slot->afternoon_end_time = $request->afternoon_end_time;
            $slot->afternoon_shift_days = $request->afternoon_shift_days;
            $slot->evening_start_time = $request->evening_start_time;
            $slot->evening_end_time = $request->evening_end_time;
            $slot->evening_shift_days = $request->evening_shift_days;
            $slot->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your time slot info has been updated']);
            
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function deleteDoctorEducation($id)
    {
        try
        {
            $degree = Doctordegree::findorfail($id);
            $degree->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the degree has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function deleteDoctorExperience($id)
    {
        try
        {
            $experience = Doctorexperience::findorfail($id);
            $experience->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the experience has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    
    public function addDoctorDegree(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'degree_name' => 'required|string',
                'speciality' => 'required|string',
                'institute_name' => 'required|string',
                'country_name' => 'required|string',
                'passing_year' => 'required|numeric',
                'duration' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
            
            $user = user();
            //return user()->id;

            $degree = new Doctordegree();
            $degree->doctor_id = $user->id;
            $degree->degree_name = $request->degree_name;
            $degree->speciality = $request->speciality;
            $degree->institute_name = $request->institute_name;
            $degree->country_name = $request->country_name;
            $degree->passing_year = $request->passing_year;
            $degree->duration = $request->duration;
            $degree->save();

            return response()->json(['status'=>true, 'doctor_id'=>intval($degree->id), 'message'=>'Successfully a degree has been added']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function addDoctorExperience(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'speciality' => 'required|string',
                'hospital_name' => 'required|string',
                'country' => 'required|string',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'is_continue' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $experience = new Doctorexperience();
            $experience->doctor_id = user()->id;
            $experience->speciality = $request->speciality;
            $experience->hospital_name = $request->hospital_name;
            $experience->country = $request->country;
            $experience->start_time = $request->start_time;
            $experience->end_time = $request->end_time;
            $experience->is_continue = $request->is_continue;
            $experience->save();

            return response()->json(['status'=>true, 'experience_id'=>intval($experience->id), 'message'=>'Successfully an experience has been added']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function doctorLists(Request $request)
    {
        try {
            $data = Doctor::with('doctoravailability','doctordegrees','doctorexperiences','doctordoc','doctorfee')
                          ->whereNotNull('type')
                          ->latest()
                          ->paginate(15);

            return response()->json($data);
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'code'   => $e->getCode(),
                'message'=> $e->getMessage()
            ], 500);
        }
    }

    
    public function changePassword(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = user();

            

            if (!Hash::check($request->current_password, $user->password)) {
    

                return response()->json(['status'=>false, 'message'=>"The current password is not matched"],400);
            }

            $user->password = Hash::make($request->new_password);
            $user->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your password has been changed']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function deleteDoctorAccount()
    {
        try
        {
            $doctor = user();
           // $doctor->doctordegrees()->delete();
            $doctor->doctorexperiences()->delete();
            $doctor->doctoravailability()->delete();
            $doctor->doctorfee()->delete();
            if($doctor->doctordoc)
            {   
                $doc = $doctor->doctordoc;
                 if ($doc->nid_passport_photo && file_exists(public_path($doc->nid_passport_photo))) {
                    unlink(public_path($doc->nid_passport_photo));
                }
                if ($doc->doctor_photo && file_exists(public_path($doc->doctor_photo))) {
                    unlink(public_path($doc->doctor_photo));
                }
            } 
            // if(count($doctor->doctordocs) > 0)
            // {
            //     foreach($doctor->doctordocs as $doc)
            //     {
            //         if ($doc->nid_passport_photo && file_exists(public_path($doc->nid_passport_photo))) {
            //             unlink(public_path($doc->nid_passport_photo));
            //         }
            //         if ($doc->doctor_photo && file_exists(public_path($doc->doctor_photo))) {
            //             unlink(public_path($doc->doctor_photo));
            //         }
            //     }
            // }
            $doctor->doctorappointments()->delete();          
            $doctor->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the account has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function updateDoctorType(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [
                'about' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
            $doctor = user();
            $doctor->about = $request->about;
            $doctor->update();
            return response()->json(['status'=>true, 'doctor_id'=>intval($doctor->id), 'message'=>'Suceessfully Updated']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    
    public function doctorAppointment(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'appointment_date' => 'required|date_format:Y-m-d',
                'shift' => 'required|in:morning,afternoon,evening',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $doctor = Doctor::with('doctoravailability','doctorfee')->findorfail($request->doctor_id);

            //$doctor->doctorfee->consultation_duration_minutes;

            //$timeVal = $doctor->doctorfee->consultation_duration_minutes * $pCount;

            //return $pCount;

            // $userExist = Doctorappointment::where('user_id',user()->id)->where('appointment_date',$request->appointment_date)->where('doctor_id',$request->doctor_id)->where('status','Booked')->first();

            // if($userExist)
            // {
            //     return response()->json(['status'=>false, 'message'=>"Alreay you have booked in the time slot", 'data'=> new \stdClass()],410);
            // }



            $appointment_date = strtotime($request->appointment_date);
            $getDay = date("D", $appointment_date);
            $fullDay = date("l", $appointment_date);

            $pCount = Doctorappointment::where('appointment_date',$request->appointment_date)->where('shift',$request->shift)->where('doctor_id',$request->doctor_id)->count();

            $pCount+=1;

            $timeOne = "";
            $timeTwo = "";
            if($request->shift == 'morning')
            {
                $doctorAvailability = in_array($getDay, $doctor->doctoravailability->morning_shift_days)?1:0;
                $timeOne = $doctor->doctoravailability->morning_start_time;
                $timeTwo = $doctor->doctoravailability->morning_end_time;
            }elseif($request->shift == 'afternoon'){
                $doctorAvailability = in_array($getDay, $doctor->doctoravailability->afternoon_shift_days)?1:0;
                $timeOne = $doctor->doctoravailability->afternoon_start_time;
                $timeTwo = $doctor->doctoravailability->afternoon_end_time;
            }elseif($request->shift == 'evening'){
                $doctorAvailability = in_array($getDay, $doctor->doctoravailability->evening_shift_days)?1:0;
                $timeOne = $doctor->doctoravailability->evening_start_time;
                $timeTwo = $doctor->doctoravailability->evening_end_time;
            }

            //return $timeOne." ".$timeTwo;

            $startTime = strtotime($timeOne);
            $endTime   = strtotime($timeTwo);

            // If end time is earlier than start, assume it's the next day
            if ($endTime < $startTime) {
                $endTime += 24 * 60 * 60; // add 24 hours
            }

            $duration = ($endTime - $startTime) / 60;

            //return $duration;

            $countVal = $doctor->doctorfee->consultation_duration_minutes * $pCount;

            //return $countVal;

            if($countVal > $duration)
            {
                return response()->json(['status'=>false, 'message'=>"You can't booking because already too many patient has been booked", 'data'=> new \stdClass()],400);
            }

            if($doctorAvailability == 0)
            {
                return response()->json(['status'=>false, 'message'=>"Doctor is not available in $fullDay", 'data'=> new \stdClass()],400);
            }

            $query = Doctorappointment::query();

            if($request->shift == 'morning')
            {
                $query->where('shift','morning');
            }elseif($request->shift == 'afternoon'){
                $query->where('shift','afternoon');
            }elseif($request->shift == 'evening'){
                $query->where('shift','evening');
            }

            $serial = $query->where('appointment_date',$request->appointment_date)->count();

            $serial+=1;

            //return $serial;

            $book = new Doctorappointment();
            $book->user_id = user()->id;
            $book->doctor_id = $request->doctor_id;
            $book->serial = $serial;
            $book->shift = $request->shift;
            $book->appointment_date = $request->appointment_date;
            $book->booking_date = date('Y-m-d');
            $book->booking_time = date('h:i:s a');
            $book->appointment_day = $fullDay;
            $book->timestamp = time();
            $book->status = 'Booked';
            $book->save();

            
            $paths = [];
            if ($request->hasFile('previous_documents')) {
                

                foreach ($request->file('previous_documents') as $key=>$image) {
                    $imageName = time() . $key+1 . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/patients'), $imageName);

                    $paths[] = 'uploads/patients/' . $imageName;
                }

                //return $paths; 
            }

            //return $paths;


            $info = new Patientinfo();
            $info->doctorappointment_id = $book->id;
            $info->patient_name = $request->patient_name;
            $info->patient_age = $request->patient_age;
            $info->patient_weight = $request->patient_weight;
            $info->symptoms = $request->symptoms;
            $info->previous_documents = json_encode($paths);
            $info->save();

            DB::commit();

            return response()->json(['status'=>true, 'message'=>'Successfully take the appointment', 'data'=>$book]);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function saveLawyerAppointment(Request $request)
    {
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'lawyer_id' => 'required|integer|exists:lawyers,id',
                'appointment_date' => 'required|date_format:Y-m-d',
                'shift' => 'required|in:morning,afternoon,evening',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $lawyer = Lawyer::with('lawyeravailability','lawyerfee')->findorfail($request->lawyer_id);



            $appointment_date = strtotime($request->appointment_date);
            $getDay = date("D", $appointment_date);
            $fullDay = date("l", $appointment_date);

            $pCount = Lawyerappointment::where('appointment_date',$request->appointment_date)->where('shift',$request->shift)->where('lawyer_id',$request->lawyer_id)->count();

            $pCount+=1;

            $timeOne = "";
            $timeTwo = "";
            if($request->shift == 'morning')
            {
                $lawyerAvailability = in_array($getDay, $lawyer->lawyeravailability->morning_shift_days)?1:0;
                $timeOne = $lawyer->lawyeravailability->morning_start_time;
                $timeTwo = $lawyer->lawyeravailability->morning_end_time;
            }elseif($request->shift == 'afternoon'){
                $lawyerAvailability = in_array($getDay, $lawyer->lawyeravailability->afternoon_shift_days)?1:0;
                $timeOne = $lawyer->lawyeravailability->afternoon_start_time;
                $timeTwo = $lawyer->lawyeravailability->afternoon_end_time;
            }elseif($request->shift == 'evening'){
                $lawyerAvailability = in_array($getDay, $lawyer->lawyeravailability->evening_shift_days)?1:0;
                $timeOne = $lawyer->lawyeravailability->evening_start_time;
                $timeTwo = $lawyer->lawyeravailability->evening_end_time;
            }

            //return $timeOne." ".$timeTwo;

            $startTime = strtotime($timeOne);
            $endTime   = strtotime($timeTwo);

            // If end time is earlier than start, assume it's the next day
            if ($endTime < $startTime) {
                $endTime += 24 * 60 * 60; // add 24 hours
            }

            $duration = ($endTime - $startTime) / 60;

            //return $duration;

            $countVal = $lawyer->lawyerfee->consultation_duration_minutes * $pCount;

            //return $countVal;

            if($countVal > $duration)
            {
                return response()->json(['status'=>false, 'message'=>"You can't booking because already too many patient has been booked", 'data'=> new \stdClass()],400);
            }

            if($lawyerAvailability == 0)
            {
                return response()->json(['status'=>false, 'message'=>"Lawyer is not available in $fullDay", 'data'=> new \stdClass()],400);
            }

            $query = Lawyerappointment::query();

            if($request->shift == 'morning')
            {
                $query->where('shift','morning');
            }elseif($request->shift == 'afternoon'){
                $query->where('shift','afternoon');
            }elseif($request->shift == 'evening'){
                $query->where('shift','evening');
            }

            $serial = $query->where('appointment_date',$request->appointment_date)->count();

            $serial+=1;

            //return $serial;

            $book = new Lawyerappointment();
            $book->user_id = user()->id;
            $book->lawyer_id = $request->lawyer_id;
            $book->serial = $serial;
            $book->shift = $request->shift;
            $book->appointment_date = $request->appointment_date;
            $book->booking_date = date('Y-m-d');
            $book->booking_time = date('h:i:s a');
            $book->appointment_day = $fullDay;
            $book->timestamp = time();
            $book->status = 'Booked';
            $book->save();

            
            $paths = [];
            if ($request->hasFile('previous_documents')) {
                

                foreach ($request->file('previous_documents') as $key=>$image) {
                    $imageName = time() . $key+1 . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/lawyer_users'), $imageName);

                    $paths[] = 'uploads/lawyer_users/' . $imageName;
                }

                //return $paths; 
            }

            //return $paths;


            $info = new Userinfo();
            $info->lawyerappointment_id = $book->id;
            $info->user_name = $request->user_name;
            $info->user_age = $request->user_age;
            $info->user_weight = $request->user_weight;
            $info->remarks = $request->remarks;
            $info->previous_documents = json_encode($paths);
            $info->save();

            DB::commit();

            return response()->json(['status'=>true, 'message'=>'Successfully take the appointment', 'data'=>$book]);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerAppointmentLists(Request $request)
    {
        try
        {   
            $date = date('Y-m-d');
            $data = Lawyerappointment::with('userinfo')->where('lawyer_id',user()->id)->where('appointment_date','>=',$date)->where('status','Booked')->orderBy('appointment_date','ASC')->paginate(15);
            return response()->json($data);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        } 
    }

    public function appointmentLists(Request $request)
    {
        try
        {   
            $date = date('Y-m-d');
            $data = Doctorappointment::with('patientinfo')->where('doctor_id',user()->id)->where('appointment_date','>=',$date)->where('status','Booked')->orderBy('appointment_date','ASC')->paginate(15);
            return response()->json($data);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function saveDoctorReview(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'rating' => 'required|numeric',
                'review_text' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $rate = new Doctorrating();
            $rate->doctor_id = $request->doctor_id;
            $rate->user_id = user()->id;
            $rate->rating = $request->rating;
            $rate->review_text = $request->review_text;
            $rate->save();

            return response()->json(['status'=>true, 'rate_id'=>intval($rate->id), 'message'=>"Successfully a rating has been added"]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function doctorRatings(Request $request)
    {
        try
        {
            $query = Doctorrating::query();

            if($request->has('user_id'))
            {
                $query->where('user_id',$request->user_id);
            }

            if($request->has('doctor_id'))
            {
                $query->where('doctor_id',$request->doctor_id);
            }

            $ratings = $query->with('user')->latest()->get(); 

            return response()->json(['status'=>count($ratings)>0, 'total'=>count($ratings), 'data'=>$ratings]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function getDoctorDetails($id)
    {
        try
        {
            $doctor = Doctor::with('doctordegrees','doctordoc','doctorfee','doctorexperiences')->findorfail($id);
            return response()->json(['status'=>true, 'base_url'=>url('/'), 'data'=>$doctor]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function doctorStatusUpdate(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'status' => 'required|in:Active,Inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
            
            $doctor = Doctor::findorfail($request->doctor_id);
            $doctor->status = $request->status;
            $doctor->update();
            
            return response()->json(['status'=>true, 'message'=>"Successfully the doctor's status has been updated"]);
            
            
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    public function searchDoctor(Request $request)
    {
        //
    }

    public function myAppointmentLists()
    {
        try{
            $data = Doctorappointment::with('doctor.doctordoc','patientinfo','doctor.doctorfee')->where('user_id',user()->id)->where('status','booked')->get();
            return response()->json(['status'=>count($data)>0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function savePrescription(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'doctorappointment_id' => 'required|integer|exists:doctorappointments,id|unique:prescriptions',
                'user_id' => 'required|integer|exists:users,id',
                'tests' => 'nullable|array',
                'medicines' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }
            $prescription = new Prescription();
            $prescription->user_id = $request->user_id;
            $prescription->symptoms = $request->symptoms;
            $prescription->suggestion = $request->suggestion;
            $prescription->doctorappointment_id = $request->doctorappointment_id;
            $prescription->date = date('Y-m-d');
            $prescription->time = date('h:i:s a');
            $prescription->save();

            if(count($request->medicines) > 0)
            {
                foreach($request->medicines as $row){
                    $medicine = new Medicine();
                    $medicine->prescription_id = $prescription->id;
                    $medicine->medicine_name = $row['medicine_name'];
                    $medicine->medicine_time = $row['medicine_time'];
                    $medicine->medicine_rules = $row['medicine_rules'];
                    $medicine->duration = $row['duration'];
                    $medicine->duration_unit = $row['duration_unit'];
                    $medicine->special_instructions = $row['special_instructions'];
                    $medicine->save();
                }
            }

            

            if(count($request->tests) > 0)
            {
                foreach($request->tests as $row2){
                    $test = new Prescriptiontest();
                    $test->prescription_id = $prescription->id;
                    $test->test_name = $row2['test_name'];
                    $test->save();
                }
            }

            DB::commit();

            return response()->json(['status'=>true, 'prescription'=>intval($prescription->id), 'message'=>'Successfully a prescription has been added']);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function myPrescriptions(Request $request)
    {
        try
        {
            $data = Prescription::with('medicines','prescriptiontests')->where('user_id',user()->id)->get();
            return response()->json(['status'=>count($data)>0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function prescriptionDetails($id)
    {
        try
        {   

            $prescription = Prescription::with('medicines','prescriptiontests','doctorappointment.patientinfo')->where('doctorappointment_id',$id)->first();

            if(!$prescription){
                return response()->json(['status'=>false, 'data'=>new \stdClass()]);
            }

            return response()->json(['status'=>true, 'data'=>$prescription]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function riderZones()
    {
        try
        {
            $data = Riderarea::where('status','Active')->get();
            return response()->json(['status'=>count($data)>0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function regSeries()
    {
        try
        {
            $data = Regseries::where('status','Active')->get();
            return response()->json(['status'=>count($data)>0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function riderSignup(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'full_name'       => 'required|string',
                //'email'           => 'nullable|email|unique:riders,email|required_without:phone',
                //'phone'           => 'nullable|string|unique:riders,phone|required_without:email',
                'email'           => 'required|email|unique:riders,email',
                'phone'           => 'required|string|unique:riders,phone',
                'riderarea_id'    => 'required|integer|exists:riderareas,id',
                'nid_passport'    => 'required|string|unique:riders,nid_passport',
                'dob'             => 'required|date_format:Y-m-d',
                'gender'          => 'required|in:Male,Female,Others',
                'vehicle'         => 'required|string',
                'license_number'  => 'required|string',
                'regseries_id'    => 'required|integer|exists:regseries,id',
                'reg_no'          => 'required|string',
                'refer_code'      => 'nullable|string',
                'password'        => 'required|string|min:6',
                'confirm_password'=> 'required|string|same:password'
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $rider = new Rider();
            $rider->full_name = $request->full_name;
            $rider->email = $request->email;
            $rider->phone = $request->phone;
            $rider->riderarea_id = $request->riderarea_id;
            $rider->nid_passport = $request->nid_passport;
            $rider->dob = $request->dob;
            $rider->gender = $request->gender;
            $rider->vehicle = $request->vehicle;
            $rider->license_number = $request->license_number;
            $rider->regseries_id = $request->regseries_id;
            $rider->reg_no = $request->reg_no;
            $rider->refer_code = $request->refer_code;
            $rider->reffaral_code = $request->phone;
            $rider->status = 'Active';
            $rider->password = bcrypt($request->password);
            $rider->save();

            return response()->json(['status'=>true, 'rider_id'=>intval($rider->id), 'message'=>"Successfully a rider has been added"]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function searchUser(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [
                'search' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = User::where('email',$request->search)->orWhere('mobile',$request->search)->first();
            if(!$user){
                return response()->json(['status'=>false, 'data'=>new \stdClass()],404);
            }
            return response()->json(['status'=>true, 'data'=>$user],200);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function riderDocUpload(Request $request) 
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'rider_id' => 'required|integer|exists:riders,id', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $rider = Rider::findorfail($request->rider_id);

            if($rider->riderdoc){
                return response()->json(['status'=>false, 'doc_id'=>0, 'rider_id'=>0, 'message'=>'Already doc uploaded'],400);
            }

            $count = Rider::count();
            $count+=1;

            if($request->file('nid_front_photo'))
            {   
                $file = $request->file('nid_front_photo');
                $name = time()."nid_front".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $nid_front_photo = 'uploads/riders/'.$name;
            }else{
                $nid_front_photo = $rider->riderdoc?$rider->riderdoc->nid_front_photo:null; 
            }


            if($request->file('nid_back_photo'))
            {   
                $file = $request->file('nid_back_photo');
                $name = time()."nid_back".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $nid_back_photo = 'uploads/riders/'.$name;
            }else{
                $nid_back_photo = $rider->riderdoc?$rider->riderdoc->nid_back_photo:null; 
            }


            if($request->file('driving_license_one'))
            {   
                $file = $request->file('driving_license_one');
                $name = time()."driving_license_one".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $driving_license_one = 'uploads/riders/'.$name;
            }else{
                $driving_license_one = $rider->riderdoc?$rider->riderdoc->driving_license_one:null; 
            }


            if($request->file('driving_license_two'))
            {   
                $file = $request->file('driving_license_two');
                $name = time()."driving_license_two".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $driving_license_two = 'uploads/riders/'.$name;
            }else{
                $driving_license_two = $rider->riderdoc?$rider->riderdoc->driving_license_two:null; 
            }


            if($request->file('vehicle_license_one'))
            {   
                $file = $request->file('vehicle_license_one');
                $name = time()."vehicle_license_one".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $vehicle_license_one = 'uploads/riders/'.$name;
            }else{
                $vehicle_license_one = $rider->riderdoc?$rider->riderdoc->vehicle_license_one:null; 
            }


            if($request->file('vehicle_license_two'))
            {   
                $file = $request->file('vehicle_license_two');
                $name = time()."vehicle_license_two".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $vehicle_license_two = 'uploads/riders/'.$name;
            }else{
                $vehicle_license_two = $rider->riderdoc?$rider->riderdoc->vehicle_license_two:null; 
            }


            if($request->file('profile_image'))
            {   
                $file = $request->file('profile_image');
                $name = time()."profile_image".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/riders/', $name); 
                $profile_image = 'uploads/riders/'.$name;
            }else{
                $profile_image = $rider->riderdoc?$rider->riderdoc->profile_image:null; 
            }

            $doc = new Riderdoc();
            $doc->rider_id = $rider->id;
            $doc->nid_front_photo = $nid_front_photo;
            $doc->nid_back_photo = $nid_back_photo;
            $doc->driving_license_one = $driving_license_one;
            $doc->driving_license_two = $driving_license_two;
            $doc->vehicle_license_one = $vehicle_license_one;
            $doc->vehicle_license_two = $vehicle_license_two;
            $doc->profile_image = $profile_image;
            $doc->save();

            return response()->json(['status'=>true, 'doc_id'=>intval($doc->id), 'rider_id'=>intval($rider->id), 'message'=>'Successfully Uploaded']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }


    public function lawyerDocUpload(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'lawyer_id' => 'required|integer|exists:lawyers,id', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            } 


            $lawyer = Lawyer::findorfail($request->lawyer_id);

            $count = Lawyer::count();
            $count+=1;

            if($request->file('nid_front_photo'))
            {   
                $file = $request->file('nid_front_photo');
                $name = time()."nid_front".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $nid_front_photo = 'uploads/lawyers/'.$name;
            }else{
                $nid_front_photo = $lawyer->lawyerdoc?$lawyer->lawyerdoc->nid_front_photo:null; 
            }


            if($request->file('nid_back_photo'))
            {   
                $file = $request->file('nid_back_photo');
                $name = time()."nid_back".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $nid_back_photo = 'uploads/lawyers/'.$name;
            }else{
                $nid_back_photo = $lawyer->lawyerdoc?$lawyer->lawyerdoc->nid_back_photo:null; 
            }


            if($request->file('license_certificate'))
            {   
                $file = $request->file('license_certificate');
                $name = time()."license_certificate".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $license_certificate = 'uploads/lawyers/'.$name;
            }else{
                $license_certificate = $lawyer->lawyerdoc?$lawyer->lawyerdoc->license_certificate:null; 
            }


            if($request->file('profile'))
            {   
                $file = $request->file('profile');
                $name = time()."profile".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $profile = 'uploads/lawyers/'.$name;
            }else{
                $profile = $lawyer->lawyerdoc?$lawyer->lawyerdoc->profile:null; 
            }


            $paths = [];
            if ($request->hasFile('documents')) {
                

                foreach ($request->file('documents') as $key=>$image) {
                    $imageName = time() . $key+1 . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/documents'), $imageName);

                    $paths[] = 'uploads/documents/' . $imageName;
                }

                //return $paths; 
            }

            $doc = new Lawyerdoc();
            $doc->lawyer_id = $lawyer->id;
            $doc->license_certificate = $license_certificate;
            $doc->nid_front_photo = $nid_front_photo;
            $doc->nid_back_photo = $nid_back_photo;
            $doc->documents = json_encode($paths);
            $doc->profile = $profile;
            $doc->save();

            return response()->json(['status'=>true, 'doc_id'=>intval($doc->id), 'lawyer_id'=>intval($lawyer->id), 'message'=>'Successfully Uploaded']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerDegress()
    {
        try
        {
            $data = Lawyerdegree::where('status','Active')->get();
            return response()->json(['status'=>count($data) > 0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function userDetails()
    {
        try
        {
            $user = user();
            //$user->load('riderdoc','riderarea','regseries');
            return response()->json(['status'=>true, 'data'=>$user]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function userDeleteAccount()
    {
        try
        {
            $user = user();
            $user->delete();
            return response()->json(['status'=>true, 'message'=>"Successfully the user's account has been deleted"]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function appNotifications(Request $request)
    {
        try
        {
            $query = Appnotify::query();
            if($request->has('date')){
                $query->where('date',date('Y-m-d'));
            }
            $data = $query->latest()->paginate(20);
            return response()->json(['status'=>count($data) > 0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function userProfileUpdate(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                //'user_id' => 'required|integer|exists:users,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'password' => 'nullable|string',
                'address' => 'nullable', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Duplicate Value found or Invalid Information Provided', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $count = User::count();
            $count+=1;

            $user = user();

            

            if($user)
            {
                $profile = DB::table('profile')->where('email',$user->email)->first();

            //return $profile;

                $user_type = $profile?$profile->type:"0";
            }

            $pictureUploaded = false;

            //return $user_type;

            if ($request->file('image')) {   
                $file = $request->file('image');
                $name = time() . $count . $file->getClientOriginalName();

                $sizeInBytes = $file->getSize();
                $sizeInMB = $sizeInBytes / 1024 / 1024;

                // // Example: Limit to 1 MB
                // if ($sizeInMB <= 2) {
                //     return response()->json(['status'=>false, 'user_id'=>0, 'message'=>"Failed must less than 2MB", 'data'=>new \stdClass()],503);
                // }

                $file->move(public_path('/uploads/users/'), $name); 
                //$path = public_path('uploads/users/' . $name);
                $path = 'uploads/users/' . $name;

                if ($user_type == 1) {
                    

                    //return "dhdhdh";

                    // echo or return $response if needed


                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://union-express.online/api/user/transfer-user-image',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS => array('user_id' => $user->id,'url' => url('/')."/".$path),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $result = json_decode($response,true);

                    if($result['status'] == true){
                        $pictureUploaded = true;
                        //$path = $result['path'];
                        unlink(public_path($path));
                    }

                    //return response()->json($result);
                }


            } else {
                $path = $user->picture; // Must convert to absolute path
            }

            
            
            

            
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->address = $request->address;
            $user->password = $request->has('password')?bcrypt($request->password):$user->password;
            $user->password_two = $request->has('password')?md5($request->password):$user->password_two;
            if($pictureUploaded == false){
                $user->picture = $path;
            }
            
            $user->update();

            // if ($request->file('image')) {
                
            // }

            $getUser = User::findorfail($user->id);
            

            return response()->json(['status'=>true, 'user_id'=>intval($user->id), 'message'=>"Successfully your profile has been updated", 'data'=>$getUser]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerProfileUpdate(Request $request)
    {   
        DB::beginTransaction();
        try
        {  

            $lawyer = user();
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string',
                'email' => 'required|email|unique:lawyers,email,' . $lawyer->id,
                'phone' => 'required|string|unique:lawyers,phone,' . $lawyer->id,
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'dob' => 'required|date_format:Y-m-d',
                //'license_number' => 'required|string|unique:lawyers',
                'license_number' => 'required|string|unique:lawyers,license_number,' . $lawyer->id,
                'total_experience' => 'required|string',
                'practice_area' => 'required|string',
                'current_law_firm' => 'nullable|string',
                'start_time' => 'required|string',
                'academic_institute' => 'required|string',
                'lawyerdegrees' => 'required',
                'lawyer_bio' => 'nullable',
                'passing_year' => 'required|numeric',
                'nid_front_photo' => 'nullable',
                'nid_back_photo' => 'nullable',
                'license_certificate' => 'nullable',
                'profile' => 'nullable',
                'documents' => 'nullable',
                'password' => 'nullable|string',
                'confirm_password' => 'nullable|string|same:password'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields or duplicate value have found', 
                    'data' => $validator->errors()
                ], 422);  
            }

            //return response()->json($request->all());

            
            $lawyer->full_name = $request->full_name;
            $lawyer->email = $request->email;
            $lawyer->phone = $request->phone;
            $lawyer->gender = $request->gender;
            $lawyer->dob = $request->dob;
            $lawyer->license_number = $request->license_number;
            $lawyer->total_experience = $request->total_experience;
            $lawyer->practice_area = $request->practice_area;
            $lawyer->current_law_firm = $request->current_law_firm; 
            $lawyer->start_time = $request->start_time;
            $lawyer->academic_institute = $request->academic_institute;
            $lawyer->lawyerdegrees = $request->lawyerdegrees;
            $lawyer->lawyer_bio = $request->lawyer_bio;
            $lawyer->passing_year = $request->passing_year; 
            //$lawyer->refer_code = $request->refer_code;       
            //$lawyer->password = bcrypt($request->password); 
            //$lawyer->status = 'Active';
            $lawyer->update();


            //$lawyer = Lawyer::findorfail($request->lawyer_id);

            $count = Lawyer::count();
            $count+=1;

            if($request->file('nid_front_photo'))
            {   
                $file = $request->file('nid_front_photo');
                $name = time()."nid_front".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $nid_front_photo = 'uploads/lawyers/'.$name;
            }else{
                $nid_front_photo = $lawyer->lawyerdoc?$lawyer->lawyerdoc->nid_front_photo:null; 
            }


            if($request->file('nid_back_photo'))
            {   
                $file = $request->file('nid_back_photo');
                $name = time()."nid_back".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $nid_back_photo = 'uploads/lawyers/'.$name;
            }else{
                $nid_back_photo = $lawyer->lawyerdoc?$lawyer->lawyerdoc->nid_back_photo:null; 
            }


            if($request->file('license_certificate'))
            {   
                $file = $request->file('license_certificate');
                $name = time()."license_certificate".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $license_certificate = 'uploads/lawyers/'.$name;
            }else{
                $license_certificate = $lawyer->lawyerdoc?$lawyer->lawyerdoc->license_certificate:null; 
            }


            if($request->file('profile'))
            {   
                $file = $request->file('profile');
                $name = time()."profile".$count.$file->getClientOriginalName();
                $file->move(public_path().'/uploads/lawyers/', $name); 
                $profile = 'uploads/lawyers/'.$name;
            }else{
                $profile = $lawyer->lawyerdoc?$lawyer->lawyerdoc->profile:null; 
            }


            $paths = [];
            if ($request->hasFile('documents')) {
                

                foreach ($request->file('documents') as $key=>$image) {
                    $imageName = time() . $key+1 . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/documents'), $imageName);

                    $paths[] = 'uploads/documents/' . $imageName;
                }

                //return $paths; 
            }

            $doc = $lawyer->lawyerdoc;
            $doc->lawyer_id = $lawyer->id;
            $doc->license_certificate = $request->has('license_certificate')?$license_certificate:$lawyer->lawyerdoc->license_certificate;
            $doc->nid_front_photo = $request->has('nid_front_photo')?$nid_front_photo:$lawyer->lawyerdoc->nid_front_photo;
            $doc->nid_back_photo = $request->has('nid_back_photo')?$nid_back_photo:$lawyer->lawyerdoc->nid_back_photo;
            $doc->documents = $request->has('documents')?json_encode($paths):$lawyer->lawyerdoc->documents;
            $doc->profile = $request->has('profile')?$profile:$lawyer->lawyerdoc->profile;
            $doc->update();

            $getLawyer = Lawyer::with('lawyerdoc')->findorfail($lawyer->id);

            DB::commit();

            return response()->json(['status'=>true, 'lawyer_id'=>intval($lawyer->id), 'message'=>'Successfully updated', 'data'=>$getLawyer]);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function getlawyerDetails($id)
    {
        try
        {
            $lawyer = Lawyer::with('lawyerdoc','lawyeravailability','lawyerfee')->findorfail($id);
            return response()->json(['status'=>true, 'base_url'=>url('/'), 'data'=>$lawyer]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    // public function editLawyerSlot(Request $request)
    // {
    //     try
    //     {
    //         //
    //     }catch(Exception $e){
    //         return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    //     }
    // }

    public function editLawyerSlot(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'slot_id' => 'required|integer|exists:lawyeravailabilities,id',
                'morning_start_time' => 'required|string',
                'morning_end_time' => 'required|string',
                'morning_shift_days' => 'required|string',
                'afternoon_start_time' => 'required|string',
                'afternoon_end_time' => 'required|string',
                'afternoon_shift_days' => 'required|string',
                'evening_start_time' => 'required|string',
                'evening_end_time' => 'required|string',
                'evening_shift_days' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }


            $slot = Lawyeravailability::findorfail($request->slot_id);
            $slot->morning_start_time = $request->morning_start_time;
            $slot->morning_end_time = $request->morning_end_time;
            $slot->morning_shift_days = $request->morning_shift_days;
            $slot->afternoon_start_time = $request->afternoon_start_time;
            $slot->afternoon_end_time = $request->afternoon_end_time;
            $slot->afternoon_shift_days = $request->afternoon_shift_days;
            $slot->evening_start_time = $request->evening_start_time;
            $slot->evening_end_time = $request->evening_end_time;
            $slot->evening_shift_days = $request->evening_shift_days;
            $slot->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your time slot info has been updated']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerLists()
    {
        try
        {   
            $lawyers = Lawyer::with('lawyerdoc','lawyeravailability','lawyerfee')->where('status','Active')->paginate(15);
            return response()->json($lawyers);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function saveLawyerRating(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'lawyer_id' => 'required|integer|exists:users,id',
                'rating' => 'required|integer|max:5',
                'review' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $rating = new Lawyerreview();
            $rating->user_id = $request->user_id;
            $rating->lawyer_id = $request->lawyer_id;
            $rating->rating = $request->rating;
            $rating->review = $request->review;
            $rating->save();
            return response()->json(['status'=>true, 'message'=>'Successfully add rating', 'data'=>$rating]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerRatings(Request $request)
    {
        try
        {
            $query = Lawyerreview::query();
            if($request->has('user_id')){
                $query->where('user_id',$request->user_id);
            }
            if($request->has('lawyer_id')){
                $query->where('lawyer_id',$request->lawyer_id);
            }
            $ratings = $query->with('lawyer.lawyerdoc.lawyeravailability.lawyerfee')->latest()->paginate(15);
            return response()->json($ratings);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function myLawyerAppointments(Request $request)
    {
        try
        {   
            $date = date('Y-m-d');
            $data = Lawyerappointment::with('userinfo')->where('user_id',user()->id)->where('appointment_date','>=',$date)->orderBy('appointment_date','ASC')->paginate(15);
            return response()->json($data);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerStatusUpdate(Request $request)
    {
        try
        {   

            $validator = Validator::make($request->all(), [
                'activation_status' => 'required|in:Online,Offline',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $lawyer = user();
            $lawyer->activation_status = $request->activation_status;
            $lawyer->update();

            return response()->json(['status'=>true, "message"=>"You Successfully $request->activation_status"]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerAppointmentStatusChange(Request $request)
    {
        try
        {   

            $validator = Validator::make($request->all(), [
                'appointment_id' => 'required|integer|exists:lawyerappointments,id',
                'status' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $appointment = Lawyerappointment::findorfail($request->appointment_id);
            $appointment->status = $request->status;
            $appointment->update();

            return response()->json(['status'=>true, 'lawyer_id'=>intval($appointment->id), 'message'=>'Successfully updated']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function saveLawyerConsultation(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'appointment_id' => 'required|integer|exists:lawyerappointments,id',
                'description' => 'required',
                'title' => 'nullable',
                'files' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $paths = [];
            if ($request->hasFile('files')) {
                

                foreach ($request->file('files') as $key=>$image) {
                    $imageName = time() . $key+1 . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/consultations'), $imageName);

                    $paths[] = 'uploads/consultations/' . $imageName;
                }

                //return $paths; 
            }

            $consultation = new Lawyerconsultation();
            $consultation->lawyerappointment_id = $request->appointment_id;
            $consultation->title = $request->title;
            $consultation->description = $request->description;
            $consultation->files = json_encode($paths);
            $consultation->save();

            return response()->json(['status'=>true, 'consultation_id'=>intval($consultation->id), 'message'=>"Successfully a consultation added"]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function getConsultation($id)
    {
        try
        {
            $data = Lawyerconsultation::findorfail($id);
            return response()->json(['status'=>true, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function lawyerPasswordChange(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = user();

            $lawyer = Lawyer::findorfail($user->id);

            

            if (!Hash::check($request->current_password, $lawyer->password)) {
    

                return response()->json(['status'=>false, 'message'=>"The current password is not matched"],400);
            }

            $lawyer->password = Hash::make($request->new_password);
            $lawyer->update();

            return response()->json(['status'=>true, 'message'=>'Successfully your password has been changed']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
