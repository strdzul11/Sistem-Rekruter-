<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'hrd':
                return redirect()->route('hrd.dashboard');
            case 'applicant':
                return redirect()->route('applicant.dashboard');
            default:
                return redirect('/');
        }
    }
}
