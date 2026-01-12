<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function privacy()
    {
        $privacy = Setting::where('key', 'privacy_policy')->first();
        return view('pages.privacy', compact('privacy'));
    }

    public function terms()
    {
        $terms = Setting::where('key', 'terms_conditions')->first();
        return view('pages.terms', compact('terms'));
    }

    public function about()
    {
        $about = Setting::where('key', 'about_us')->first();
        return view('pages.about', compact('about'));
    }
}
