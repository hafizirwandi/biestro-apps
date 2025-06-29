<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Role as SpatieRole;



class RoleController extends Controller
{
    public function index()
    {
        $data['data'] = Role::all();
        return view('role.index', $data);
    }

    public function create()
    {
        return view('role.create');
    }
    public function edit($id)
    {
        $data['data'] = Role::findOrFail($id);
        return view('role.edit', $data);
    }
    public function detail($id)
    {
        $data['role'] = Role::findOrFail($id);
        $data['data'] = (new Role)->getPermissionWithChecked($id);
        return view('role.detail', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            if ($id != null) {
                $role = Role::findOrFail($id);
                $data = $request->except(['_token', '_method', 'role']);

                $role->where('id', $id)->update($data);
                $msg = 'Role berhasil diperbaharui';
            } else {
                $data = $request->all();
                Role::create($data);
                $msg = 'Role berhasil dibuat';
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request)
    {
        try {
            Role::destroy($request->input('id'));
            return back()->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
    public function savePermission(Request $request)
    {


        try {
            $role = SpatieRole::find($request->input('role_id'));
            if ($request->input('is_add') == 'true') {
                $role->givePermissionTo($request->input('permission_name'));
            } else {
                $role->revokePermissionTo($request->input('permission_name'));
            }
            $json = array(
                'status' => 'success',
                'msg' => 'Permission berhasil disimpan',
            );
        } catch (\Exception $e) {
            $json = array(
                'status' => 'error',
                'msg' => $e->getMessage(),
            );
        }
        return json_encode($json);
    }
}
