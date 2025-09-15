<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if(auth()->user()->cannot('company')) abort(403, 'User does not have the right roles.');
      // $users = User::with('company','state')->whereHas('roles', function($q){
      //   $q->where('name', 'Company'); //fetch user from users table hasrole SuperAdmin
      //   })->get();
      //   // dd($users);
       $users = Company::all();
       $page_title = 'Companys';
       $page_description = 'Some description for the page';
       $action = 'table_landownerships';
       return view('admin.company.index', compact('users','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      if(auth()->user()->cannot('add company')) abort(403, 'User does not have the right roles.');
      $action = 'form_pickers';
      $page_title = 'Create companys';
      $page_description = 'Create companys';
      $States=State::get();
      $Districts=District::get();
      $roles=Role::where('name','Company')->get();
      return view('admin.company.create',compact('action','page_title','States','Districts','roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // dd($request->all());
      if(auth()->user()->cannot('add company')) abort(403, 'User does not have the right roles.');
      $user= new User;
      $user->name = $request->name;
      $user->email = $request->email;
      $user->mobile = $request->mobile;
      $user->password = bcrypt($request->password);
      $user->role = 'Company';
      $user->company_code = $request->company_code;
      $user->state_id = $request->state_id;
      $user->status = $request->status;
      $user->save();
      $user->syncRoles($request->roles);
      if(!$user){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      $company = new Company;
      $company->company = $request->company;
      $company->state_id = $request->state_id;
      if(is_array($request->district)){
        $company->district_id = implode(',', $request->district);
      }

      $company->company_code = $user->company_code;
      $company->status = $request->status;
      $company->user_id = $user->id; // Fixed typo on this line
      $company->save();

      if(!$company){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      return redirect()->route('admin.company.index')->with('success', 'Saved Successfully');
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
        if(auth()->user()->cannot('edit company')) abort(403, 'User does not have the right roles.');

        $user = User::find($id);
        $roles=Role::where('name','Company')->get();
        // Check if user is null
        if(!$user) {
            abort(404, 'User not found.');
        }

        $company = Company::where('user_id', $user->id)->first();
        $companyname = $company->company;
        // Check if company is null
        if(!$company) {
            abort(404, 'Company not found.');
        }

        $action = 'form_pickers';
        $page_title = 'Edit companies';
        $page_description = 'Edit companies';
        $States = State::get();
        $Districts = District::get();
        $array_dist = json_encode(explode(',', $company->district_id));

        return view('admin.company.edit', compact('company', 'user', 'action', 'States', 'Districts', 'array_dist','roles','companyname'));
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
      if(auth()->user()->cannot('edit company')) abort(403, 'User does not have the right roles.');

      $user= User::find($id);
      $user->name = $request->name;
      $user->email = $request->email;
      $user->mobile = $request->mobile;
      $user->password = bcrypt($request->password);
      $user->role = 'Company';
      $user->company_code = $request->company_code;
      $user->state_id = $request->state_id;
      $user->status = $request->status;
      $user->save();
      if(!$user){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      $company = Company::where('user_id',$user->id)->first();
      $company->company = $request->company??" ";
      $company->state_id = $request->state_id;
      $company->termcond = $request->termcond;      
      $company->company_code = $request->company_code;
      $company->district_id = implode(',',$request->district);
      $company->status = $request->status;
      $company->save();
      if(!$company){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      return redirect()->route('admin.company.index')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(auth()->user()->cannot('delete company')) abort(403, 'User does not have the right roles.');
      try {
            $company = User::destroy($id);
            if(!$company){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Get list of organization from state id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_organization(Request $request)
    {
      $user = User::find($request->user_id);
      $company = Company::where('company_code',$user->company_code)->get();
      return response()->json(['success'=>true,'list'=>$company],200);

    }

    public function get_organizatios_state(Request $request)
    {
      // $user = User::find($request->user_id);
    $company = Company::where('company_code', $request->company_code)->first(); 

    if ($company) {
        $stateIds = is_array($company->state_id) ? $company->state_id : [$company->state_id];
        $states = State::whereIn('id', $stateIds)->select('id','name',)->get();
        
        return response()->json(['success' => true, 'states' => $states], 200);
    } else {
        return response()->json(['success' => false, 'message' => 'Company not found'], 404);
    }

    }
    


}
