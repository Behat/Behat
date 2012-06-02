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
 * Extended InputDefinition, which supports switchers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InputDefinition extends BaseDefinition
{
    /**
     * Gets the synopsis.
     *
     * @return string The synopsis
     */
    public function getSynopsis()
    {
        $elements = array();
        $isSwitch = false;
        $options  = $this->getOptions();

        foreach ($options as $option) {
            if ($isSwitch) {
                $isSwitch = false;
                continue;
            }

            // if switch
            if (array_key_exists('no-'.$option->getName(), $options)) {
                $elements[] = sprintf('[--[no-]%s]', $option->getName());
                $isSwitch   = true;
            } else {
                $shortcut = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
                $elements[] = sprintf('['.($option->isValueRequired() ? '%s--%s="..."' : ($option->isValueOptional() ? '%s--%s[="..."]' : '%s--%s')).']', $shortcut, $option->getName());
            }
        }

        foreach ($this->getArguments() as $argument) {
            $elements[] = sprintf($argument->isRequired() ? '%s' : '[%s]', $argument->getName().($argument->isArray() ? '1' : ''));

            if ($argument->isArray()) {
                $elements[] = sprintf('... [%sN]', $argument->getName());
            }
        }

        return implode(' ', $elements);
    }

    /**
     * Returns a textual representation of the InputDefinition.
     *
     * @return string A string representing the InputDefinition
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
            if ($this->hasOption('no-'.$option->getName())) {
                $nameLength += 5;
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
                    $default = sprintf('<comment> (default: %s)</comment>', $this->subformatDefaultValue($argument->getDefault()));
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

            $isSwitch = false;
            $options  = $this->getOptions();
            foreach ($options as $option) {
                if ($isSwitch) {
                    $isSwitch = false;
                    continue;
                }

                if ($option->acceptValue() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault()))) {
                    $default = sprintf('<comment> (default: %s)</comment>', $this->subformatDefaultValue($option->getDefault()));
                } else {
                    $default = '';
                }

                if ($this->hasOption('no-'.$option->getName())) {
                    $isSwitch = true;
                }

                $multiple = $option->isArray() ? '<comment> (multiple values allowed)</comment>' : '';
                $description = str_replace("\n", "\n".str_pad('', $max + 2, ' '), $option->getDescription());

                $optionMax = $max - strlen($option->getName()) - 2;

                if ($isSwitch) {
                    $optionMax -= 5;
                }

                $text[] = sprintf(" <info>%s</info> %-${optionMax}s%s%s%s",
                    '--'.( $isSwitch ? '[no-]' : '' ) . $option->getName(),
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

    private function subformatDefaultValue($default)
    {
        if (is_array($default) && $default === array_values($default)) {
            return sprintf("array('%s')", implode("', '", $default));
        }

        return str_replace("\n", '', var_export($default, true));
    }
}
