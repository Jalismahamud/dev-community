<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Conversation;
use App\Models\Follow;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostLike;
use App\Models\PostShare;
use App\Models\TechTag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProductionCommunitySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TechTagSeeder::class);
        $tags = TechTag::all()->keyBy('name');

        // 1. Curated realistic tech images from Unsplash
        $techImages = [
            'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1607799279861-4dd421887fb3?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1504639725590-34d0984388bd?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1537498425277-c283d32ef9db?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1523800503107-5bc3ba2a6481?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1571171637578-41bc2dd41cd2?w=1200&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&auto=format&fit=crop&q=80',
        ];

        // 2. Developer Personas / Seed Data
        $developers = [
            [
                'name' => 'Alex Rivera',
                'email' => 'test@example.com',
                'current_status' => 'Lead Platform Architect @ CloudScale',
                'bio' => "Staff Architect & Open Source Contributor. Passionate about distributed systems, Laravel Octane, and high-concurrency event-driven architectures. Building scalable software with passion. ☕️⚡️",
                'avatar_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80',
                'github_url' => 'https://github.com/alexrivera-dev',
                'linkedin_url' => 'https://linkedin.com/in/alex-rivera-architect',
                'tags' => ['Laravel', 'Architecture', 'PostgreSQL', 'Docker', 'Redis'],
            ],
            [
                'name' => 'Community Admin',
                'email' => 'admin@dev.community',
                'current_status' => 'Platform Administrator & Head of Moderation',
                'bio' => 'System administrator managing community health, safety reports, and platform operations.',
                'avatar_path' => 'https://i.pravatar.cc/300?img=69',
                'github_url' => 'https://github.com/devcommunity-admin',
                'linkedin_url' => 'https://linkedin.com/in/devcommunity-admin',
                'tags' => ['Architecture', 'Security', 'DevOps'],
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'elena.rostova@dev.community',
                'current_status' => 'Principal Database Engineer @ FinScale',
                'bio' => "Specialized in high-scale PostgreSQL tuning, partitioning, and zero-downtime database migrations. Contributor to pgvector and open data standards.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=47',
                'github_url' => 'https://github.com/erostova',
                'linkedin_url' => 'https://linkedin.com/in/elena-rostova-db',
                'tags' => ['PostgreSQL', 'Performance', 'Architecture', 'Linux'],
            ],
            [
                'name' => 'Marcus Chen',
                'email' => 'marcus.chen@dev.community',
                'current_status' => 'Staff Frontend Architect | Vue 3 Core Enthusiast',
                'bio' => "Obsessed with frontend runtime performance, AST transforms, Vite plugins, and fluid micro-interactions. Building design systems for enterprise scale.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=12',
                'github_url' => 'https://github.com/marcuschen-fe',
                'linkedin_url' => 'https://linkedin.com/in/marcus-chen-tech',
                'tags' => ['Vue.js', 'TypeScript', 'TailwindCSS', 'Inertia.js'],
            ],
            [
                'name' => 'Aisha Al-Mansoor',
                'email' => 'aisha.mansoor@dev.community',
                'current_status' => 'Senior SRE & Cloud Architect @ HyperCloud',
                'bio' => "Kubernetes wrangler, Terraform lover, and latency investigator. I keep distributed clusters alive under peak traffic bursts. ☁️🚀",
                'avatar_path' => 'https://i.pravatar.cc/300?img=32',
                'github_url' => 'https://github.com/aishamansoor',
                'linkedin_url' => 'https://linkedin.com/in/aisha-al-mansoor',
                'tags' => ['Kubernetes', 'AWS', 'DevOps', 'Docker', 'Linux'],
            ],
            [
                'name' => 'Liam O\'Connor',
                'email' => 'liam.oconnor@dev.community',
                'current_status' => 'Full-Stack Lead | Laravel, Inertia & TailwindCSS',
                'bio' => "Crafting rapid SaaS products. Advocate for the 'Ship fast, measure everything' engineering philosophy. Writer on dev.to and hashnode.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=60',
                'github_url' => 'https://github.com/liamoconnor-ie',
                'linkedin_url' => 'https://linkedin.com/in/liam-oconnor-dev',
                'tags' => ['Laravel', 'Vue.js', 'Inertia.js', 'TailwindCSS', 'PHP'],
            ],
            [
                'name' => 'Sofia Rodriguez',
                'email' => 'sofia.rodriguez@dev.community',
                'current_status' => 'Distributed Systems Engineer | Go & Rust',
                'bio' => "Building low-latency networking primitives and consensus algorithms. When not writing Go, I'm reading distributed papers or cycling.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=49',
                'github_url' => 'https://github.com/sofiarodriguez-sys',
                'linkedin_url' => 'https://linkedin.com/in/sofia-rodriguez-systems',
                'tags' => ['Go', 'Rust', 'Microservices', 'WebSockets'],
            ],
            [
                'name' => 'Kenji Sato',
                'email' => 'kenji.sato@dev.community',
                'current_status' => 'Security Researcher & DevSecOps Engineer',
                'bio' => "Pen-testing APIs, auditing smart contracts, and hardening containerized runtimes. Defense-in-depth is the only true defense. 🔒🛡️",
                'avatar_path' => 'https://i.pravatar.cc/300?img=11',
                'github_url' => 'https://github.com/kenjisato-sec',
                'linkedin_url' => 'https://linkedin.com/in/kenji-sato-security',
                'tags' => ['Security', 'Linux', 'Docker', 'DevOps'],
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@dev.community',
                'current_status' => 'AI Engineer & ML Infrastructure Lead',
                'bio' => "Deploying LLMs at scale with vLLM, TensorRT-LLM, and Python. Bridging the gap between frontier research models and production APIs.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=26',
                'github_url' => 'https://github.com/priyasharma-ai',
                'linkedin_url' => 'https://linkedin.com/in/priya-sharma-ml',
                'tags' => ['AI & LLMs', 'Python', 'AWS', 'Docker'],
            ],
            [
                'name' => 'David Miller',
                'email' => 'david.miller@dev.community',
                'current_status' => 'Backend Specialist @ Fintech Core Banking',
                'bio' => "15 years building transactional ledger engines and high-throughput payment bridges. Obsessed with ACID guarantees and idempotent API design.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=68',
                'github_url' => 'https://github.com/davidmiller-fin',
                'linkedin_url' => 'https://linkedin.com/in/david-miller-fintech',
                'tags' => ['PHP', 'Laravel', 'PostgreSQL', 'Redis', 'Architecture'],
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@dev.community',
                'current_status' => 'UI/UX Developer & Design Systems Lead',
                'bio' => "Bridging Figma and production code. Passionate about WCAG AAA accessibility, micro-animations, and CSS container queries.",
                'avatar_path' => 'https://i.pravatar.cc/300?img=45',
                'github_url' => 'https://github.com/fatimazahra-ui',
                'linkedin_url' => 'https://linkedin.com/in/fatima-zahra-design',
                'tags' => ['Vue.js', 'TailwindCSS', 'JavaScript', 'TypeScript'],
            ],
        ];

        // Generate 91 additional realistic global developer personas to reach exactly 101 users
        $firstNames = ['Tanvir', 'Hasan', 'Carlos', 'Lucas', 'Zoe', 'Maya', 'Rohan', 'Nina', 'Mateo', 'Chiara', 'Tariq', 'Viktor', 'Amara', 'Noah', 'Emma', 'Luka', 'Hiroshi', 'Leila', 'Gabriel', 'Ingrid', 'Arjun', 'Sven', 'Mia', 'Kofi', 'Chloe', 'Dmitry', 'Ananya', 'Felix', 'Fatima', 'Arthur', 'Nadia', 'Tobias', 'Camila', 'Farhan', 'Valerie', 'Sean', 'Yuki', 'Elias', 'Siddharth', 'Jonas', 'Mei', 'Oscar', 'Amina', 'Dante', 'Astrid', 'Samir', 'Clara', 'Riku', 'Zara', 'Oliver'];
        $lastNames = ['Hasan', 'Silva', 'Kowalski', 'Dubois', 'Lin', 'Gupta', 'Meyer', 'Ricci', 'Nasser', 'Ivanov', 'Okafor', 'Bauer', 'Vidal', 'Lindqvist', 'Nakamura', 'Abbas', 'Santos', 'Larsson', 'Patel', 'Andersen', 'Moreau', 'Novak', 'Chowdhury', 'Fischer', 'Popov', 'Menon', 'Wagner', 'Mansour', 'Fontaine', 'Nielsen', 'Morales', 'Schmidt', 'Sultana', 'Castillo', 'Benoit', 'Gallagher', 'Yamamoto', 'Kruger', 'Verhoeven', 'Takahashi'];
        $roles = [
            ['title' => 'Senior Backend Engineer', 'status' => 'Senior Backend Engineer | High-concurrency APIs & PHP 8.4', 'tags' => ['PHP', 'Laravel', 'PostgreSQL', 'Redis']],
            ['title' => 'Staff Frontend Architect', 'status' => 'Staff Frontend Architect | Vue 3, Vite & Micro-frontends', 'tags' => ['Vue.js', 'TypeScript', 'TailwindCSS', 'JavaScript']],
            ['title' => 'DevOps & SRE Specialist', 'status' => 'DevOps Specialist | Kubernetes, Terraform & CI/CD Pipelines', 'tags' => ['DevOps', 'Kubernetes', 'Docker', 'AWS', 'Linux']],
            ['title' => 'Distributed Systems Engineer', 'status' => 'Distributed Systems Engineer | Go, Kafka & Event Streaming', 'tags' => ['Go', 'Microservices', 'WebSockets', 'Architecture']],
            ['title' => 'Full-Stack Developer', 'status' => 'Full-Stack Developer | Building SaaS with Laravel & Inertia.js', 'tags' => ['Laravel', 'Vue.js', 'Inertia.js', 'MySQL']],
            ['title' => 'Database Performance Engineer', 'status' => 'Database Performance Engineer | PostgreSQL & MySQL query tuning', 'tags' => ['PostgreSQL', 'MySQL', 'Performance', 'Redis']],
            ['title' => 'Cloud Solutions Architect', 'status' => 'AWS Certified Solutions Architect | Serverless & EventBridge', 'tags' => ['AWS', 'DevOps', 'Architecture', 'Docker']],
            ['title' => 'Systems & Rust Engineer', 'status' => 'Systems Programmer | Memory safety, WebAssembly & Rust', 'tags' => ['Rust', 'Performance', 'Linux', 'Security']],
            ['title' => 'AI Applications Engineer', 'status' => 'AI Engineer | RAG pipelines, Vector Embeddings & Python', 'tags' => ['AI & LLMs', 'Python', 'Docker', 'PostgreSQL']],
            ['title' => 'Application Security Engineer', 'status' => 'AppSec Engineer | OAuth2, Zero-Trust & API Security', 'tags' => ['Security', 'Linux', 'Architecture', 'DevOps']],
        ];

        $avatarIndex = 1;
        for ($i = 0; $i < 91; $i++) {
            $fn = $firstNames[$i % count($firstNames)];
            $ln = $lastNames[($i * 3 + 7) % count($lastNames)];
            $fullName = "{$fn} {$ln}";
            $slug = Str::slug($fullName) . ($i > 50 ? $i : '');
            $role = $roles[$i % count($roles)];
            $avatarId = ($avatarIndex % 70) + 1;
            $avatarIndex++;

            $developers[] = [
                'name' => $fullName,
                'email' => "{$slug}@dev.community",
                'current_status' => $role['status'],
                'bio' => "Software engineer with 7+ years of building production web platforms. Believer in type-safety, automated testing, and developer experience. Writing code that lasts.",
                'avatar_path' => "https://i.pravatar.cc/300?img={$avatarId}",
                'github_url' => "https://github.com/{$slug}",
                'linkedin_url' => "https://linkedin.com/in/{$slug}",
                'tags' => $role['tags'],
            ];
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Insert or update users
        $createdUsers = [];
        foreach ($developers as $devData) {
            $userTags = $devData['tags'];
            unset($devData['tags']);

            $user = User::updateOrCreate(
                ['email' => $devData['email']],
                array_merge($devData, [
                    'username' => explode('@', $devData['email'])[0],
                    'password' => Hash::make('password'),
                    'email_verified_at' => Carbon::now()->subDays(rand(10, 100)),
                ])
            );

            if ($user->email === 'admin@dev.community' || $user->email === 'test@example.com') {
                $user->assignRole($adminRole);
            }

            // Attach user tech tags
            $tagIds = [];
            foreach ($userTags as $tagName) {
                if (isset($tags[$tagName])) {
                    $tagIds[] = $tags[$tagName]->id;
                }
            }
            $user->techTags()->sync($tagIds);

            $createdUsers[] = $user;
        }

        $allUsers = collect($createdUsers);
        $testUser = $allUsers->firstWhere('email', 'test@example.com') ?? $allUsers->first();

        // 3. Realistic Rich Developer Posts
        $postsData = [
            [
                'title' => 'Deep Dive: Achieving Sub-10ms Database Queries in Laravel 12 with PostgreSQL Partial Indexes',
                'tags' => ['Laravel', 'PostgreSQL', 'Performance', 'Architecture'],
                'body' => "When our active user records crossed **15 million rows**, our query latencies on the feed started creeping up to 450ms during peak hours.\n\nHere is how we solved it using PostgreSQL **partial indexes** and composite btree indexes without touching any Redis cache layer.\n\n### The Problem\nOur notifications and active feed queries were filtering by `read_at IS NULL` and `status = 'published'`:\n\n```sql\nSELECT * FROM notifications \nWHERE user_id = 4921 AND read_at IS NULL \nORDER BY created_at DESC LIMIT 20;\n```\n\nEven with an index on `(user_id, created_at)`, PostgreSQL was scanning through tens of thousands of already-read rows.\n\n### The Solution: Partial Index\nBecause 98% of notifications in our database are already read, we indexed ONLY the unread notifications:\n\n```sql\nCREATE INDEX idx_unread_notifications \nON notifications (user_id, created_at DESC) \nWHERE read_at IS NULL;\n```\n\n### The Result\n- **Index size reduced from 3.2 GB to 48 MB** (98.5% disk savings!)\n- **Query execution time dropped from 380ms to 4.2ms**\n- Zero application-level cache invalidation bugs.\n\nAlways check your `EXPLAIN (ANALYZE, BUFFERS)` output before reaching for an external cache!",
                'image_idx' => 0,
            ],
            [
                'title' => 'Why We Migrated from Next.js to Laravel 12 + Inertia Vue 3 for Our Enterprise SaaS',
                'tags' => ['Laravel', 'Vue.js', 'Inertia.js', 'TypeScript'],
                'body' => "After 18 months of maintaining a separate Next.js frontend and Node/NestJS backend, our team decided to unify the entire stack into **Laravel 12 + Inertia.js with Vue 3**.\n\nHere is our honest breakdown after 6 months in production:\n\n### 1. The API Duplication Tax Is Gone\nPreviously, every simple feature required:\n- A backend DTO and route\n- A frontend Axios call with React query hooks\n- Duplicated TypeScript interfaces\n- Complex state hydration and CORS configurations\n\nWith Inertia, our controllers return:\n```php\nreturn Inertia::render('Dashboard/Overview', [\n    'metrics' => fn () => \$metricService->getAggregates(\$request->user()),\n    'tenants' => fn () => TenantResource::collection(\$tenants),\n]);\n```\nEverything is typed, reactive, and authenticated with standard Laravel session cookies.\n\n### 2. Vue 3 `<script setup>` with Vite is Insanely Fast\nCold HMR reloads happen in **sub-25ms**. The DX difference between webpack/Next turbopack edge quirks and Vite + Laravel is night and day.\n\n### 3. Reduced Team Cognitive Load\nOur engineers now ship complete end-to-end features in a single pull request. Deployment takes 45 seconds via GitHub Actions.",
                'image_idx' => 1,
            ],
            [
                'title' => 'Shrinking Docker Production Images from 1.4GB down to 88MB with Multi-Stage Builds',
                'tags' => ['Docker', 'DevOps', 'Linux', 'PHP'],
                'body' => "Container build times and image transfer sizes directly impact our autoscaling group boot times on AWS ECS.\n\nHere is the exact multi-stage Dockerfile pattern we use for our high-performance PHP 8.4 + FrankenPHP / Octane microservices:\n\n```dockerfile\n# Stage 1: Build Frontend Assets\nFROM node:22-alpine AS frontend\nWORKDIR /app\nCOPY package*.json ./\nRUN npm ci --prefer-offline\nCOPY resources/ ./resources/\nCOPY vite.config.js ./\nRUN npm run build\n\n# Stage 2: Vendor Composer dependencies\nFROM composer:2 AS vendor\nWORKDIR /app\nCOPY composer*.json ./\nRUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader\n\n# Stage 3: Production Alpine Runtime\nFROM php:8.4-alpine AS production\nRUN docker-php-ext-install pdo_mysql opcache bcmath\nCOPY --from=vendor /app/vendor /var/www/html/vendor\nCOPY --from=frontend /app/public/build /var/www/html/public/build\nCOPY . /var/www/html\n\nEXPOSE 8000\nCMD [\"php\", \"artisan\", \"octane:start\", \"--server=frankenphp\", \"--port=8000\"]\n```\n\n### Outcome:\n- Total image size: **88.4 MB**\n- Pull time on cold ECS Task: **1.8 seconds**\n- Build time cached: **14 seconds**",
                'image_idx' => 2,
            ],
            [
                'title' => 'Mastering Redis Streams for Event-Driven Job Distribution',
                'tags' => ['Redis', 'Architecture', 'Go', 'Microservices'],
                'body' => "Most developers default to Redis Pub/Sub for realtime messaging. But Pub/Sub has one fatal flaw: **fire-and-forget without persistence**. If a subscriber is offline for 2 seconds during a restart, those events are permanently lost.\n\nEnter **Redis Streams** with Consumer Groups:\n\n```bash\n# Producer adds event\nXADD orders:events * event_type \"order_placed\" order_id \"9482\" amount \"199.50\"\n\n# Consumer group reads with auto-acknowledgment\nXREADGROUP GROUP processing_workers worker_1 COUNT 10 BLOCK 2000 STREAMS orders:events >\n\n# Acknowledge after successful processing\nXACK orders:events processing_workers 1728472910-0\n```\n\n### Why Redis Streams win:\n1. **Persistent history** with configurable stream capping (`MAXLEN ~ 100000`)\n2. **Pending Entries List (PEL)** automatically tracks unacknowledged messages for retries\n3. **Consumer groups** distribute work across multiple parallel worker instances.",
                'image_idx' => 3,
            ],
            [
                'title' => 'Vue 3 Optimization Tip: When to Use shallowRef Instead of ref',
                'tags' => ['Vue.js', 'JavaScript', 'Performance', 'TypeScript'],
                'body' => "Are you rendering large tables, code buffers, or map datasets with Vue 3 and experiencing UI stutter?\n\nHere is a common performance trap:\n```javascript\n// ❌ BAD: Vue recursively wraps all 10,000 objects in deep reactive Proxies\nconst telemetryLogs = ref(largeDataset);\n\n// ✅ GOOD: Only triggers updates when the entire array reference changes\nconst telemetryLogs = shallowRef(largeDataset);\n```\n\n### Benchmark Results:\n- Memory footprint: **82% reduction**\n- Rendering latency: **from 180ms down to 14ms**\n- Garbage collection pauses: virtually zero!\n\nIf you are only re-assigning the array or mutating values from an external API response, always consider `shallowRef`!",
                'image_idx' => null,
            ],
            [
                'title' => 'Zero-Downtime Schema Migrations on Tables with 50M+ Rows',
                'tags' => ['PostgreSQL', 'Architecture', 'DevOps', 'Laravel'],
                'body' => "Running `ALTER TABLE users ADD COLUMN phone VARCHAR(20) NOT NULL DEFAULT '';` on a large table will acquire an **ACCESS EXCLUSIVE** lock, bringing down your entire production database.\n\nHere is the three-phase migration pattern we enforce:\n\n### Phase 1: Add column as nullable\n```sql\nALTER TABLE users ADD COLUMN phone VARCHAR(20);\n```\n*(In modern PostgreSQL, adding a nullable column or a column with a constant default takes < 1ms because it updates metadata only).*\n\n### Phase 2: Backfill existing rows asynchronously in chunks\n```php\nUser::whereNull('phone')->chunkById(5000, function (\$users) {\n    foreach (\$users as \$user) {\n        \$user->update(['phone' => 'pending']);\n    }\n    usleep(50000); // 50ms pause to let replica lag catch up\n});\n```\n\n### Phase 3: Add the NOT NULL constraint with VALIDATE\n```sql\nALTER TABLE users ADD CONSTRAINT check_phone_not_null CHECK (phone IS NOT NULL) NOT VALID;\nALTER TABLE users VALIDATE CONSTRAINT check_phone_not_null;\n```\nZero locks. Zero downtime. Zero customer complaints.",
                'image_idx' => 4,
            ],
            [
                'title' => 'Realtime WebSockets with Laravel Reverb: Production Benchmarking & Clustering',
                'tags' => ['Laravel', 'WebSockets', 'Architecture', 'Performance'],
                'body' => "We recently stress-tested the new **Laravel Reverb** server under high concurrent connections.\n\n### Test Architecture:\n- 1x AWS c6g.large (2 vCPU, 4GB RAM)\n- Reverb running with Redis pulse backend\n- Artillery load generator simulating real browser socket lifecycles\n\n### Peak Numbers:\n- **35,000 simultaneous connections** per single node\n- Memory consumption: **~195MB total**\n- Broadcast dispatch latency: **under 12ms p99**\n\nReverb has officially eliminated our need for third-party paid socket services like Pusher or Ably. The pure PHP asynchronous event loop powered by ReactPHP under the hood is remarkably robust.",
                'image_idx' => 5,
            ],
            [
                'title' => 'TypeScript Strict Mode: Advanced Utility Types You Should Be Using in 2026',
                'tags' => ['TypeScript', 'JavaScript'],
                'body' => "Stop writing `any` or repeating interface shapes. Here are 3 TypeScript patterns that made our codebase 10x safer:\n\n### 1. Deep Readonly\n```typescript\ntype DeepReadonly<T> = {\n    readonly [P in keyof T]: T[P] extends object ? DeepReadonly<T[P]> : T[P];\n};\n```\n\n### 2. Inferring API Return Types\n```typescript\ntype ApiResponse<T extends (...args: any[]) => any> = Awaited<ReturnType<T>>;\n```\n\n### 3. Discriminated Union Guards\n```typescript\ntype SignalEvent = \n    | { type: 'post.created'; payload: { id: number; title: string } }\n    | { type: 'comment.added'; payload: { postId: number; body: string } };\n\nfunction handleSignal(event: SignalEvent) {\n    switch (event.type) {\n        case 'post.created':\n            console.log(event.payload.title); // Strongly typed!\n            break;\n    }\n}\n```",
                'image_idx' => null,
            ],
            [
                'title' => 'How We Hardened API Authentication: Refresh Token Rotation & Device Fingerprinting',
                'tags' => ['Security', 'Architecture', 'Laravel'],
                'body' => "Storing long-lived JWTs in `localStorage` is an XSS catastrophe waiting to happen.\n\nHere is our current architecture:\n1. **Access Token**: Short-lived (10 minutes), stored in memory state only.\n2. **Refresh Token**: Stored in an `httpOnly`, `Secure`, `SameSite=Strict` cookie.\n3. **Automatic Rotation**: Whenever a refresh token is exchanged, a new one is issued and the old family UUID is immediately invalidated.\n\nIf an attacker steals an old refresh token and attempts to replay it, our backend detects family reuse, revokes **ALL** sessions for that user account, and triggers a security alert.\n\nSecurity is about layers, not silver bullets.",
                'image_idx' => 6,
            ],
            [
                'title' => 'Go Concurrency in Practice: Graceful Worker Pools with Channels and Context',
                'tags' => ['Go', 'Architecture', 'Microservices'],
                'body' => "Here is a pattern every Go developer should have in their toolkit when processing millions of incoming webhooks without exhausting system file descriptors:\n\n```go\npackage main\n\nimport (\n    \"context\"\n    \"fmt\"\n    \"sync\"\n)\n\nfunc WorkerPool(ctx context.Context, numWorkers int, jobs <-chan int, wg *sync.WaitGroup) {\n    for i := 1; i <= numWorkers; i++ {\n        wg.Add(1)\n        go func(workerID int) {\n            defer wg.Done()\n            for {\n                select {\n                case <-ctx.Done():\n                    return\n                case job, ok := <-jobs:\n                    if !ok {\n                        return\n                    }\n                    fmt.Printf(\"Worker %d processing job %d\\n\", workerID, job)\n                }\n            }\n        }(i)\n    }\n}\n```\n\nBounded concurrency guarantees that memory usage remains strictly flat even during upstream traffic spikes.",
                'image_idx' => 7,
            ],
            [
                'title' => 'TailwindCSS v4: The New Lightning-Fast Engine & Native CSS Variables',
                'tags' => ['TailwindCSS', 'Vue.js', 'JavaScript'],
                'body' => "Upgraded three enterprise frontends to the new TailwindCSS engine today.\n\n### What blew us away:\n- **Zero config file by default**: Everything configured via `@theme` directly in CSS.\n- Build times dropped from **2.4s to 120ms** thanks to the Rust-based Oxide parser.\n- First-class CSS container queries and modern color spaces (`oklch()`).\n\n```css\n@theme {\n  --color-brand-primary: #F5A623;\n  --color-brand-accent: #0FA3B1;\n  --font-mono: 'JetBrains Mono', monospace;\n}\n```\nThe web platform is advancing at a breathtaking pace!",
                'image_idx' => null,
            ],
            [
                'title' => 'Demystifying pgvector: Adding Semantic AI Search to an Existing PostgreSQL Database',
                'tags' => ['PostgreSQL', 'AI & LLMs', 'Python', 'Architecture'],
                'body' => "You don't need Pinecone, Milvus, or a standalone vector database if your dataset is under 20 million vectors. PostgreSQL with the `pgvector` extension handles this beautifully with ACID compliance.\n\n```sql\n-- Enable extension\nCREATE EXTENSION IF NOT EXISTS vector;\n\n-- Add embedding vector column (1536 dims for OpenAI text-embedding-3-small)\nALTER TABLE posts ADD COLUMN embedding vector(1536);\n\n-- Create HNSW index for ultra-fast approximate nearest neighbor queries\nCREATE INDEX idx_posts_embedding_hnsw ON posts \nUSING hnsw (embedding vector_cosine_ops)\nWITH (m = 16, ef_construction = 64);\n\n-- Query semantic matches in < 5ms!\nSELECT id, title, 1 - (embedding <=> '[0.012, -0.045, ...]') AS similarity\nFROM posts\nORDER BY embedding <=> '[0.012, -0.045, ...]'\nLIMIT 10;\n```\nFewer moving parts means fewer 3 AM production incident calls.",
                'image_idx' => 8,
            ],
            [
                'title' => 'Our Complete GitHub Actions CI/CD Pipeline: Sub-2 Minute Builds',
                'tags' => ['DevOps', 'Git', 'Docker', 'Linux'],
                'body' => "Slow test suites kill developer velocity. We brought our PR verification pipeline from 11 minutes down to 1 minute 45 seconds.\n\n### The secrets:\n1. **GitHub Actions Cache for Composer and NPM**:\n   Cache key based on `composer.lock` and `package-lock.json`.\n2. **In-memory SQLite for Unit Tests**:\n   Running isolated unit tests against `:memory:` database instead of a physical container.\n3. **Parallelized matrix test jobs**:\n   Frontend linting, static analysis (`phpstan max`), and feature tests run concurrently on 4 separate runners.\n4. **Pre-warmed Docker base layers**.\n\nInvest in your pipeline speed; your team will thank you every day!",
                'image_idx' => 9,
            ],
            [
                'title' => 'Clean Architecture in Laravel: Domain Services without Unnecessary Bloat',
                'tags' => ['Laravel', 'Architecture', 'PHP'],
                'body' => "A lot of developers over-complicate Laravel with 6 layers of repositories, factories, DTOs, and interface wrappers for simple CRUD apps.\n\nOur rule of thumb for clean Laravel architecture:\n- **Controllers**: Thin HTTP input/output mappers. Validate request, invoke Service or Action, return Resource.\n- **Form Requests**: Strict validation and authorization logic only.\n- **Domain Services / Actions**: Single responsibility business logic.\n- **Eloquent Models**: Data schema, relationships, and localized scopes.\n- **API Resources**: DTO transformations for consistent JSON / Inertia payloads.\n\nKeep it simple, maintainable, and pragmatic!",
                'image_idx' => null,
            ],
            [
                'title' => 'Kubernetes Pod Autoscaling (HPA) Based on RabbitMQ Queue Depth',
                'tags' => ['Kubernetes', 'DevOps', 'AWS', 'Microservices'],
                'body' => "Default Kubernetes HPA scales on CPU or Memory usage. But for asynchronous queue workers, CPU can remain at 15% while the queue accumulates 500,000 backlog messages!\n\nUsing **KEDA (Kubernetes Event-driven Autoscaling)**:\n```yaml\napiVersion: keda.sh/v1alpha1\nkind: ScaledObject\nmetadata:\n  name: order-workers-scaler\nspec:\n  scaleTargetRef:\n    name: order-worker-deployment\n  minReplicaCount: 2\n  maxReplicaCount: 50\n  triggers:\n  - type: rabbitmq\n    metadata:\n      queueName: orders_processing\n      mode: QueueLength\n      value: \"100\" # Scale 1 pod per 100 pending messages\n```\nNow workers spin up within seconds of a traffic spike and scale back down to zero idle instances.",
                'image_idx' => 10,
            ],
            [
                'title' => 'Why We Switched from MySQL 5.7 to PostgreSQL 16: An Unfiltered Review',
                'tags' => ['PostgreSQL', 'MySQL', 'Architecture'],
                'body' => "Last year we migrated our main SaaS database from MySQL to PostgreSQL 16. Here are the biggest upgrades:\n\n1. **Transactional DDL**: In Postgres, if a migration fails halfway through, the schema change rolls back cleanly. In MySQL, failed DDL leaves the database in an inconsistent state.\n2. **JSONB with GIN indexing**: Querying nested telemetry payloads is 5x faster.\n3. **Generated Columns and CTEs**: Window functions and recursive queries allow complex reporting without offloading to application code.\n4. **Rich ecosystem of extensions**: PostGIS, pgvector, and citext.\n\nNever looking back!",
                'image_idx' => 11,
            ],
            [
                'title' => 'Building Custom Vite Plugins for Automatic Icon & SVG Tree Shaking',
                'tags' => ['Vue.js', 'JavaScript', 'TypeScript'],
                'body' => "Instead of importing entire icon iconsets that bloat the client bundle by 800KB, we wrote a lightweight Vite transform plugin that converts raw SVG files into pure Vue functional components at build time.\n\n### Benefits:\n- 0 runtime overhead\n- Full tree-shaking support\n- 100% SVG style controllability via Tailwind CSS classes like `text-[#F5A623] h-5 w-5`.\n\nCustom build tooling is one of the highest leverage skills you can develop.",
                'image_idx' => null,
            ],
            [
                'title' => 'Understanding PHP 8.4 Property Hooks & Asymmetric Visibility',
                'tags' => ['PHP', 'Laravel'],
                'body' => "PHP 8.4 brings one of the most exciting language features in years: **Property Hooks**.\n\nSay goodbye to repetitive getter and setter boilerplate:\n\n```php\nclass DeveloperProfile\n{\n    public private(set) string \$slug;\n\n    public string \$name {\n        set {\n            \$this->name = trim(\$value);\n            \$this->slug = strtolower(str_replace(' ', '-', \$this->name));\n        }\n    }\n}\n```\n\nNotice `public private(set)`! Read is public, but write is strictly private to the class. Beautiful, concise, and type-safe.",
                'image_idx' => 12,
            ],
            [
                'title' => 'Rust for Web Developers: Writing Your First High-Performance CLI Tool',
                'tags' => ['Rust', 'Linux'],
                'body' => "If you are coming from JavaScript or Python, Rust's borrow checker can feel intimidating at first. But the compiler errors are the best interactive tutor in the world.\n\nUsing the `clap` crate, we built an internal log parsing CLI that processes **12GB of Nginx access logs in 4.1 seconds**. The same task in Python took 8 minutes.\n\nInvest a weekend in Rust. It will fundamentally improve how you think about memory and data ownership in every other language.",
                'image_idx' => 13,
            ],
            [
                'title' => 'Event Sourcing in Production: What They Don’t Tell You in the Tutorials',
                'tags' => ['Architecture', 'Microservices', 'PostgreSQL'],
                'body' => "Tutorials make Event Sourcing look trivial: append events, fold state, rebuild aggregate. Done, right?\n\nHere is what reality looks like after 2 years in production:\n1. **Event Schema Evolution**: What happens when an event schema from 2024 has 3 fewer fields than today's code expects? You need Upcasters.\n2. **Snapshotting**: Replaying 800,000 events to reconstruct a single shopping cart will timeout your HTTP request. Snapshotting every 100 events is mandatory.\n3. **Projections & Idempotency**: Projections will fail or lag. Design them to be idempotent from day one.\n\nEvent Sourcing is incredible for auditability, but never adopt it without evaluating whether a simple append-only audit log meets your actual requirements.",
                'image_idx' => 14,
            ],
            [
                'title' => 'Practical Guide: Monitoring Laravel Applications with OpenTelemetry and Grafana',
                'tags' => ['DevOps', 'Laravel', 'Architecture', 'Performance'],
                'body' => "Eliminate guesswork during outages. With OpenTelemetry auto-instrumentation, every incoming HTTP request generates a distributed trace spanning:\n- Database query execution times\n- External Stripe webhook HTTP calls\n- Redis cache hits and misses\n- Queue job dispatching latency\n\nCorrelating logs with trace IDs in Grafana Tempo lets our on-call engineers pinpoint slow queries in under 60 seconds.",
                'image_idx' => 15,
            ],
            [
                'title' => 'CSS Subgrid: Solving the Age-Old Problem of Aligned Card Layouts',
                'tags' => ['TailwindCSS', 'JavaScript'],
                'body' => "Ever had a 3-column card grid where headers, bodies, and footer buttons wouldn't align because card descriptions had varying text lengths?\n\nWith modern CSS `grid-template-rows: subgrid`, child elements directly participate in the parent grid tracks:\n\n```css\n.card-grid {\n    display: grid;\n    grid-template-columns: repeat(3, 1fr);\n    gap: 1.5rem;\n}\n\n.card {\n    display: grid;\n    grid-row: span 3;\n    grid-template-rows: subgrid;\n}\n```\nAll buttons and headers stay laser-aligned across rows regardless of dynamic content size. Pure CSS magic!",
                'image_idx' => null,
            ],
            [
                'title' => 'Mastering Git Rebase: Clean Commit Histories for Collaborative Teams',
                'tags' => ['Git', 'DevOps'],
                'body' => "Merge commits like `Merge branch 'main' into fix-auth-bug` clutter git history and make `git bisect` frustratingly difficult to use.\n\nOur team workflow:\n```bash\ngit fetch origin\ngit rebase origin/main\ngit push --force-with-lease\n```\nUse `--force-with-lease` instead of raw `--force` to guarantee you never accidentally overwrite a teammate’s commits. Keep your Git history linear and readable!",
                'image_idx' => 16,
            ],
            [
                'title' => 'Building Resilience: Circuit Breaker Pattern for External API Dependencies',
                'tags' => ['Architecture', 'Microservices', 'Laravel'],
                'body' => "When a third-party payment gateway or SMS provider goes down, naive code keeps hammering their endpoint until all PHP-FPM worker pools are exhausted waiting for HTTP timeouts.\n\nImplement the **Circuit Breaker pattern**:\n- **Closed**: Requests flow normally. Failures are counted.\n- **Open**: If failure threshold (e.g. 50% over 10s) is reached, fail fast immediately for the next 30 seconds without calling the remote endpoint.\n- **Half-Open**: Send a single canary request to test recovery.\n\nProtect your application from cascading downstream failures.",
                'image_idx' => 17,
            ],
            [
                'title' => 'Python for High-Throughput Data Pipelines: AsyncIO vs Multiprocessing',
                'tags' => ['Python', 'Performance', 'Architecture'],
                'body' => "A quick rule for Python backend engineers:\n- **I/O Bound** (fetching 50,000 URLs, querying APIs): Use `asyncio` with `aiohttp` or `httpx`. Single thread, tens of thousands of concurrent sockets.\n- **CPU Bound** (image processing, data transformations, matrix math): Use `multiprocessing` or native C/Rust extensions to bypass the GIL.\n\nChoosing the right concurrency model makes a 50x throughput difference.",
                'image_idx' => 18,
            ],
        ];

        // Seed 125 posts distributed across all users over the last 30 days
        $seededPosts = [];
        $totalPostsCount = 125;

        for ($p = 0; $p < $totalPostsCount; $p++) {
            $template = $postsData[$p % count($postsData)];
            $iteration = (int) floor($p / count($postsData));
            $postTitle = $iteration === 0 ? $template['title'] : "{$template['title']} (Part " . ($iteration + 1) . ')';
            
            $author = $p === 0 ? $testUser : $allUsers[$p % $allUsers->count()];
            
            $daysAgo = ($p * 30) / $totalPostsCount;
            $hoursAgo = rand(1, 23);
            $createdAt = Carbon::now()->subDays((int)$daysAgo)->subHours($hoursAgo)->subMinutes(rand(0, 59));

            $post = Post::create([
                'user_id' => $author->id,
                'title' => $postTitle,
                'body_markdown' => $template['body'],
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Attach tech tags
            $postTagIds = [];
            foreach ($template['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $postTagIds[] = $tags[$tagName]->id;
                }
            }
            $post->tags()->sync($postTagIds);

            // Attach image if template has one or for 35% of posts
            if ($template['image_idx'] !== null && isset($techImages[$template['image_idx']])) {
                PostImage::create([
                    'post_id' => $post->id,
                    'uploaded_by' => $author->id,
                    'path_webp' => $techImages[$template['image_idx']],
                    'sort_order' => 0,
                    'alt_text' => $template['title'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            } elseif ($p % 3 === 0) {
                $imgUrl = $techImages[$p % count($techImages)];
                PostImage::create([
                    'post_id' => $post->id,
                    'uploaded_by' => $author->id,
                    'path_webp' => $imgUrl,
                    'sort_order' => 0,
                    'alt_text' => 'Architecture snapshot',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $seededPosts[] = $post;
        }

        // 4. Realistic Comments and Threaded Discussions
        $commentSnippets = [
            "Brilliant breakdown! We ran into this exact bottleneck last month during our Black Friday load test.",
            "Great write-up. One caveat with partial indexes in PostgreSQL is that your query WHERE clause data types must strictly match, otherwise the planner falls back to a sequential scan.",
            "Can you share how you handled cache invalidation when concurrent transactions commit simultaneously?",
            "In Laravel 12 you can also use `Benchmark::measure` to get high-precision nanosecond timings for this.",
            "We migrated from Next.js to Inertia Vue last quarter and our team velocity literally doubled. Best decision we made.",
            "Did you experience any memory leaks with Octane workers on long-running WebSocket connections?",
            "Awesome benchmark! Bookmarking this for our upcoming sprint retrospective.",
            "How does this approach compare with Redis sorted sets (ZSET) for high-velocity leaderboard queries?",
            "This Docker multi-stage trick saved us over 40GB of container registry storage per week. Thank you!",
            "Spot on regarding refresh token rotation. Storing access tokens in localStorage is unfortunately still way too common.",
            "Have you considered testing pgvector with the new HNSW indexing parameters in PostgreSQL 16? The recall rate is fantastic.",
            "Clean code, practical examples, and no unnecessary jargon. This is what community engineering posts should be.",
            "We implemented a similar circuit breaker with Guzzle middleware. Saved our services when our SMS provider went down.",
            "Love the CSS subgrid tip! Native CSS has gotten so powerful that we barely need complex utility wrappers for layouts anymore.",
            "Could you elaborate on how you handle database migrations across multi-tenant schemas without downtime?",
        ];

        $replySnippets = [
            "Exactly right! That type-casting nuance in PostgreSQL caught us off-guard initially as well.",
            "We use database transactions with optimistic locking (`lockVersion`) to handle the concurrent updates safely.",
            "Zero memory leaks so far! We set `--max-requests=10000` on Octane workers to automatically recycle workers periodically.",
            "Thanks for reading! Yes, in Part 2 we will cover multi-tenant partitioning strategies with PostgreSQL schema isolation.",
            "Agreed! HNSW index build times are slightly higher, but the p99 search latency under 5ms makes it well worth it.",
            "Glad it helped! Feel free to copy and tweak the Dockerfile template for your setup.",
        ];

        foreach ($seededPosts as $index => $post) {
            $numComments = rand(2, 5);
            for ($c = 0; $c < $numComments; $c++) {
                $commenter = $allUsers->random();
                $commentTime = (clone $post->created_at)->addMinutes(rand(10, 300));
                
                $rootComment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                    'parent_id' => null,
                    'body' => $commentSnippets[($index + $c) % count($commentSnippets)],
                    'depth' => 0,
                    'likes_count' => rand(1, 12),
                    'created_at' => $commentTime,
                    'updated_at' => $commentTime,
                ]);

                // 60% chance of author or other user replying
                if (rand(0, 100) > 40) {
                    $replier = rand(0, 1) === 0 ? $post->user : $allUsers->random();
                    $replyTime = (clone $commentTime)->addMinutes(rand(5, 60));
                    
                    Comment::create([
                        'post_id' => $post->id,
                        'user_id' => $replier->id,
                        'parent_id' => $rootComment->id,
                        'body' => $replySnippets[rand(0, count($replySnippets) - 1)],
                        'depth' => 1,
                        'likes_count' => rand(0, 8),
                        'created_at' => $replyTime,
                        'updated_at' => $replyTime,
                    ]);
                }
            }

            // Update comments_count
            $post->update(['comments_count' => $post->comments()->count()]);
        }

        // 5. Post Likes Distribution
        foreach ($seededPosts as $post) {
            $likeCount = rand(6, 40);
            if (rand(0, 10) > 7) {
                $likeCount = rand(50, 95);
            }

            $likers = $allUsers->random(min($likeCount, $allUsers->count()));
            $likeRecords = [];
            foreach ($likers as $liker) {
                $likeRecords[] = [
                    'post_id' => $post->id,
                    'user_id' => $liker->id,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->created_at,
                ];
            }
            PostLike::insertOrIgnore($likeRecords);
            $post->update(['likes_count' => count($likeRecords)]);
        }

        // 6. Post Shares & Reposts
        $shareQuotes = [
            "Must-read engineering breakdown for anyone running high-concurrency systems.",
            "Sharing with our backend team. Excellent production advice.",
            "This is why I love this community. Real benchmarks, zero fluff.",
            "Bookmarking this architectural pattern for our Q4 refactor.",
            "Crucial insight on database indexing and connection pooling.",
        ];

        foreach ($seededPosts as $post) {
            if (rand(0, 10) > 5) {
                $shareCount = rand(2, 14);
                $sharers = $allUsers->random(min($shareCount, $allUsers->count()));
                $shareRecords = [];
                foreach ($sharers as $sharer) {
                    $shareRecords[] = [
                        'post_id' => $post->id,
                        'user_id' => $sharer->id,
                        'quote_body' => $shareQuotes[rand(0, count($shareQuotes) - 1)],
                        'created_at' => $post->created_at,
                        'updated_at' => $post->created_at,
                    ];
                }
                PostShare::insertOrIgnore($shareRecords);
                $post->update(['shares_count' => count($shareRecords)]);
            }
        }

        // 7. Bookmarks
        foreach ($allUsers as $user) {
            $bookmarkedPosts = collect($seededPosts)->random(rand(2, 8));
            $bookmarkRecords = [];
            foreach ($bookmarkedPosts as $bmPost) {
                $bookmarkRecords[] = [
                    'user_id' => $user->id,
                    'post_id' => $bmPost->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 20)),
                ];
            }
            Bookmark::insertOrIgnore($bookmarkRecords);
        }

        // 8. Follow Graph (700+ follow connections)
        $followRecords = [];
        foreach ($allUsers as $user) {
            $followingUsers = $allUsers->where('id', '!==', $user->id)->random(rand(8, 20));
            foreach ($followingUsers as $target) {
                $followRecords[] = [
                    'follower_id' => $user->id,
                    'following_id' => $target->id,
                    'created_at' => Carbon::now()->subDays(rand(5, 60)),
                    'updated_at' => Carbon::now()->subDays(rand(5, 60)),
                ];
            }
        }
        $otherUsers = $allUsers->where('id', '!==', $testUser->id);
        foreach ($otherUsers->take(25) as $ou) {
            $followRecords[] = [
                'follower_id' => $ou->id,
                'following_id' => $testUser->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ];
            $followRecords[] = [
                'follower_id' => $testUser->id,
                'following_id' => $ou->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ];
        }
        Follow::insertOrIgnore($followRecords);

        // 9. Direct Conversations & Messages
        for ($conv = 0; $conv < 12; $conv++) {
            $userA = $conv === 0 ? $testUser : $allUsers->random();
            $userB = $allUsers->where('id', '!==', $userA->id)->random();

            $conversation = Conversation::create(['type' => 'private']);
            $conversation->participants()->attach([
                $userA->id => ['joined_at' => Carbon::now()->subDays(5)],
                $userB->id => ['joined_at' => Carbon::now()->subDays(5)],
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userA->id,
                'body' => "Hey {$userB->name}! Loved your recent post about distributed system architecture. Quick question about handling dead-letter queues in RabbitMQ?",
                'created_at' => Carbon::now()->subHours(8),
                'updated_at' => Carbon::now()->subHours(8),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userB->id,
                'body' => "Hey {$userA->name}! Thanks for reaching out. We route all unroutable or failed messages to an exchange with `x-dead-letter-exchange` and inspect them with a custom alert consumer.",
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ]);
        }
    }
}
