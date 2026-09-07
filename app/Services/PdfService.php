<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class PdfService
{
    public function generate(string $view, array $data, string $filename): PdfDocument
    {
        return Pdf::loadView($view, $data)->setPaper('a4')->setWarnings(false);
    }
}