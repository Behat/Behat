<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints collected deprecations at the end of the test run.
 */
final class DeprecationPrinter
{
    public function __construct(
        private readonly DeprecationCollector $collector,
    ) {
    }

    /**
     * Prints all collected deprecations.
     */
    public function printDeprecations(OutputInterface $output): void
    {
        if (!$this->collector->hasDeprecations()) {
            return;
        }

        $deprecations = $this->collector->getDeprecations();
        $totalCount = $this->collector->getDeprecationCount();
        $uniqueCount = count($deprecations);

        $output->writeln('');
        $output->writeln(sprintf(
            '<comment>%d deprecation%s triggered (%d unique):</comment>',
            $totalCount,
            $totalCount === 1 ? '' : 's',
            $uniqueCount
        ));
        $output->writeln('');

        foreach ($deprecations as $message => $count) {
            $countText = $count > 1 ? sprintf(' <comment>(%dx)</comment>', $count) : '';
            $output->writeln(sprintf('  <comment>⚠</comment> %s%s', $message, $countText));
        }

        $output->writeln('');
    }
}
