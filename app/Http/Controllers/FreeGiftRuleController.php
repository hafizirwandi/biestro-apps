<?php

namespace App\Http\Controllers;

use App\Models\FreeGiftRule;
use Illuminate\Http\Request;
use App\Models\FreeGiftRuleWahana;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FreeGiftRuleController extends Controller
{

    public function index()
    {
        $data = FreeGiftRule::all();
        return view('free-gift.index', compact('data'));
    }
    public function create()
    {
        return view('free-gift.create');
    }
    public function edit($id)
    {
        $q = FreeGiftRule::findOrFail($id);
        $data['selectedWahanas'] = $q->wahanas->map(function ($item) {
            return [
                'wahana_id' => $item->id,
                'qty' => $item->pivot->qty,
            ];
        });


        $data['data'] = $q;

        return view('free-gift.edit', $data);
    }
    public function saveOrUpdate(Request $request, $id = null)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'name' => 'required',
                'description' => 'nullable',
                'min_purchase' => 'required|numeric',
                'is_active' => 'required|in:0,1',
                'is_multiple' => 'required|in:0,1',
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
                $FreeGiftRule = FreeGiftRule::findOrFail($id);
                $FreeGiftRule->update($dataTicket);

                // Hapus wahana sebelumnya
                FreeGiftRuleWahana::where('free_gift_rule_id', $FreeGiftRule->id)->delete();

                $msg = 'Free Gift Rule updated successfully';
            } else {
                // Create
                $FreeGiftRule = FreeGiftRule::create($dataTicket);
                $msg = 'Free Gift Rule saved successfully';
            }

            // Simpan wahana baru
            for ($i = 0; $i < count($data['wahana_id']); $i++) {
                $dataTicketWahana = [
                    'free_gift_rule_id' => $FreeGiftRule->id,
                    'wahana_id' => $data['wahana_id'][$i],
                    'qty' => $data['qty'][$i],
                ];
                FreeGiftRuleWahana::create($dataTicketWahana); // ← ini yg benar
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
            FreeGiftRule::destroy($request->input('id'));
            return back()->with('success', 'Free Gift Rule deleted successfully');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
