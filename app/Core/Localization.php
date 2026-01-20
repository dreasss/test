<?php

namespace App\Core;

class Localization
{
    private array $strings;
    private string $locale;

    public function __construct(string $locale, array $strings)
    {
        $this->locale = $locale;
        $this->strings = $strings;
    }

    public function get(string $key): string
    {
        return $this->strings[$this->locale][$key] ?? $key;
    }

    public function locale(): string
    {
        return $this->locale;
    }
}
