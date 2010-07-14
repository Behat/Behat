<?php

namespace Everzet\Gherkin\Structures\Inline;

use \Everzet\Gherkin\Structures\Inline\Table;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Examples.
 *
 * @package     behat
 * @subpackage  Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Examples
{
    protected $title;
    protected $table;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;
    }

    public function getTable()
    {
        return $this->table;
    }
}
