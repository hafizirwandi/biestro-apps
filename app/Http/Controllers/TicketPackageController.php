<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketPackage;
use App\Models\TicketPackageWahana;
use App\Models\Wahana;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketPackageController extends Controller
{
    public function index()
    {
        $data = TicketPackage::all();
        return view('ticket-package.index', compact('data'));
    }
    public function create()
    {
        return view('ticket-package.create');
    }
    public function edit($id)
    {
        $q = TicketPackage::findOrFail($id);
        $data['selectedWahanas'] = $q->wahanas->map(function ($item) {
            return [
                'wahana_id' => $item->id,
                'qty' => $item->pivot->qty,
            ];
        });


        $data['data'] = $q;

        return view('ticket-package.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'name' => 'required',
                'description' => 'nullable',
                'price' => 'required|numeric',
                'is_active' => 'required|in:0,1',
                'wahana_id' => 'required|array',
                'wahana_id.*' => 'required|exists:wahanas,id',
                'qty' => 'required|array',
                'qty.*' => 'required|numeric|min:1',
            ];

            // Validasi
            $data = $request->validate($rules);

            // Buat data tanpa wahana_id dan qty
            $dataTicket = collect($data)->except(['wahana_id', 'qty'])->toArray();

            if ($id !== null) {
                // Update
                $ticketpackage = TicketPackage::findOrFail($id);
                $ticketpackage->update($dataTicket);

                // Hapus wahana sebelumnya
                TicketPackageWahana::where('ticket_package_id', $ticketpackage->id)->delete();

                $msg = 'Ticket Package updated successfully';
            } else {
                // Create
                $ticketpackage = TicketPackage::create($dataTicket);
                $msg = 'Ticket Package saved successfully';
            }

            // Simpan wahana baru
            for ($i = 0; $i < count($data['wahana_id']); $i++) {
                $dataTicketWahana = [
                    'ticket_package_id' => $ticketpackage->id,
                    'wahana_id' => $data['wahana_id'][$i],
                    'qty' => $data['qty'][$i],
                ];
                TicketPackageWahana::create($dataTicketWahana); // ← ini yg benar
            }

            DB::commit();
            return back()->with('success', $msg);
        } catch (ValidationException $e) {
            DB::rollback();
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function destroy(Request $request)
    {
        try {
            TicketPackage::destroy($request->input('id'));
            return back()->with('success', 'Ticket Package deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
