<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Testwork abstract translatable CLI formatter.
 *
 * In addition to CLI functionality this formatter provides i18n tools.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class TranslatableCliFormatter extends CliFormatter
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes formatter.
     *
     * @param OutputPrinter       $printer
     * @param ExceptionPresenter  $exceptionPresenter
     * @param TranslatorInterface $translator
     */
    public function __construct(
        OutputPrinter $printer,
        ExceptionPresenter $exceptionPresenter,
        TranslatorInterface $translator
    ) {
        parent::__construct($printer, $exceptionPresenter);

        $this->translator = $translator;
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
        return $this->translator->trans($message, $parameters, 'output');
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
        return $this->translator->transChoice($message, $number, $parameters, 'output');
    }
}
