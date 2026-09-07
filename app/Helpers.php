<?php

use Carbon\CarbonInterface;

if (! function_exists('format_datetime')) {
    function format_datetime(CarbonInterface|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $value = $date instanceof CarbonInterface ? $date : Carbon\Carbon::parse($date);

        return $value->formatDhaka();
    }
}

if (! function_exists('format_money')) {
    function format_money(float|int|string|null $amount): string
    {
        $formatted = number_format((float) ($amount ?? 0), 2, '.', ',');
        [$integer, $decimal] = explode('.', $formatted);
        $integer = str_replace(',', '', $integer);
        $lastThree = substr($integer, -3);
        $remaining = substr($integer, 0, -3);

        if ($remaining !== '') {
            $lastThree = ',' . $lastThree;
            $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        }

        return '৳ ' . ($remaining . $lastThree) . '.' . $decimal;
    }
}