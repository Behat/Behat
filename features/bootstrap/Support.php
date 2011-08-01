<?php

use Behat\Behat\Context\BehatContext;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat test suite support.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Support extends BehatContext
{
    public function createFile($filename, $content)
    {
        file_put_contents($filename, $content);
    }

    public function moveToNewPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        chdir($path);
    }

    public function hello($name)
    {
        return "Hello, $name";
    }
}
