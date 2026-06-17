<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index()
    {
        return auth()->check()
            ? Inertia::render('Dashboard')
            : Inertia::render('Welcome');
    }
    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }
}
