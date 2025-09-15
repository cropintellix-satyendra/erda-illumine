<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Callerlist;
use Illuminate\Http\Request;
// $request = file_get_contents('php://input');

class CallerlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $caller = Callerlist::where('phone_number' , $request->phone_number)->first();
        if(!$caller)
        {
            return response()->json(['error'=>true,'message'=>'Not Verified'],422);
        }
        return response()->json(['success'=>true,'message'=>'Verified'],200);
       
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
        $request1 = file_get_contents('php://input');
        $requestArr = json_decode($request1, true);
        $mobile=$requestArr['phone_number'];
        $caller = new Callerlist;
        $caller->phone_number = $requestArr['phone_number'];
        $caller->called_number = $requestArr['called_number'];
        $caller->phoneno_zone = $requestArr['phoneno_zone'];
        $caller->operator  =$requestArr['operator'];
        $caller->call_time  = $requestArr['call_time'];
        $caller->save();
        if(!$caller)
        {
            return response()->json(['error'=>true,'message'=>'Somethings went wrong'],422);
        }
        return response()->json(['success'=>true,'message'=>'Successfully submitted' , 'caller' => $caller ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
