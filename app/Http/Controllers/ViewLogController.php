<?php

namespace App\Http\Controllers;

use App\Http\Requests\ViewLogRequest;
use App\Models\ViewLog;

class ViewLogController extends Controller
{
    public function index()
    {
        return ViewLog::all();
    }

    public function store(ViewLogRequest $request)
    {
        return ViewLog::create($request->validated());
    }

    public function show(ViewLog $viewLog)
    {
        return $viewLog;
    }

    public function update(ViewLogRequest $request, ViewLog $viewLog)
    {
        $viewLog->update($request->validated());

        return $viewLog;
    }

    public function destroy(ViewLog $viewLog)
    {
        $viewLog->delete();

        return response()->json();
    }
}
