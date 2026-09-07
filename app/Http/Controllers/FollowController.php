<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FollowService;
use App\Traits\ApiResponse;

class FollowController extends Controller
{
    use ApiResponse;
    public function toggle(User $user, FollowService $follows) { return $this->success(['following' => $follows->toggle(request()->user(), $user)]); }
}