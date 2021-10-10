<?php

namespace Adscom\LarapackPriceModifiers;

use Adscom\LarapackPriceModifiers\Models\Currency;
use Adscom\LarapackPriceModifiers\Traits\SupportsPriceModifierPriorities;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class PriceManager
{
  protected array $columns = [];
  protected Model $priceModifier;
  protected bool $isModifierSet = false;
  protected Currency $currency;

  abstract public function __construct();

  public static function orderBy(Builder $builder, string $column, string $direction = 'desc'): Builder
  {
    if (config('database.default') === 'mysql') {
      $builder->orderBy($column, $direction);
    } else {
      $builder->orderByRaw("{$column} {$direction} NULLS LAST");
    }

    return $builder;
  }

  /**
   * @return Currency
   */
  public function getCurrency(): Currency
  {
    return $this->currency;
  }

  /**
   * @param  Currency  $currency
   */
  public function setCurrency(Currency $currency): void
  {
    $this->isModifierSet = false;

    $this->currency = $currency;
  }

  public function getRate(): float
  {
    return $this->currency->exchange_rate;
  }

  public function getColumn(string $key): mixed
  {
    return $this->columns[$key] ?? null;
  }

  public function setColumn(string $key, $value): void
  {
    $this->isModifierSet = false;
    $this->columns[$key] = $value;
  }

  /**
   * With applied modifier
   * @param  float  $original
   * @return float
   */
  public function alterPrice(float $original): float
  {
    return $this->getPriceModifier()->alterPrice($original);
  }

  /**
   * Extract modifier from alterPrice
   * @param  float  $price
   * @return float
   */
  public function revertPrice(float $price): float
  {
    return $this->getPriceModifier()->revertPrice($price);
  }

  public function format(float $value): string
  {
    return currency()->format($value, $this->currency->code, true);
  }

  abstract protected function getPriceModifierModelClass(): string;

  protected function getPriceModifierDefaultBuilder(): Builder
  {
    /** @var SupportsPriceModifierPriorities $modelClass * */
    $modelClass = $this->getPriceModifierModelClass();

    /** @var Builder $builder */
    $builder = $modelClass::query();

    foreach ($modelClass::getPriorityColumns() as $column) {
      $builder->where(fn($q) => $q
        ->when($this->getColumn($column), fn($q) => $q->where($column, $this->getColumn($column)))
        ->orWhere(fn($q) => $q->whereNull($column)));
    }

    return $builder;
  }

  protected function getPriceModifiersBuilder(): Builder
  {
    /** @var SupportsPriceModifierPriorities $modelClass * */
    $modelClass = $this->getPriceModifierModelClass();

    $builder = $this->getPriceModifierDefaultBuilder();

    foreach ($modelClass::getPriorityColumnsOrdering() as $column => $direction) {
      static::orderBy($builder, $column, $direction);
    }

    return $builder;
  }

  protected function setupPriceModifier(): void
  {
    $this->priceModifier = $this->getPriceModifiersBuilder()->first() ?? new ($this->getPriceModifierModelClass());
  }

  public function getPriceModifier(): Model
  {
    if ($this->isModifierSet) {
      return $this->priceModifier;
    }

    $this->setupPriceModifier();

    $this->isModifierSet = true;

    return $this->priceModifier;
  }
}
