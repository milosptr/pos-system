<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    private const DEFAULTS = [
        'checkin_show_upload_icon' => '1',
        'checkin_show_invoice_icon' => '1',
    ];

    public function index()
    {
        $settings = Cache::remember('settings', 60, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        return response()->json(array_merge(self::DEFAULTS, $settings));
    }

    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );

        Cache::forget('settings');

        return response()->json(['success' => true]);
    }
}
