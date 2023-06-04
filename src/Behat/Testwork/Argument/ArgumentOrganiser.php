<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

/**
 * Organises function arguments using its reflection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArgumentOrganiser
{
    /**
     * Organises arguments using function reflection.
     *
     * @param mixed[] $arguments
     *
     * @return mixed[]
     */
    public function organiseArguments(\ReflectionFunctionAbstract $function, array $arguments);
}
