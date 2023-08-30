<?php

namespace Waavi\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator {

    /**
     * Create a new translator instance.
     *
     * @param  \Illuminate\Contracts\Translation\Loader  $loader
     * @param  string  $locale
     * @return void
     */
    public function __construct(Loader $loader, $locale)
    {
        parent::__construct($loader, $locale);
    }


    /**
     * Get the translation for the given key.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string|null  $locale
     * @param  bool  $fallback
     * @return string|array|null
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        list($namespace, $group, $item) = $this->parseKey($key);

        $locales = $this->localeArray($locale);
        if ($fallback) {
            $locales[] = $this->fallback;
        }

        foreach ($locales as $locale) {
            if (! is_null($line = $this->getLine(
                $namespace, $group, $locale, $item, $replace
            ))) {
                break;
            }
        }

        // If the line doesn't exist, we will return back the key which was requested as
        // that will be quick to spot in the UI if language keys are wrong or missing
        // from the application's language files. Otherwise we can return the line.
        if (isset($line)) {
            return $line;
        }

        return $key;
    }

    /**
     * Get the array of locales to be checked.
     *
     * @param  string|null  $locale
     * @return array
     */
    private function localeArray($locale)
    {
        $locales = [$locale ?: $this->locale];

        $parentLocale = $this->getParentLocale($locale);
        if ($parentLocale) {
            $locales[] = $parentLocale;
        }

        return array_filter($locales);
    }

    private function getParentLocale($locale)
    {
        $separator = strpos($locale, '_');
        if ($separator === false) {
            return null;
        }

        return substr($locale, 0, $separator);
    }
}
