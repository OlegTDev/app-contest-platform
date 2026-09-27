<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ContestController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('contests/index', [

        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('contests/create', []);
    }
}
