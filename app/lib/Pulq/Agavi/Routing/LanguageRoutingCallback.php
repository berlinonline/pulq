<?php

namespace Pulq\Agavi\Routing;

/**
 * The ProjectLanguageRoutingCallbacck response to locale information
 * matched inside a url and applies the corresponding settings to our agavi env.
 * It is also responseable for correctly providing i18n data for url generation.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class LanguageRoutingCallback extends \AgaviRoutingCallback
{
    /**
     * An array containing locales that are available to use.
     *
     * @var         array
     */
    protected $availableLocales = array();

    /**
     * Initialize this ProjectLanguageRoutingCallback instance.
     *
     * @param       AgaviContext $context
     *
     * @param       array $route
     */
    public function initialize(\AgaviContext $context, array &$route)
    {
        parent::initialize($context, $route);

        // reduce method calls
        $this->translationManager = $this->context->getTranslationManager();

        // store the available locales, that's faster
        $this->availableLocales = $this->context->getTranslationManager()->getAvailableLocales();
    }

    /**
     * Routing callback that is invoked when the root we are applied to matches (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @codingStandardsIgnoreStart
     */
    public function onMatched(array &$parameters, \AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        // let's check if the locale is allowed
        try
        {
            $this->context->getTranslationManager()->getLocaleIdentifier($parameters['locale']);
            // yup, worked. now lets set that as a cookie
            $this->context->getController()->getGlobalResponse()->setCookie(
                'locale',
                $parameters['locale'],
                '+1 month'
            );

            return TRUE;
        }
        catch (\AgaviException $e)
        {
            // uregistered or ambigious locale... uncool!
            // onNotMatched will be called for us next
            return FALSE;
        }
    }

    /**
     * Routing callback that is invoked when the root we are applied to does not match (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @codingStandardsIgnoreStart
     */
    public function onNotMatched(\AgaviExecutionContainer $container) // @codingStandardsIgnoreEnd
    {
        // the pattern didn't match, or onMatched() returned FALSE.
        // that's sad. let's see if there's a locale set in a cookie from an earlier visit.
        $requestData = $this->context->getRequest()->getRequestData();

        $cookie = $requestData->getCookie('locale');

        if ($cookie !== NULL)
        {
            try
            {
                $this->translationManager->setLocale($cookie);

                return;
            }
            catch (\AgaviException $e)
            {
                // bad cookie :<
                $this->context->getController()->getGlobalResponse()->unsetCookie('locale');
            }
        }

        if ($requestData->hasHeader('Accept-Language'))
        {
            $hasIntl = function_exists('locale_accept_from_http');
            // try to find the best match for the locale
            $locales = self::parseAcceptLanguage($requestData->getHeader('Accept-Language'));

            foreach ($locales as $locale)
            {
                try
                {
                    if ($hasIntl)
                    {
                        // we don't use this directly on Accept-Language,
                        // because we might not have the preferred locale,
                        // but another one in any case, it might help clean up the value a bit further
                        $locale = locale_accept_from_http($locale);
                    }

                    $this->translationManager->setLocale($locale);

                    return;
                }
                catch (\AgaviException $e)
                {
                    return;
                }
            }
        }
    }

    /**
     * Routing callback that is invoked when the root we are applied to does not match (routing runtime).
     *
     * @param       array $parameters
     * @param       AgaviExecutionContainer $container
     *
     * @return      boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function onGenerate(array $defaultParameters, array &$userParameters, array &$options) //@codingStandardsIgnoreEnd
    {
        if (isset($userParameters['locale']))
        {
            $userParameters['locale'] = $this->getShortestLocaleIdentifier(
                $userParameters['locale']
            );
        }
        else
        {
            $userParameters['locale'] = $this->getShortestLocaleIdentifier(
                $this->translationManager->getCurrentLocaleIdentifier()
            );
        }

        return TRUE;
    }

    /**
     * Resolve a given locale identifier to its corresponding short identifier.
     *
     * @staticvar   string $localeMap
     *
     * @param       string $localeIdentifier
     *
     * @return      string
     */
    public function getShortestLocaleIdentifier($localeIdentifier)
    {
        static $localeMap = NULL;

        if ($localeMap === NULL)
        {
            foreach ($this->availableLocales as $locale)
            {
                $localeMap[$locale['identifierData']['language']][] = $locale['identifierData']['territory'];
            }
        }

        if (count($localeMap[$short = substr($localeIdentifier, 0, 2)]) > 1)
        {
            return $localeIdentifier;
        }
        else
        {
            return $short;
        }
    }

    /**
     * Parses the value of a http accept-language header into an array of identifiers.
     *
     * @param       string $acceptLanguage
     *
     * @return      array
     */
    protected static function parseAcceptLanguage($acceptLanguage)
    {
        $locales = array();

        $matchCount = preg_match_all(
            '/(^|\s*,\s*)([a-zA-Z]{1,8}(-[a-zA-Z]{1,8})*)\s*(;\s*q\s*=\s*(1(\.0{0,3})?|0(\.[0-9]{0,3})))?/i',
            $acceptLanguage,
            $matches
        );

        if ($matchCount)
        {
            foreach ($matches[2] as &$language)
            {
                $language = str_replace('-', '_', $language);
            }

            foreach ($matches[5] as &$quality)
            {
                if ($quality === '')
                {
                    $quality = '1';
                }
            }

            $locales = array_combine($matches[2], $matches[5]);
            arsort($locales, SORT_NUMERIC);
        }

        return array_keys($locales);
    }
}
