<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * Display a listing of available services.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $services = Service::where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('guest.services.index', [
            'services' => $services
        ]);
    }
}
