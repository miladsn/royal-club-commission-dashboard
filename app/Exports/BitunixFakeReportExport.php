<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BitunixFakeReportExport implements FromArray, WithHeadings
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return ['uid', 'trade_volume', 'commission', 'date'];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $row) {
            $data[] = [
                'uid'          => $row['uid'] ?? '',
                'trade_volume' => $row['trade_volume'] ?? 0,
                'commission'   => $row['commission'] ?? 0,
                'date'         => $row['date'] ?? now()->toDateTimeString(),
            ];
        }
        return $data;
    }
}
