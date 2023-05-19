<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;

    protected $table = 'bank_statement';

    protected $fillable = [
        'date',
        'narration',
        'amount',
        'money_in',
        'money_out',
        'balance',
        'reference_no',
    ];
}