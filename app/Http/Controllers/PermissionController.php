<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $data['data'] = Permission::with('role')->get();
        //   dd($data);
        return view('permission.index', $data);
    }

    public function create()
    {
        return view('permission.create');
    }
    public function edit($id)
    {
        $data['data'] = Permission::findOrFail($id);

        return view('permission.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            $rules = [
                'name' => 'required',
            ];
            if ($id != null) {
                $permission = Permission::findOrFail($id);
                $data = $request->validate($rules);

                $permission->where('id', $id)->update($data);
                $msg = 'Permission berhasil diperbaharui';
            } else {
                $data = $request->validate($rules);

                Permission::create($data);
                $msg = 'Permission berhasil dibuat';
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request)
    {
        try {
            Permission::destroy($request->input('id'));
            return back()->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
