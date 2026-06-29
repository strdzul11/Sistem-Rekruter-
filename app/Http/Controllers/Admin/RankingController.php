<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationRanking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        $rankings = ApplicationRanking::with(['application', 'jobListing'])->get();
        return view('admin.rankings.index', compact('rankings'));
    }
}
