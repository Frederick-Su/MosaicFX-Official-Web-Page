<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    /**
     * The MosaicFX landing page (v5 story).
     */
    public function __invoke(): View
    {
        return view('landing', ['assetVersion' => self::assetVersion()]);
    }

    /**
     * Cache-busting stamp for the CSS and JS links: changes whenever either file is edited.
     */
    public static function assetVersion(): string
    {
        // Hash of the file contents: changes only when the CSS or JS really changes, same on every machine.
        $css = (string) @md5_file(public_path('css/mosaicfx.css'));
        $js = (string) @md5_file(public_path('js/mosaicfx.js'));
        return substr(md5($css.'|'.$js), 0, 10);
    }
}
