<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.settings.banner');
    }

    public function banner()
    {
        return view('admin.settings.banner');
    }

    public function terms()
    {
        $terms = Setting::where('key', 'terms_conditions')->first();
        return view('admin.settings.terms', compact('terms'));
    }

    public function privacy()
    {
        $privacy = Setting::where('key', 'privacy_policy')->first();
        return view('admin.settings.privacy', compact('privacy'));
    }

    public function about()
    {
        $about = Setting::where('key', 'about_us')->first();
        return view('admin.settings.about', compact('about'));
    }

    public function updateTerms(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'terms_conditions'],
            ['value' => $request->value]
        );

        return redirect()->back()->with('success', 'Terms & Conditions updated successfully.');
    }

    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'privacy_policy'],
            ['value' => $request->value]
        );

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }

    public function updateAbout(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'about_us'],
            ['value' => $request->value]
        );

        return redirect()->back()->with('success', 'About Us updated successfully.');
    }
}
