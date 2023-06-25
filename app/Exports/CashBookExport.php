<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashBookExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ['date', 'transaction_type', 'description', 'account', 'no', 'split', 'amount', 'balance'];
    }

    public function map($cash_book): array
    {
        return [
            $cash_book->date,
            $cash_book->transaction_type,
            $cash_book->description,
            $cash_book->account,
            $cash_book->no,
            $cash_book->split,
            $cash_book->amount,
            $cash_book->balance
        ];
    }
}