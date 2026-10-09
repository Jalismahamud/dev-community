<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Inertia\Inertia;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $notifications = request()->user()->notifications()->latest()->paginate(20);

        if (request()->wantsJson() && ! request()->header('X-Inertia')) {
            return $this->success($notifications);
        }

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function read(string $notification)
    {
        request()->user()->notifications()->whereKey($notification)->update(['read_at' => now()]);

        return $this->success();
    }
}