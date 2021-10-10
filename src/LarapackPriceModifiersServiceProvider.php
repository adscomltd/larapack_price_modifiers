<?php

namespace Adscom\LarapackPriceModifiers;

use Illuminate\Support\ServiceProvider;

class LarapackPriceModifiersServiceProvider extends ServiceProvider
{
  public function boot(): void
  {
    if ($this->app->runningInConsole()) {

      $this->publishes([
        __DIR__.'/../config/currency.php' => config_path('currency.php'),
      ], 'config');

       // Export the migration
      if (! class_exists('CreateCurrencyTable')) {
        $this->publishes([
          __DIR__ . '/../database/migrations/create_currency_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_currency_table.php'),
          // you can add any number of migrations here
        ], 'migrations');
      }
    }
  }
}
