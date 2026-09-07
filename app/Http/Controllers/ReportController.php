<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use App\Traits\ApiResponse;

class ReportController extends Controller
{
    use ApiResponse;
    public function store(StoreReportRequest $request) { return $this->success(Report::create([...$request->validated(), 'reporter_id' => $request->user()->id]), 'Report submitted.', 201); }
}