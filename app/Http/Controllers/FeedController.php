<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    use ApiResponse;

    public function index(Request $request, PostService $posts)
    {
        $feed = $posts->feed($request->user(), $request->string('view', 'all')->toString());

        return Inertia::render('Feed', ['posts' => PostResource::collection($feed), 'view' => $request->string('view', 'all')->toString()]);
    }

    public function store(StorePostRequest $request, PostService $posts)
    {
        $data = $request->validated();
        $post = $posts->create($request->user(), $data, $request->file('images', []));

        return $this->success(new PostResource($post), 'Post published.', 201);
    }
}