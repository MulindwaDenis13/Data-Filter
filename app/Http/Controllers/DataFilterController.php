<?php

namespace App\Http\Controllers;

use App\Imports\CashBookImport;
use App\Imports\BankStatementImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CashBookExport;
use App\Exports\BankStatementExport;

use Illuminate\Http\Request;

class DataFilterController extends Controller
{
    protected $cash_book_path;
    protected $bank_statement_path;
    public function __construct()
    {
        $this->cash_book_path = storage_path('app/public/cashbook.json');
        $this->bank_statement_path = storage_path('app/public/bank_statement.json');
    }

    public function create_json_file($path)
    {
        touch($path);
    }

    public function handle_import($path, $file, $import)
    {
        //set file to zero bytes
        fclose(fopen($path, 'w'));

        file_put_contents($path, json_encode(Excel::toArray($import, $file)[0]));

    }
    public function import_bank_statement(Request $request)
    {
        try {

            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $this->handle_import($this->bank_statement_path, request()->file('import_file'), new BankStatementImport);

            return response()->json(['message' => true]);

        } catch (\Throwable $th) {
            return response()->json(['message' => false, 'error' => $th->getMessage()]);
        }
    }

    public function import_cash_book(Request $request)
    {

        try {

            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $this->handle_import($this->cash_book_path, request()->file('import_file'), new CashBookImport);

            return response()->json(['message' => true]);

        } catch (\Throwable $th) {
            return response()->json(['message' => false, 'error' => $th->getMessage()]);
        }
    }

    public function attach_new_object(object $transaction, string $exists): object
    {
        $new_object = (object) [];
        $new_object->amount = $transaction->amount ?? $transaction->money_in;
        $new_object->name = $transaction->name ?? $transaction->narration;
        $new_object->balance = $transaction->balance;
        $new_object->date = $transaction->date;
        $new_object->exists_in = $exists;
        $new_object->reference_no = $transaction->no ?? $transaction->reference_no;
        return $new_object;
    }

    public function loop_transactions(array $bank_statements, array $cash_books): array
    {
        $new_transactions_filter = array();

        foreach ($bank_statements as $bank_statement) {
            $check_cash_book = array_reduce($cash_books, static function ($next, $item) use ($bank_statement) {
                return $next ?? ($item->no == $bank_statement->reference_no) ? $item : $next;
            }, null);
            if (is_null($check_cash_book)) {
                array_push($new_transactions_filter, $this->attach_new_object($bank_statement, 'Bank Statement'));
            }
        }

        foreach ($cash_books as $cash_book) {
            if (!is_null($cash_book->no) || $cash_book->no !== "") {
                $check_bank_statement_with_ref = array_reduce($bank_statements, static function ($next, $item) use ($cash_book) {
                    return $next ?? ($item->reference_no == $cash_book->no ? $item : $next);
                }, null);
                if (is_null($check_bank_statement_with_ref)) {
                    array_push($new_transactions_filter, $this->attach_new_object($cash_book, 'Cash Book'));
                }
            } else {
                $name_checks = array();
                $split_name = explode(' ', $cash_book->name);
                $check_bank_statements_without_ref = array_filter($bank_statements, static function ($item) use ($cash_book) {
                    return $item->date === $cash_book->date && $item->money_in === $cash_book->amount;
                });
                foreach ($split_name as $split) {
                    $contains_name = array_reduce($check_bank_statements_without_ref, static function ($next, $item) use ($split) {
                        return $next ?? (str_contains($item->narration, $split)) ? $item : $next;
                    }, null);
                    if (!is_null($contains_name)) {
                        array_push($name_checks, $contains_name);
                    }
                }
                if (count($name_checks) <= 0) {
                    array_push($new_transactions_filter, $this->attach_new_object($cash_book, 'Cash Book'));
                }
                unset($name_checks);
            }
        }

        return $new_transactions_filter;
    }

    public function get_json_content($path)
    {
        return json_decode(file_get_contents($path));
    }


    public function all_data(array $paths): array
    {
        foreach ($paths as $path) {
            if (!file_exists($path))
                $this->create_json_file($path);
        }
        $cash_books = $this->get_json_content($paths[0]) ?? [];
        $bank_statements = $this->get_json_content($paths[1]) ?? [];
        $all_filter = array_merge($this->loop_transactions($bank_statements, $cash_books));
        shuffle($all_filter);
        $response = [
            'cashbook' => $cash_books,
            'bank_statements' => $bank_statements,
            'all_filter' => $all_filter
        ];
        return $response;
    }

    public function display()
    {
        try {
            $data = $this->all_data([$this->cash_book_path, $this->bank_statement_path]);
            return view('index', compact('data'))->with('i');
        } catch (\Throwable $th) {
            return response()->json(['message' => false, 'error' => $th->getMessage()]);
        }
    }

    public function export_data($value)
    {
        try {
            $data = $this->all_data([$this->cash_book_path, $this->bank_statement_path]);

            if ($value == 'bank') {
                $filter_cash = $this->filter_support($data['all_filter'], 'Cash Book');
                $bank_statements = $data['bank_statements'];
                if (count($bank_statements) > 0) {
                    foreach ($filter_cash as $filter) {
                        $return_object = (object) [];
                        $return_object->narration = $filter->name;
                        $return_object->date = $filter->date;
                        $return_object->reference_no = $filter->reference_no;
                        $return_object->money_in = $filter->amount;
                        $return_object->money_out = $filter->amount;
                        $return_object->balance = $filter->balance;
                        $bank_statements[] = $return_object;
                    }
                }
                return Excel::download(new BankStatementExport($bank_statements), 'bank_statements.xlsx');
            }

            if ($value == 'cash') {
                $filter_bank = $this->filter_support($data['all_filter'], 'Bank Statement');
                $cashbook = $data['cashbook'];
                if (count($cashbook) > 0) {
                    foreach ($filter_bank as $filter) {
                        $return_object = (object) [];
                        $return_object->name = $filter->name;
                        $return_object->date = $filter->date;
                        $return_object->no = $filter->no;
                        $return_object->amount = $filter->amount;
                        $return_object->balance = $filter->balance;
                        $return_object->account = $filter->account;
                        $return_object->split = $filter->split;
                        $return_object->description = $filter->description;
                        $return_object->transaction_type = $filter->transaction_type;
                        $cashbook[] = $return_object;
                    }
                }
                return Excel::download(new CashBookExport($cashbook), 'cashbook.xlsx');
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => false, 'error' => $th->getMessage()]);
        }

    }

    public function filter_support(array $data, string $result)
    {
        $return_data = array_filter($data, static function ($item) use ($result) {
            return $item->exists_in === $result;
        });
        return $return_data;
    }
}

# https://stackoverflow.com/questions/21952723/how-to-install-composer-on-a-mac
// https://stitcher.io/blog/php-82-upgrade-mac
// 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
// https://extendsclass.com/csv-generator.html