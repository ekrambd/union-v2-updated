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

class ApiController extends Controller
{
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

            $profile = DB::table('profile')->where('email',$user->email)->first();

            //return $profile;

            $user_type = $profile?$profile->type:"0";

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
            
            // if (substr($number, 0, 2) === "88") {
            //     $number = substr($number, 2);
            // }
            
            if(!$user){
                return response()->json(['status'=>false, 'message'=>'Invalid User'],404);
            }
            
            if($user->send_otp == 1)
            {
                return response()->json(['status'=>false, 'message'=>'Sorry the number already has been taken'],400);
            }
            
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

                $user->send_otp = 1;
                $user->update();

                DB::commit();

                return response()->json(['status'=>true, 'message'=>'Verification OTP has been sent'],200);
            }

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

            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

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
        try
        {
            $data = Doctor::with('doctoravailability','doctordegrees','doctorexperiences','doctordoc','doctorfee')->latest()->paginate(15);
            return response()->json($data);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
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

            $pCount = Doctorappointment::where('appointment_date',$request->appointment_date)->where('shift',$request->shift)->count();

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

    public function appointmentLists(Request $request)
    {
        try
        {   
            $date = date('Y-m-d');
            $data = Doctorappointment::with('patientinfo')->where('doctor_id',user()->id)->where('appointment_date','>=',$date)->orderBy('appointment_date','ASC')->paginate(15);
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
            $data = Doctorappointment::with('doctor.doctordoc','patientinfo')->where('user_id',user()->id)->where('status','booked')->get();
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
                'reg_series'      => 'required|string',
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
            $rider->reg_series = $request->reg_series;
            $rider->reg_no = $request->reg_no;
            $rider->refer_code = $request->refer_code;
            $rider->reffaral_code = $requests->phone;
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
}
