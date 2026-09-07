<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $dateColumn = $filters['date_column'] ?? 'created_at';
        $searchColumns = $filters['search_columns'] ?? ['name'];

        return $query
            ->when($filters['from'] ?? null, fn (Builder $builder, $from) => $builder->whereDate($dateColumn, '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $builder, $to) => $builder->whereDate($dateColumn, '<=', $to))
            ->when($filters['status'] ?? null, fn (Builder $builder, $status) => $builder->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $builder, $search) use ($searchColumns): void {
                $builder->where(function (Builder $searchQuery) use ($search, $searchColumns): void {
                    foreach ($searchColumns as $column) {
                        $searchQuery->orWhere($column, 'like', '%' . $search . '%');
                    }
                });
            });
    }
}