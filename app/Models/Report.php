<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Traits\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory, TracksActivity;

    protected $fillable = ['reporter_id', 'post_id', 'comment_id', 'reason', 'status', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['status' => ReportStatus::class, 'reviewed_at' => 'datetime'];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}