<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();
        
        foreach($roles as $role){
            $users = User::whereHas('roles', function($q) use($role){
                $q->where('name', $role->name);
            }
            )->count();
            $role->role_count = $users;
        }
        $page_title = 'Roles';
        $page_description = 'Role List';
        $action = 'table_landownerships';
        return view('admin.roles.index', compact('roles','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $permissions = Permission::get();
      $action = 'form_pickers';
      $page_title = 'Create roles';
      return view('admin.roles.create',compact('permissions','action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);
        $role = Role::create(['name' => $request->input('name')]);
        if($request->has('permission') && !empty($request->permission)){
            $role->syncPermissions($request->input('permission'));
        }
        return redirect()->route('admin.roles.index')
                        ->with('success','Role created successfully');
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
        $role = Role::find($id);
        $rolePermissions = $role->permissions()
                                    ->pluck('id')
                                    ->toArray();
        $permissions = Permission::select('id','name')->orderBy('created_at','asc')->get(); 
        $action = 'form_pickers';
        $page_title = 'Edit roles';
        return view('admin.roles.edit',compact('action','page_title','role','rolePermissions','permissions'));
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
        $this->validate($request, [
            'name' => 'required',
        ]);
        $role = Role::find($id);
        $update = $role->update([
            'name' => $request->name
        ]);
        // Sync role permissions
        $role->syncPermissions($request->input('permissions'));
        return redirect()->route('admin.roles.index')
                        ->with('success','Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $role   = Role::find($request->id);
            if(!$role){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            $delete = $role->delete();
            $perm   = $role->permissions()->delete();
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }
}
