<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Withdraw extends Model {

  protected $connection = 'mongodb';
  protected $collection = 'withdraws';

  protected $fillable = [
    'user', 'sum', 'type', 'currency', 'address', 'status', 'decline_reason', 'sum_original'
  ];

}
