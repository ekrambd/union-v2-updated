<?php

namespace App\Http\Controllers;

use App\Models\Courierrider;
use Illuminate\Http\Request;
use Validator;

class CourierriderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'rider_name' => 'required|string|max:50',
                'rider_email' => 'nullable|email|unique:courierriders',
                'rider_phone' => 'required|string|unique:courierriders',
                'area_address' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = Auth::guard('courieragent')->user();

            $rider = new Courierrider();
            $rider->courieragent = $user->id; 
            $rider->rider_name = $request->rider_name;
            $rider->rider_phone = $request->rider_phone;
            $rider->rider_email = $request->rider_email;
            $rider->area_address = $request->area_address;
            $rider->password = bcrypt('123456');
            $rider->save();
            return response()->json(['status'=>true, 'rider_id'=>intval($rider->id), 'message'=>'Successfully a rider has been added']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Courierrider  $courierrider
     * @return \Illuminate\Http\Response
     */
    public function show(Courierrider $courierrider)
    {
        return response()->json(['status'=>true, 'rider'=>$courierrider]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Courierrider  $courierrider
     * @return \Illuminate\Http\Response
     */
    public function edit(Courierrider $courierrider)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Courierrider  $courierrider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Courierrider $courierrider)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rider_name' => 'required|string|max:50',
                'rider_email' => 'nullable|email|unique:courierriders,rider_email,' . $courierrider->id,
                'rider_phone' => 'required|string|unique:courierriders,rider_phone,' . $courierrider->id,
                'area_address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all required fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            // Update the existing rider
            $courierrider->rider_name = $request->rider_name;
            $courierrider->rider_phone = $request->rider_phone;
            $courierrider->rider_email = $request->rider_email;
            $courierrider->area_address = $request->area_address;

            // Optional: reset password if provided
            if ($request->has('password') && !empty($request->password)) {
                $courierrider->password = bcrypt($request->password);
            }

            $courierrider->update();

            return response()->json([
                'status' => true,
                'rider_id' => intval($courierrider->id),
                'message' => 'Rider updated successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false, 
                'code' => $e->getCode(), 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Courierrider  $courierrider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Courierrider $courierrider)
    {
        try
        {
            $courierrider->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the rider has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
