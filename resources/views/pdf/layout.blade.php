<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        header { border-bottom: 1px solid #d1d5db; padding-bottom: 12px; }
        footer { border-top: 1px solid #d1d5db; margin-top: 24px; padding-top: 8px; color: #6b7280; }
    </style>
</head>
<body>
    <header><strong>{{ config('app.name') }}</strong><br>{{ config('app.url') }}</header>
    <main>@yield('content')</main>
    <footer>Generated {{ format_datetime(now()) }} · Page <span class="pageNumber"></span></footer>
</body>
</html>