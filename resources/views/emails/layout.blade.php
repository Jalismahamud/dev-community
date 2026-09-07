<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body { margin: 0; background: #f4f6f8; color: #1f2937; font-family: Arial, sans-serif; }
        .email { max-width: 640px; margin: 32px auto; background: #ffffff; padding: 32px; }
        .header { border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; font-size: 20px; font-weight: 700; }
        .footer { border-top: 1px solid #e5e7eb; margin-top: 28px; padding-top: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="email">
        <header class="header">{{ config('app.name') }}</header>
        <main>@yield('content')</main>
        <footer class="footer">{{ config('app.name') }} · Company information placeholder · Unsubscribe placeholder</footer>
    </div>
</body>
</html>