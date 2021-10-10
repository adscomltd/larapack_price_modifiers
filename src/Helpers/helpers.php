<?php

if (!function_exists('rate')) {
  /**
   * Returns rate of currency
   * @param  string  $from  Currency to calculate from currency
   * @param  string  $to  Currency to calculate to currency
   * @return float        Rate or currency or error
   */
  function rate(string $from, string $to): float
  {
    $rate = (float) currency(1, $from, $to, false);

    return $rate > 0 ? $rate : 1.00;
  }
}

if (!function_exists('rounded')) {
  function rounded($value)
  {
    return round($value, (int) config('market.price_round_to'));
  }
}
