<?php

namespace App\Imports;

use App\Models\CashBook;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CashBookImport implements ToCollection, WithBatchInserts, WithChunkReading, WithUpserts, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
    public function uniqueBy()
    {
        return '';
    }
    public function chunkSize(): int
    {
        return 8000;
    }

    public function batchSize(): int
    {
        return 8000;
    }
}