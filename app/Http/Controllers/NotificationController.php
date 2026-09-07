<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;
    public function index() { return $this->success(request()->user()->notifications()->latest()->paginate(20)); }
    public function read(string $notification) { request()->user()->notifications()->whereKey($notification)->update(['read_at' => now()]); return $this->success(); }
}