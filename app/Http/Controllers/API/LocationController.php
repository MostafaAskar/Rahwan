<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // "user_id" => "required",
            "latitude" => "nullable|numeric", // Allow pet_id to be null
            "longitude" => "nullable|numeric", // Allow supply_id to be null

        ]);

        if ($validator->fails()) {
            return response($validator->errors()->all(), 422);
        }
        $request['user_id'] = auth()->id();



        $location = Location::create($request->all());




        return response()->json(['message' => $location])->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $location = Location::where('user_id' , $id)->first();
        
        if($location == null){
            $message = __('messages.location_not_found');
            return comman_message_response($message,400);  
        }
        
        return new LocationResource($location);
        // return response()->json(['message' => $location])->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }
}
