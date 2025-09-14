<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;


//user signup/signin
Route::post('user-signup', [ApiController::class, 'userSignup']);
Route::post('user-signin', [ApiController::class, 'userSignin']);
Route::middleware('auth:sanctum')->group( function () { 
  Route::post('user-signout', [ApiController::class, 'userSignOut']);
});

//locations

Route::get('/districts', [ApiController::class, 'districts']);
Route::get('/upazilas', [ApiController::class, 'upazilas']);
Route::get('/unions', [ApiController::class, 'unions']);

//otps
Route::post('send-otp', [ApiController::class, 'sendOTP']);
Route::post('verify-otp', [ApiController::class, 'verifyOTP']);

//doctors auth
Route::post('doctor-signup', [ApiController::class, 'doctorSignup']);

Route::post('service-provider-signin', [ApiController::class, 'serviceProviderSignin']);

Route::post('add-doctor-doc', [ApiController::class, 'addDoctorDoc']);
Route::post('check-doctor-doc', [ApiController::class, 'checkDoctorDoc']);
Route::get('/doctor-infos', [ApiController::class, 'doctorInfos']);

//auth middleware group
Route::middleware('auth:sanctum')->group( function () { 
  Route::post('user-signout', [ApiController::class, 'userSignOut']);
  Route::post('provider-signout', [ApiController::class, 'providerSignout']);
  Route::post('doctor-signout', [ApiController::class, 'doctorSignout']);
  //details api's
  Route::get('doctor-details', [ApiController::class, 'doctorDetails']);
  Route::get('/doctor-lists', [ApiController::class, 'doctorLists']);
  
  //edit api's
  Route::post('edit-doctor-account', [ApiController::class, 'editDoctorAccount']);
  Route::post('edit-doctor-education', [ApiController::class, 'editDoctorEducation']);
  Route::post('edit-doctor-experience', [ApiController::class, 'editDoctorExperience']);
  Route::post('edit-doctor-slot', [ApiController::class, 'editDoctorSlot']);
  
  Route::get('/delete-doctor-education/{id}', [ApiController::class, 'deleteDoctorEducation']); 
  Route::get('/delete-doctor-experience/{id}', [ApiController::class, 'deleteDoctorExperience']);
  Route::post('add-doctor-degree', [ApiController::class, 'addDoctorDegree']);
  Route::post('add-doctor-experience', [ApiController::class, 'addDoctorExperience']);
  
  Route::post('change-password', [ApiController::class, 'changePassword']);
  
  Route::get('/delete-doctor-account', [ApiController::class, 'deleteDoctorAccount']);
  
  Route::post('update-doctor-about', [ApiController::class, 'updateDoctorType']);
  
  
  //doctor appointment
  Route::post('doctor-appointment', [ApiController::class, 'doctorAppointment']);

  Route::get('/appointment-lists', [ApiController::class, 'appointmentLists']);
  
  
  //doctor reviews

  Route::post('save-doctor-review', [ApiController::class, 'saveDoctorReview']);

  Route::get('/doctor-ratings', [ApiController::class, 'doctorRatings']);
  

  Route::get('/my-appointment-lists', [ApiController::class, 'myAppointmentLists']);

  Route::post('save-prescription', [ApiController::class, 'savePrescription']);

  Route::get('/my-prescriptions', [ApiController::class, 'myPrescriptions']);

  Route::get('/prescription-details/{id}', [ApiController::class, 'prescriptionDetails']);
  
});



Route::post('doctor-status-update', [ApiController::class, 'doctorStatusUpdate']);

Route::get('/get-doctor-details/{id}', [ApiController::class, 'getDoctorDetails']);

Route::post('search-doctor', [ApiController::class, 'searchDoctor']);

//news
Route::get('/news', [ApiController::class, 'news']);