<?php

use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!function_exists('statusUser')) {
    function statusUser($status)
    {
        switch ($status) {
            case 1:
                return '<span class="badge bg-label-success" text-capitalized="">Active</span>';

            case 0:
                return '<span class="badge bg-label-warning" text-capitalized="">Pending</span>';

            case 2:
                return '<span class="badge bg-label-secondary" text-capitalized="">Inactive</span>';

            default:
                return '';
        }
    }
}
if (!function_exists('statusIsRandomSoal')) {
    function statusIsRandomSoal($status)
    {
        switch ($status) {
            case 1:
                return '<span class="badge bg-label-success" text-capitalized="">Active</span>';

            case 0:
                return '<span class="badge bg-label-secondary" text-capitalized="">Pending</span>';



            default:
                return '';
        }
    }
}
if (!function_exists('statusGeneral')) {
    function statusGeneral($status)
    {
        switch ($status) {
            case 1:
                return '<span class="badge bg-label-success" text-capitalized="">Active</span>';

            case 0:
                return '<span class="badge bg-label-secondary" text-capitalized="">Pending</span>';



            default:
                return '';
        }
    }
}
if (!function_exists('uploadAndReadExcel')) {
    function uploadAndReadExcel($file)
    {
        // Mendapatkan ekstensi file
        $extension = $file->getClientOriginalExtension();

        // Memeriksa apakah file adalah file Excel
        if ($extension != 'xls' && $extension != 'xlsx') {
            return ['error' => 'File harus berformat Excel (xls, xlsx)'];
        }

        // Membaca file Excel baris per baris
        $rows = [];
        $reader = IOFactory::createReaderForFile($file->getPathname());
        $spreadsheet = $reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Jika ada sel yang kosong, jangan diperhitungkan
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $rows[] = $rowData;
        }

        return $rows;
    }
}

if (!function_exists('getTokenApi')) {
    function getTokenApi()
    {

        if (!session('api_token')) {
            $response = Http::post(env('URL_API') . 'login', [
                'username' => 'admin',
                'password' => 'admin',
            ]);
            if ($response->successful()) {
                $token = $response->json()['data']['token'];
                session(['api_token' => $token]);
                return $token;
            } else {

                return null;
            }
        } else {
            return session('api_token');
        }
    }
}

if (!function_exists('encryptText')) {
    function encryptText($plainText, $key)
    {
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encryptedText = openssl_encrypt($plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encryptedText);
    }
}

if (!function_exists('decryptText')) {
    function decryptText($encryptedText, $key)
    {
        $encryptedText = base64_decode($encryptedText);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($encryptedText, 0, $ivLength);
        $encryptedText = substr($encryptedText, $ivLength);
        return openssl_decrypt($encryptedText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
if (!function_exists('getFormFieldTypes')) {
    function getFormFieldTypes()
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'textarea' => 'Textarea',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio',
            'dropdown' => 'Dropdown',
            'rate' => 'Rate',
            'date' => 'Date',
            'month' => 'Month',
            'time' => 'Time',
            'datetime-local' => 'Datetime Local',
        ];
    }
}

if (!function_exists('sanitizeErrorMessage')) {
    function sanitizeErrorMessage($message)
    {
        // Hilangkan kata 'answere'
        $message = str_replace('answere', '', $message);

        // Hilangkan semua angka
        $message = preg_replace('/\d+/', '', $message);

        // Hilangkan underscore jika ada sisa
        $message = str_replace('_', '', $message);

        // Hilangkan spasi dobel akibat penghapusan
        $message = preg_replace('/\s+/', ' ', $message);

        // Rapikan spasi di awal/akhir
        return trim($message);
    }
}


function setting_field()
{
    return [
        [
            'label' => 'Apps Name',
            'type' => 'text',
            'name' => 'apps_name',
            'mod' => 'setting',
        ],
        [
            'label' => 'Logo',
            'type' => 'text',
            'name' => 'logo',
            'open_fm' => true,
            'mod' => 'setting',

        ],
        [
            'label' => 'favicon',
            'type' => 'text',
            'name' => 'favicon',
            'open_fm' => true,
            'mod' => 'setting',
        ],


    ];
}

if (!function_exists('setting')) {
    /**
     * Ambil nilai setting berdasarkan key
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        static $settings = null;

        // Cache settings agar tidak query berulang
        if ($settings === null) {
            $settings = Cache::remember('app_settings', 3600, function () {
                return Setting::pluck('value', 'key')->toArray();
            });
        }

        if ($key === null) {
            return $settings;
        }

        return $settings[$key] ?? $default;
    }
}
