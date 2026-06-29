<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterviewQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = InterviewQuestion::with('creator')->get();
        return view('admin.questions.index', compact('questions'));
    }
}
