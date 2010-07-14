<?php

namespace Everzet\Behat\Exceptions;

use \Everzet\Behat\Exceptions\BehaviorException as BaseException;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Undefined step Exception
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Undefined extends BaseException
{
    protected $text;

    /**
     * Creates Exception
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
