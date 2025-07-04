<?php

namespace App\Http\Controllers;

use App\Models\Wahana;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WahanaController extends Controller
{
    public function index()
    {
        $data = Wahana::all();
        return view('wahana.index', compact('data'));
    }
    public function create()
    {
        return view('wahana.create');
    }
    public function edit($id)
    {
        $q = Wahana::findOrFail($id);
        $data['data'] = $q;

        return view('wahana.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            $rules = [
                'name' => 'required',
                'description' => 'nullable',
                'key' => [
                    'required',
                    Rule::unique('wahanas', 'key')->ignore($id),
                ],
            ];
            if ($id != null) {


                $wahana = Wahana::findOrFail($id);

                $data = $request->validate($rules);


                $wahana->update($data);

                $msg = 'Wahana updated successfully';
            } else {

                $data = $request->validate($rules);
                $wahana = Wahana::create($data);
                $msg = 'Wahana saved successfully';
            }
            return back()->with('success', $msg);
        } catch (ValidationException $e) {
            return back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request)
    {
        try {
            Wahana::destroy($request->input('id'));
            return back()->with('success', 'Wahana deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
    public function getData()
    {
        $data = Wahana::all();
        return response()->json([
            'success' => true,
            'message' => 'Data wahana berhasil diambil.',
            'data' => $data
        ], 200);
    }
}
