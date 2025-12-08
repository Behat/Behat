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
    public function __construct(
        private readonly ?PhaseStatistics $statistics = null,
    ) {
    }

    public function printHeader(Formatter $formatter, Suite $suite): void
    {
        if ($this->statistics instanceof PhaseStatistics) {
            $this->statistics->reset();
        }

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();
        $outputPrinter->createNewFile($suite->getName());
    }

    public function printFooter(Formatter $formatter, Suite $suite): void
    {
        $formatter->getOutputPrinter()->flush();
    }
}
