<?php

namespace Database\Seeders;

use App\Models\TechTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Laravel', 'color_hex' => '#FF2D20'],
            ['name' => 'PHP', 'color_hex' => '#777BB4'],
            ['name' => 'Vue.js', 'color_hex' => '#42B883'],
            ['name' => 'TailwindCSS', 'color_hex' => '#06B6D4'],
            ['name' => 'TypeScript', 'color_hex' => '#3178C6'],
            ['name' => 'JavaScript', 'color_hex' => '#F7DF1E'],
            ['name' => 'Inertia.js', 'color_hex' => '#9553E9'],
            ['name' => 'Docker', 'color_hex' => '#2496ED'],
            ['name' => 'PostgreSQL', 'color_hex' => '#4169E1'],
            ['name' => 'MySQL', 'color_hex' => '#4479A1'],
            ['name' => 'Redis', 'color_hex' => '#DC382D'],
            ['name' => 'Go', 'color_hex' => '#00ADD8'],
            ['name' => 'Python', 'color_hex' => '#3776AB'],
            ['name' => 'Rust', 'color_hex' => '#DEA584'],
            ['name' => 'AWS', 'color_hex' => '#FF9900'],
            ['name' => 'Kubernetes', 'color_hex' => '#326CE5'],
            ['name' => 'WebSockets', 'color_hex' => '#F5A623'],
            ['name' => 'GraphQL', 'color_hex' => '#E10098'],
            ['name' => 'Architecture', 'color_hex' => '#0FA3B1'],
            ['name' => 'DevOps', 'color_hex' => '#10B981'],
            ['name' => 'Security', 'color_hex' => '#FF6B6B'],
            ['name' => 'Performance', 'color_hex' => '#53D2DE'],
            ['name' => 'Next.js', 'color_hex' => '#E6E6E6'],
            ['name' => 'React', 'color_hex' => '#61DAFB'],
            ['name' => 'Linux', 'color_hex' => '#FCC624'],
            ['name' => 'Git', 'color_hex' => '#F05032'],
            ['name' => 'Microservices', 'color_hex' => '#8B5CF6'],
            ['name' => 'AI & LLMs', 'color_hex' => '#EC4899'],
        ];

        foreach ($tags as $tag) {
            TechTag::firstOrCreate(
                ['slug' => Str::slug($tag['name'])],
                [
                    'name' => $tag['name'],
                    'color_hex' => $tag['color_hex'],
                ]
            );
        }
    }
}
