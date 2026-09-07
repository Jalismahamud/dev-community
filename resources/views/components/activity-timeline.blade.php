@foreach ($activities as $activity)
    <article>
        <strong>{{ $activity->description }}</strong>
        <time datetime="{{ $activity->created_at?->toIso8601String() }}">
            {{ format_datetime($activity->created_at) }}
        </time>
        @if ($activity->properties->isNotEmpty())
            <pre>{{ $activity->properties->toJson(JSON_PRETTY_PRINT) }}</pre>
        @endif
    </article>
@endforeach