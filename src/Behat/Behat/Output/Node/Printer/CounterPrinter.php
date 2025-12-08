<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Behat counter printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CounterPrinter
{
    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Prints scenario and step counters.
     *
     * @param string        $intro
     */
    public function printCounters(OutputPrinter $printer, $intro, array $stats): void
    {
        $stats = array_filter($stats, fn ($count): bool => 0 !== $count);

        $totalCount = 0 === count($stats) ? 0 : array_sum($stats);

        $detailedStats = [];
        foreach ($stats as $resultCode => $count) {
            $style = $this->resultConverter->convertResultCodeToString($resultCode);

            $transId = $style . '_count';
            $message = $this->translator->trans($transId, ['%count%' => $count], 'output');

            $detailedStats[] = sprintf('{+%s}%s{-%s}', $style, $message, $style);
        }

        $message = $this->translator->trans($intro, ['%count%' => $totalCount], 'output');
        $printer->write($message);

        if (count($detailedStats)) {
            $printer->write(sprintf(' (%s)', implode(', ', $detailedStats)));
        }

        $printer->writeln();
    }
}
