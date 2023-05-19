<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBook extends Model
{
    use HasFactory;

    protected $table = 'cash_book';

    protected $fillable = [
        'date',
        'transaction_type',
        'reference_no',
        'description',
        'name',
        'account',
        'split',
        'amount',
        'balance',
    ];
}