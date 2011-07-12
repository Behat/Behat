<?php

namespace Behat\Behat\Console\Input;

use Symfony\Component\Console\Input\InputDefinition as BaseDefinition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console input definition.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InputDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function asText()
    {
        // find the largest option or argument name
        $max = 0;
        foreach ($this->getOptions() as $option) {
            $nameLength = strlen($option->getName()) + 2;
            if ($option->getShortcut()) {
                $nameLength += strlen($option->getShortcut()) + 3;
            }

            $max = max($max, $nameLength);
        }
        foreach ($this->getArguments() as $argument) {
            $max = max($max, strlen($argument->getName()));
        }
        ++$max;

        $text = array();

        if ($this->getArguments()) {
            $text[] = '<comment>Arguments:</comment>';
            foreach ($this->getArguments() as $argument) {
                if (null !== $argument->getDefault() && (!is_array($argument->getDefault()) || count($argument->getDefault()))) {
                    $default = sprintf('<comment> (default: %s)</comment>', is_array($argument->getDefault()) ? str_replace("\n", '', var_export($argument->getDefault(), true)): $argument->getDefault());
                } else {
                    $default = '';
                }

                $description = str_replace("\n", "\n".str_pad('', $max + 2, ' '), $argument->getDescription());

                $text[] = sprintf(" <info>%-${max}s</info> %s%s", $argument->getName(), $description, $default);
            }

            $text[] = '';
        }

        if ($this->getOptions()) {
            $text[] = '<comment>Options:</comment>';

            foreach ($this->getOptions() as $option) {
                if ($option->acceptValue() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault()))) {
                    $default = sprintf('<comment> (default: %s)</comment>', is_array($option->getDefault()) ? str_replace("\n", '', print_r($option->getDefault(), true)): $option->getDefault());
                } else {
                    $default = '';
                }

                $multiple = $option->isArray() ? '<comment> (multiple values allowed)</comment>' : '';
                $description = str_replace("\n", "\n".str_pad('', $max + 2, ' '), $option->getDescription());

                $optionMax = $max - strlen($option->getName()) - 2;
                $text[] = sprintf(" <info>%s</info> %-${optionMax}s%s%s%s",
                    '--'.$option->getName(),
                    $option->getShortcut() ? sprintf('(-%s) ', $option->getShortcut()) : '',
                    $description,
                    $default,
                    $multiple
                );
            }

            $text[] = '';
        }

        return implode("\n", $text);
    }
}
