<?php

namespace Behat\Behat\Console\Output;

use Symfony\Component\Console\Output\StreamOutput;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console output.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleOutput extends StreamOutput
{
    /**
     * {@inheritdoc}
     */
    public function __construct($stream = null, $verbosity = self::VERBOSITY_NORMAL, $decorated = null)
    {
        if (null === $stream) {
            $stream = fopen('php://stdout', 'w');
        }
        parent::__construct($stream, $verbosity, $decorated);

        $this->setStyle('undefined',        array('fg' => 'yellow'));
        $this->setStyle('pending',          array('fg' => 'yellow'));
        $this->setStyle('pending_param',    array('fg' => 'yellow', 'bold' => 1));
        $this->setStyle('failed',           array('fg' => 'red'));
        $this->setStyle('failed_param',     array('fg' => 'red', 'bold' => 1));
        $this->setStyle('passed',           array('fg' => 'green'));
        $this->setStyle('passed_param',     array('fg' => 'green', 'bold' => 1));
        $this->setStyle('skipped',          array('fg' => 'cyan'));
        $this->setStyle('skipped_param',    array('fg' => 'cyan', 'bold' => 1));
        $this->setStyle('comment',          array('fg' => 'black'));
        $this->setStyle('tag',              array('fg' => 'cyan'));
    }

    /**
     * {@inheritdoc}
     */
    protected function format($message)
    {
        $message = preg_replace_callback('#{\+([a-z][a-z0-9\-_=;]+)}#i',
            array($this, 'replaceStartStyle'), $message
        );

        return preg_replace_callback('#{\-([a-z][a-z0-9\-_]*)?}#i',
            array($this, 'replaceEndStyle'), $message
        );
    }
}
