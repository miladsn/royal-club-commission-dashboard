<?php

namespace App\Services\Exchanges\Bitunix;

use App\Services\Exchanges\BaseExchangeProvider;
use App\Services\Exchanges\Contracts\ExcelImportableExchangeInterface;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Facades\Storage;

class BitunixProvider extends BaseExchangeProvider implements ExcelImportableExchangeInterface
{
    private const EXPECTED_HEADINGS = ['uid', 'trade_volume', 'commission', 'date'];

    public function __construct()
    {
        parent::__construct('bitunix');
    }

    public function parseExcel(string $filePath, array $options = []): array
    {
        $absolute = $filePath;

        // Check the path directly on the filesystem or in the storage directory
        if (!file_exists($absolute)) {
            if (Storage::exists($filePath)) {
                $absolute = Storage::path($filePath);
            } elseif (file_exists(storage_path('app/' . ltrim($filePath, '/')))) {
                $absolute = storage_path('app/' . ltrim($filePath, '/'));
            } else {
                throw new \RuntimeException("فایل اکسل یافت نشد: {$filePath}");
            }
        }

        // Validate headers precisely
        $headings = (new HeadingRowImport)->toArray($absolute)[0][0] ?? [];
        $cleanedHeadings = array_map(fn($h) => strtolower(trim((string)$h)), $headings);

        if ($cleanedHeadings !== self::EXPECTED_HEADINGS) {
            throw new \InvalidArgumentException(
                'فرمت اکسل معتبر نیست. هدرهای مورد انتظار: ' . implode(', ', self::EXPECTED_HEADINGS)
            );
        }

        // Read data using an empty class to prevent the No ReaderType error
        $sheets = Excel::toArray(new class {}, $absolute);
        $rows = $sheets[0] ?? [];

        array_shift($rows);

        // Convert indexed Excel rows to arrays keyed as required by the adapter
        $mappedRows = [];
        foreach ($rows as $row) {
            if (empty($row) || !isset($row[0])) {
                continue;
            }
            $mappedRows[] = [
                'uid'          => (string)$row[0],
                'trade_volume' => $row[1] ?? 0,
                'commission'   => $row[2] ?? 0,
                'date'         => $row[3] ?? now()->toDateTimeString(),
            ];
        }

        // Convert data to the standard format using the adapter
        return BitunixAdapter::transform($mappedRows);
    }
}
