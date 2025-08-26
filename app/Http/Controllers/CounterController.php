<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CounterController extends Controller
{
    public function index()
    {
        $data = Counter::all();
        return view('counter.index', compact('data'));
    }
    public function create()
    {
        return view('counter.create');
    }
    public function edit($id)
    {
        $q = Counter::findOrFail($id);
        $data['data'] = $q;

        return view('counter.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        try {
            $rules = [
                'name' => 'required',
                'description' => 'nullable',
                'location' => 'required',
                'is_active' => 'required|in:1,0',

            ];
            if ($id != null) {


                $counter = Counter::findOrFail($id);

                $data = $request->validate($rules);


                $counter->update($data);

                $msg = 'Counter updated successfully';
            } else {

                $data = $request->validate($rules);
                $counter = Counter::create($data);
                $msg = 'Counter saved successfully';
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
            Counter::destroy($request->input('id'));
            return back()->with('success', 'Counter deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
    public function getData()
    {
        $data = Counter::all();
        return response()->json([
            'success' => true,
            'message' => 'Data counter berhasil diambil.',
            'data' => $data
        ], 200);
    }
}
