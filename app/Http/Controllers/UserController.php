<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Store;
use App\Models\Wahana;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $data['data'] = User::with(['roles'])->get();

        return view('user.index', $data);
    }

    public function create()
    {
        $data['role'] = Role::all();
        $data['wahanas'] = Wahana::all();
        return view('user.create', $data);
    }
    public function edit($id)
    {
        $q = User::with(['roles', 'wahanas'])->findOrFail($id);
        $data['role'] = Role::all();
        $data['wahanas'] = Wahana::all();
        $data['data'] = $q;

        return view('user.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            $rules = [
                'name' => 'required',
                'username' => 'required',
                'password' => 'required',
                'email' => 'nullable|email',
                'status' => 'required|in:0,1,2',
                'spv_pin' => 'nullable|string|min:4|max:6',
            ];
            if ($id != null) {
                $rules['username'] = ['required', Rule::unique('users')->ignore($id)];

                $user = User::with('roles')->findOrFail($id);
                $data = $request->validate($rules);
                if ($request->input('password2')) {
                    $data['password'] = Hash::make($request->input('password2'));
                }

                $user->where('id', $id)->update($data);
                //role
                $old_role = $user->roles[0]->name ?? null;
                if ($old_role != $request->input('role')) {
                    $user->assignRole($request->input('role'));
                    if ($old_role != null) {
                        $user->removeRole($old_role);
                    }
                }

                $user->assignRole($request->input('role'));
                $msg = 'User berhasil diperbaharui';
            } else {
                $rules['username'] = 'required|unique:users';
                $data = $request->validate($rules);
                $data['password'] = Hash::make($request->input('password'));
                $user = User::create($data);
                //role
                $user->assignRole($request->input('role'));
                $msg = 'User berhasil dibuat';
            }

            $user->wahanas()->sync($request->input('wahana_ids', []));

            if ($request->boolean('scan_unflag_authorized')) {
                $user->givePermissionTo('scan-unflag');
            } else {
                $user->revokePermissionTo('scan-unflag');
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return $e->getMessage();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            User::destroy($request->input('id'));
            return back()->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
