<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;

/**
 * Prints step.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlStepPrinter implements StepPrinter
{
    /**
     * @var HtmlPrinter
     */
    private $htmlPrinter;

    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

    /**
     * Initializes printer.
     *
     * @param HtmlPrinter $htmlPrinter
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(
        HtmlPrinter $htmlPrinter,
        ExceptionPresenter $exceptionPresenter
    ) {
        $this->setHtmlPrinter($htmlPrinter);
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
        if (!$result instanceof ExceptionResult || !$result->hasException()) {
            $error = '';
        } else {
            $error = $this->exceptionPresenter->presentException($result->getException());
        }

        $this->getHtmlPrinter($formatter->getOutputPrinter())->openStep($step, $result, $error);
    }

    /**
     * @param OutputPrinter $printer
     * @return HtmlPrinter
     */
    protected function getHtmlPrinter(OutputPrinter $printer)
    {
        $this->htmlPrinter->setOutputPrinter($printer);
        return $this->htmlPrinter;
    }

    /**
     * @param HtmlPrinter $htmlPrinter
     * @return $this
     */
    protected function setHtmlPrinter(HtmlPrinter $htmlPrinter)
    {
        $this->htmlPrinter = $htmlPrinter;
        return $this;
    }
}
