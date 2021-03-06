<?php

namespace Massive\Component\Web;

use Symfony\Component\Intl\Intl;

/**
 * This Twig Extension manages the image formats.
 */
class IntlTwigExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('intl_countries', [$this, 'getCountries']),
            new \Twig_SimpleFunction('intl_country', [$this, 'getCountry']),
            new \Twig_SimpleFunction('intl_locales', [$this, 'getLocales']),
            new \Twig_SimpleFunction('intl_locale', [$this, 'getLocale']),
            new \Twig_SimpleFunction('intl_languages', [$this, 'getLanguages']),
            new \Twig_SimpleFunction('intl_language', [$this, 'getLanguage']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('intl_icu_locale', [$this, 'getIcuLocale']),
        ];
    }

    /**
     * Get icu locale from sulu locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getIcuLocale($locale)
    {
        $parts = explode('-', $locale);
        if (isset($parts[1])) {
            $parts[1] = strtoupper($parts[1]);
        }

        return implode('_', $parts);
    }

    /**
     * Get countries.
     *
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getCountries($displayLocale = null)
    {
        return Intl::getRegionBundle()->getCountryNames($displayLocale);
    }

    /**
     * Get country.
     *
     * @param string $country
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getCountry($country, $displayLocale = null)
    {
        return Intl::getRegionBundle()->getCountryName(strtoupper($country), $displayLocale);
    }

    /**
     * Get languages.
     *
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getLanguages($displayLocale = null)
    {
        return Intl::getLanguageBundle()->getLanguageNames($displayLocale);
    }

    /**
     * Get language.
     *
     * @param string $language
     * @param string|null $region
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getLanguage($language, $region = null, $displayLocale = null)
    {
        return Intl::getLanguageBundle()->getLanguageName($language, $region, $displayLocale);
    }

    /**
     * Get locales.
     *
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getLocales($displayLocale = null)
    {
        return Intl::getLocaleBundle()->getLocaleNames($displayLocale);
    }

    /**
     * Get locale.
     *
     * @param string $locale
     * @param string|null $displayLocale
     *
     * @return string
     */
    public function getLocale($locale, $displayLocale = null)
    {
        return Intl::getLocaleBundle()->getLocaleName($locale, $displayLocale);
    }
}
