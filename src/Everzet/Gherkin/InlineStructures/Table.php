<?php

namespace Everzet\Gherkin\InlineStructures;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Table.
 *
 * @package     behat
 * @subpackage  Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Table
{
    protected $rowSplitter;
    protected $keys = array();
    protected $values = array();

    public function __construct($rowSplitter = '|')
    {
        $this->rowSplitter = $rowSplitter;
    }

    public function addRow($row)
    {
        $items = array_map(function($item) {
            return trim($item);
        }, explode($this->rowSplitter, $row));

        if (empty($this->keys)) {
            $this->keys = $items;
        } else {
            $this->values[] = $items;
        }
    }

    public function getHash()
    {
        $hash = array();

        foreach ($this->values as $rowValues) {
            $hash[] = array_combine($this->keys, $rowValues);
        }

        return $hash;
    }
}
