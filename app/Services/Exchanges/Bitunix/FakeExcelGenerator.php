<?php

namespace App\Services\Exchanges\Bitunix;

use App\Services\FakeExchangePayloadGenerator;
use App\Exports\BitunixFakeReportExport;
use Maatwebsite\Excel\Facades\Excel;

class FakeExcelGenerator
{
    /**
     * Generate a mock Bitunix Excel file and return its path.
     */
    public function generate(int $userCount = 15, int $reportCount = 30, ?string $startDate = null, ?string $endDate = null): string
    {
        $fake = (new FakeExchangePayloadGenerator())
            ->generate('bitunix', $userCount, $reportCount, $startDate, $endDate);

        $rows = $fake['payload']['commissions'] ?? [];

        $fileName = 'bitunix-fake-' . now()->format('Ymd_His') . '.xlsx';
        $path = 'excel/fake/' . $fileName; // storage/app/excel/fake

        Excel::store(new BitunixFakeReportExport($rows), $path);

        return $path;
    }
}
