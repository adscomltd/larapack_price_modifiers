<?php

namespace Adscom\LarapackPriceModifiers\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
  protected $fillable = [
    'name',
    'code',
    'symbol',
    'format',
    'exchange_rate',
    'active',
  ];

  public static function booted()
  {
    self::creating(function(self $model) {
      $model->exchange_rate = rounded($model->exchange_rate);
    });

    self::updating(function(self $model) {
      $model->exchange_rate = rounded($model->exchange_rate);
    });
  }
}
