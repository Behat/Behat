<?php

namespace Behat\Behat\Output\Formatter;

use Symfony\Component\Translation\TranslatorInterface;

abstract class TranslatableConsoleFormatter extends ConsoleFormatter
{
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    /**
     * Translates message to output language.
     *
     * @param string $message    message to translate
     * @param array  $parameters message parameters
     *
     * @return string
     */
    final protected function translate($message, array $parameters = array())
    {
        return $this->translator->trans(
            $message, $parameters, 'formatter', $this->getParameter('language')
        );
    }

    /**
     * Translates numbered message to output language.
     *
     * @param string $message    message specification to translate
     * @param string $number     choice number
     * @param array  $parameters message parameters
     *
     * @return string
     */
    final protected function translateChoice($message, $number, array $parameters = array())
    {
        return $this->translator->transChoice(
            $message, $number, $parameters, 'formatter', $this->getParameter('language')
        );
    }

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        $defaultLanguage = null;
        if (($locale = getenv('LANG')) && preg_match('/^([a-z]{2})/', $locale, $matches)) {
            $defaultLanguage = $matches[1];
        }

        return array(
            'language' => $defaultLanguage,
        );
    }
}
