<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('shares_count');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->text('body')->nullable()->change();
            if (!Schema::hasColumn('messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('body');
            }
            if (!Schema::hasColumn('messages', 'attachment_type')) {
                $table->string('attachment_type')->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('messages', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_type');
            }
            if (!Schema::hasColumn('messages', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'views_count')) {
                $table->dropColumn('views_count');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('messages', 'attachment_path') ? 'attachment_path' : null,
                Schema::hasColumn('messages', 'attachment_type') ? 'attachment_type' : null,
                Schema::hasColumn('messages', 'attachment_name') ? 'attachment_name' : null,
                Schema::hasColumn('messages', 'attachment_size') ? 'attachment_size' : null,
            ]));
        });
    }
};
