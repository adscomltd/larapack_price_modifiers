<?php

namespace Adscom\LarapackPriceModifiers\Traits;

trait SupportsPriceModifierRules
{
  public static function getRuleTypes(): array
  {
    return [
      'sum',
      'multiply',
      'round',
    ];
  }

  public static function getDefaultRule(): string
  {
    return 'multiply';
  }

  public function getModifiers(): array
  {
    $modifiers = $this->modifiers;

    if (!$modifiers) {
      return [];
    }

    if (!is_array($modifiers) && is_numeric($modifiers)) {
      return [
        'type' => self::getDefaultRule(),
        'value' => $modifiers,
      ];
    }

    if (!is_array($modifiers)) {
      return [];
    }

    return $modifiers;
  }

  public static function getAlterRules(): array
  {
    return [
      'sum' => fn($price, $value) => $price + $value,
      'multiply' => fn($price, $value) => $price * $value,
      'round' => fn($price, $value) => ceil($price / $value) * $value,
    ];
  }

  public static function getRevertRules(): array
  {
    return [
      'sum' => fn($price, $value) => $price - $value,
      'multiply' => fn($price, $value) => $price / $value,
      'round' => fn($price, $value) => floor($price / $value) * $value,
    ];
  }

  public function alterPrice(float $original): float
  {
    $price = $original;
    $rules = static::getAlterRules();

    foreach ($this->getModifiers() as ['type' => $type, 'value' => $value]) {
      $price = $rules[$type]($price, $value);
    }

    return rounded($price);
  }

  public function revertPrice(float $price): float
  {
    $original = $price;
    $rules = static::getRevertRules();

    foreach (array_reverse($this->getModifiers()) as ['type' => $type, 'value' => $value]) {
      $original = $rules[$type]($original, $value);
    }

    return $original;
  }
}
