<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BankStatementExport implements FromCollection, withHeadings, withMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ['date', 'narration', 'reference_no', 'money_in', 'money_out', 'balance'];
    }

    public function map($bank_statement): array
    {
        return [
            $bank_statement->date,
            $bank_statement->narration,
            $bank_statement->money_in,
            $bank_statement->money_out,
            $bank_statement->balance
        ];
    }
}