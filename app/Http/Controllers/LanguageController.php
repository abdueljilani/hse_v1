<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (! in_array($locale, ['en', 'ar'])) {
            abort(404);
        }

        Session::put('locale', $locale);
        Session::save();

        App::setLocale($locale);

        return redirect()->back();
    }
}