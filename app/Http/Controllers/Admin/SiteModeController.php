<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteModeController extends Controller
{
    public function edit()
    {
        return view('admin.mode', [
            'setting' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(array_keys(SiteSetting::MODES))],
        ]);

        SiteSetting::query()->updateOrCreate([], ['mode' => $data['mode']]);

        return redirect()->route('admin.mode')->with(
            'success',
            'Mode mis à jour : '.SiteSetting::MODES[$data['mode']].'.'
        );
    }
}
