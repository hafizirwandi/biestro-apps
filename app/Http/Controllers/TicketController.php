<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Wahana;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function index()
    {
        $data = Ticket::all();
        return view('ticket.index', compact('data'));
    }
    public function create()
    {
        $wahana = Wahana::all();
        return view('ticket.create', compact('wahana'));
    }
    public function edit($id)
    {
        $data['wahana'] = Wahana::all();
        $q = Ticket::findOrFail($id);
        $data['data'] = $q;

        return view('ticket.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        // dd($request->input());
        try {
            $rules = [
                'wahana_id' => 'required|exists:wahanas,id',
                'name' => 'required|string',
                'price' => 'required|numeric',
                'is_active' => 'required|in:1,0',
            ];
            if ($id != null) {
                $ticket = Ticket::findOrFail($id);
                $data = $request->validate($rules);
                $ticket->update($data);

                $msg = 'Ticket updated successfully';
            } else {

                $data = $request->validate($rules);
                $ticket = Ticket::create($data);
                $msg = 'Ticket saved successfully';
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
            Ticket::destroy($request->input('id'));
            return back()->with('success', 'Ticket deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
