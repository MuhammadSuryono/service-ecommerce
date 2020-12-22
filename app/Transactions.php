<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $table = 'transactions';

    public function order()
    {
        return $this->belongsTo('App\Orders');
    }
}
