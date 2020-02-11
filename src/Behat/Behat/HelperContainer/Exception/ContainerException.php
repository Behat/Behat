<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Exception;

class_alias(
    interface_exists('Interop\\Container\\Exception\\ContainerException')
        ? 'Interop\\Container\\Exception\\ContainerException'
        : 'Psr\\Container\\ContainerExceptionInterface',
    'Behat\\Behat\\HelperContainer\\Exception\\ContainerException'
);

if (false) {
    /**
     * @internal
     */
    interface ContainerException
    {
    }
}
