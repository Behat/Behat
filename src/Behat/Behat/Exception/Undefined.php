<?php

namespace Behat\Behat\Exception;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Undefined step Exception.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Undefined extends BehaviorException
{
    protected $text;

    /**
     * Initialize Exception.
     *
     * @param   string  $text   step description
     */
    public function __construct($text)
    {
        parent::__construct();
        $this->text = $text;
        $this->message = sprintf('Undefined step "%s"', $this->text);
    }
}
