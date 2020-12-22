<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    public function customer()
    {
        return $this->belongsTo('App\Customers');
    }

    public function orderitem()
    {
        return $this->hasOne('App\OrderItems', 'order_id');
    }

    public function payment()
    {
        return $this->hasOne('App\Transactions', 'foreign_key', 'order_id');
    }
}
