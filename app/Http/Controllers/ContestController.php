<?php

namespace App\Http\Controllers;

use App\Enums\ContestType;
use App\Http\Requests\ContestRequest;
use App\Models\Contest;
use Illuminate\Http\RedirectResponse;
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
        $contestTypes = ContestType::selectOptions();
        return Inertia::render('contests/create', [
            'contestTypes' => $contestTypes,
        ]);
    }

    public function store(ContestRequest $request): RedirectResponse
    {
        Contest::create($request->validated());
        return to_route('contest.index')->with('success', 'Contest created successfully.');
    }

}
