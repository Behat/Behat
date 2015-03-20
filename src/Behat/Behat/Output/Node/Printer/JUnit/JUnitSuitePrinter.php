<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Output\Node\Printer\SuitePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Suite\Suite;

/**
 * Creates new JUnit report file.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitSuitePrinter implements SuitePrinter
{
    /**
     * @var PhaseStatistics
     */
    private $statistics;

    public function __construct(PhaseStatistics $statistics = null)
    {
        $this->statistics = $statistics;
    }

    /**
     * {@inheritDoc}
     */
    public function printHeader(Formatter $formatter, Suite $suite)
    {
        if ($this->statistics) {
            $this->statistics->reset();
        }

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();
        $outputPrinter->createNewFile($suite->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function printFooter(Formatter $formatter, Suite $suite)
    {
        $formatter->getOutputPrinter()->flush();
    }
}
