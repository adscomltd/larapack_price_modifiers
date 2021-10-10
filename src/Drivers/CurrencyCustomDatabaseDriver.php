<?php

namespace Adscom\LarapackPriceModifiers\Drivers;

use Adscom\LarapackPriceModifiers\Models\Currency;
use DateTime;
use Torann\Currency\Drivers\Database;

class CurrencyCustomDatabaseDriver extends Database {
  /**
   * {@inheritdoc}
   */
  public function create(array $params): bool|string
  {
    // Ensure the currency doesn't already exist
    if ($this->find($params['code'], null) !== null) {
      return 'exists';
    }

    // Created at stamp
    $created = new DateTime('now');

    $params = array_merge([
      'name' => '',
      'code' => '',
      'symbol' => '',
      'format' => '',
      'exchange_rate' => 1,
      'active' => 0,
    ], $params);

    return Currency::create($params);
  }

  /**
   * Override for using Model and events
   */
  public function update($code, array $attributes, DateTime $timestamp = null)
  {
    $model = Currency::query()
      ->firstWhere('code', strtoupper($code));

    if ($code === 'EUR') {
      $attributes['exchange_rate'] = 1;
    }

    return $model?->update($attributes);
  }
}

