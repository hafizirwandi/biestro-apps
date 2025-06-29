<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function index(Request $req)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $allFields = setting_field();

        // Tentukan mod aktif (default: 'setting')
        $activeMod = $req->mod ?? 'setting';

        // Filter field berdasarkan mod aktif
        $input = array_filter($allFields, function ($item) use ($activeMod) {
            return isset($item['mod']) && $item['mod'] === $activeMod;
        });

        // Ambil semua mod unik untuk tab navigasi
        $mods = collect($allFields)
            ->pluck('mod')
            ->unique()
            ->map(function ($mod) {
                return [
                    'mod' => $mod,
                    'label' => ucwords(str_replace('-', ' ', $mod)),
                ];
            });

        return view('setting.index', compact('input', 'settings', 'mods', 'activeMod'));
    }

    public function save(Request $req)
    {
        try {
            // dd($req->except(['_token']));
            foreach ($req->except(['_token']) as $key => $value) {

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            // Bersihkan cache config dan cache umum, lalu buat ulang cache config
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:cache');

            return back()->with('success', 'Setting content succesfull to save');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
