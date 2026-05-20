<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FooterInfo;
use App\Models\FooterSocial;
use App\Models\Setting;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        $footer_info = FooterInfo::select('id', 'phone', 'email', 'address', 'logo', 'copyright')->first();
        $footer_social_links = FooterSocial::select('id', 'name', 'icon', 'url', 'serial_no', 'status')->get();
        $siteSettings = Setting::select('id', 'site_name', 'contact_email', 'contact_phone', 'contact_address', 'map_url', 'logo', 'favicon')->first();

        return response()->json([
            'success' => true,
            'footer_info' => $footer_info,
            'footer_social_links' => $footer_social_links,
            'site_settings' => $siteSettings,
        ]);
    }
}
