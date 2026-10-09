<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\TechTag;
use App\Models\User;
use Database\Seeders\ProductionCommunitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_community_seeder_executes_successfully(): void
    {
        $this->seed(ProductionCommunitySeeder::class);

        $this->assertGreaterThanOrEqual(100, User::count());
        $this->assertGreaterThanOrEqual(100, Post::count());
        $this->assertGreaterThanOrEqual(300, Comment::count());
        $this->assertGreaterThanOrEqual(20, TechTag::count());

        $testUser = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($testUser);
        $this->assertEquals('Alex Rivera', $testUser->name);
    }
}
