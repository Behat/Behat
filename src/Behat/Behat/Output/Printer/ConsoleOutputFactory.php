<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Printer;

use Behat\Behat\Output\Printer\Formatter\ConsoleFormatter;
use Behat\Testwork\Output\Printer\Factory\ConsoleOutputFactory as BaseFactory;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Extends default printer with default styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConsoleOutputFactory extends BaseFactory
{
    /**
     * {@inheritDoc}
     */
    protected function createOutputFormatter()
    {
        $formatter = new ConsoleFormatter($this->isOutputDecorated());

        foreach ($this->getDefaultStyles() as $name => $style) {
            $formatter->setStyle($name, $style);
        }

        return $formatter;
    }

    /**
     * Returns default styles.
     *
     * @return OutputFormatterStyle[string]
     */
    private function getDefaultStyles()
    {
        return array(
            'keyword'       => new OutputFormatterStyle(null, null, array('bold')),
            'stdout'        => new OutputFormatterStyle(null, null, array()),
            'exception'     => new OutputFormatterStyle('red'),
            'undefined'     => new OutputFormatterStyle('yellow'),
            'pending'       => new OutputFormatterStyle('yellow'),
            'pending_param' => new OutputFormatterStyle('yellow', null, array('bold')),
            'failed'        => new OutputFormatterStyle('red'),
            'failed_param'  => new OutputFormatterStyle('red', null, array('bold')),
            'passed'        => new OutputFormatterStyle('green'),
            'passed_param'  => new OutputFormatterStyle('green', null, array('bold')),
            'skipped'       => new OutputFormatterStyle('cyan'),
            'skipped_param' => new OutputFormatterStyle('cyan', null, array('bold')),
            'comment'       => new OutputFormatterStyle('black'),
            'tag'           => new OutputFormatterStyle('cyan')
        );
    }
}
