<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::orderBy('created_at','asc')->get();
        $page_title = 'Permissions';
        $page_description = 'Permission List';
        $action = 'table_landownerships';
        return view('admin.permission.index', compact('permissions','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $roles = Role::all();
      $action = 'form_pickers';
      $page_title = 'Create roles';
      return view('admin.permission.create',compact('roles','action','page_title'));
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
            'permission' => 'required|unique:permissions,name',
            'role' => 'required',
        ]);
        $permission = Permission::create(['name' => $request->permission]);
        if($request->has('role')){
           $permission->syncRoles($request->role); 
        }
        return redirect()->route('admin.permissions.index')
                        ->with('success','Permission created successfully');
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
        $roles = Role::all();
        $permissions = Permission::find($id); 
        $rolePermissions = $permissions->roles()
                                    ->pluck('id')
                                    ->toArray();
        $action = 'form_pickers';
        $page_title = 'Edit roles';
        return view('admin.permission.edit',compact('action','page_title','roles','rolePermissions','permissions'));
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
            'permission' => 'required',
        ]);
        // update permission table
        $permission = Permission::find($id);
        $permission->name = $request->permission;
        $permission->save();
        return redirect()->route('admin.permissions.index')
                        ->with('success','Permission updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        try {
            $permission   = Permission::find($request->id);
            if(!$permission){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            $delete = $permission->delete();
            $perm   = $permission->roles()->delete();
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
