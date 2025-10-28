<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CourierriderController;


Route::get('login', [ApiController::class, 'login'])->name('login');
//user signup/signin
Route::post('user-signup', [ApiController::class, 'userSignup']);
Route::post('user-signin', [ApiController::class, 'userSignin']);

//courer agent signin
Route::post('courier-agent-signin', [ApiController::class, 'courierAgentSignin']);

//rider signup
Route::post('rider-signup', [ApiController::class, 'riderSignup']);
Route::get('/rider-zones', [ApiController::class, 'riderZones']);
Route::get('/reg-series', [ApiController::class, 'regSeries']);
Route::post('rider-doc-upload', [ApiController::class, 'riderDocUpload']);



Route::middleware('auth:sanctum')->group( function () { 
  Route::post('user-signout', [ApiController::class, 'userSignOut']);
});

//locations

Route::get('/divisions', [ApiController::class, 'divisions']);
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

Route::post('search-user', [ApiController::class, 'searchUser']);

//lawyers

Route::post('lawyer-signup', [ApiController::class, 'lawyerSignup']);
Route::post('lawyer-doc-upload', [ApiController::class, 'lawyerDocUpload']);
Route::get('/lawyer-degrees', [ApiController::class, 'lawyerDegress']);

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

  //lawyer appointment

  Route::post('save-lawyer-appointment', [ApiController::class, 'saveLawyerAppointment']);
  Route::get('/lawyer-appointment-lists', [ApiController::class, 'lawyerAppointmentLists']); 
  
  //doctor reviews

  Route::post('save-doctor-review', [ApiController::class, 'saveDoctorReview']);

  Route::get('/doctor-ratings', [ApiController::class, 'doctorRatings']);
  

  Route::get('/my-appointment-lists', [ApiController::class, 'myAppointmentLists']);

  Route::post('save-prescription', [ApiController::class, 'savePrescription']);

  Route::get('/my-prescriptions', [ApiController::class, 'myPrescriptions']);

  Route::get('/prescription-details/{id}', [ApiController::class, 'prescriptionDetails']);

  //users

  Route::get('/user-details', [ApiController::class, 'userDetails']);

  Route::get('/user-delete-account', [ApiController::class, 'userDeleteAccount']);

  //notifications
  Route::get('/app-notifications', [ApiController::class, 'appNotifications']);
  Route::post('user-profile-update', [ApiController::class, 'userProfileUpdate']);
  Route::post('lawyer-profile-update', [ApiController::class, 'lawyerProfileUpdate']);
  
  //Route::post('/edit-lawyer-education', [ApiController::class, 'editLawyerEducation']);
  Route::post('edit-lawyer-slot', [ApiController::class, 'editLawyerSlot']);  
  Route::get('/lawyer-lists', [ApiController::class, 'lawyerLists']);
  Route::post('save-lawyer-rating', [ApiController::class, 'saveLawyerRating']);
  Route::get('/lawyer-ratings', [ApiController::class, 'lawyerRatings']);
  Route::get('/lawyer-details/{id}', [ApiController::class, 'lawyerDetails']);

  //5 api's

  Route::get('/my-lawyer-appointments', [ApiController::class, 'myLawyerAppointments']); 

  Route::post('lawyer-status-update', [ApiController::class, 'lawyerStatusUpdate']);

  Route::post('lawyer-appointment-status-change', [ApiController::class, 'lawyerAppointmentStatusChange']);

  Route::post('save-lawyer-consulation', [ApiController::class, 'saveLawyerConsultation']);

  Route::get('/get-consultation/{id}', [ApiController::class, 'getConsultation']);

  Route::post('lawyer-password-change', [ApiController::class, 'lawyerPasswordChange']);

  //courirer api's

  Route::post('courier-price-cal', [ApiController::class, 'courierPriceCal']);

  Route::post('save-courier-order', [ApiController::class, 'saveCourier']);

  Route::get('/delete-courier-order/{id}', [ApiController::class, 'deleteCourierOrder']);

  Route::post('doctor-status-acitve', [ApiController::class, 'doctorStatusActive']);

}); 

//'from.browser',
Route::middleware(['auth:courieragent','from.browser','custom.cors'])->group(function () {
    Route::apiResource('courierriders',CourierriderController::class);
    Route::post('courier-rider-signout', [ApiController::class, 'courierRiderSignout']);
    Route::post('courier-order-status-update', [ApiController::class, 'courierOrderStatusUpdate']);
    Route::post('set-order-rider', [ApiController::class, 'setOrderRider']);
    Route::get('/delete-courier-order-agent/{id}', [ApiController::class, 'deleteCourierOrder']);
    Route::post('set-order-agent', [ApiController::class, 'setOrderAgent']);
    Route::get('/get-courier-agents', [ApiController::class, 'getCourierAgents']);
    Route::post('agent-change-password', [ApiController::class, 'agentChangePassword']);
});

Route::get('/get-courier-agent-details/{id}', [ApiController::class, 'getCourierAgentDetails']);



Route::post('doctor-status-update', [ApiController::class, 'doctorStatusUpdate']);

Route::get('/get-doctor-details/{id}', [ApiController::class, 'getDoctorDetails']);

Route::post('search-doctor', [ApiController::class, 'searchDoctor']);

//news
Route::get('/news', [ApiController::class, 'news']);

Route::get('/get-lawyer-details/{id}', [ApiController::class, 'getlawyerDetails']);



Route::post('courier-order-lists', [ApiController::class, 'courierOrderLists']);

Route::get('/get-courier-order-lists', [ApiController::class, 'getCourierOrderLists']);

Route::post('save-agent', [ApiController::class, 'saveAgent']);

Route::get('/courier-order-details/{id}', [ApiController::class, 'courierOrderDetails']);