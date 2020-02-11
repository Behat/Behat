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
    interface_exists('Interop\\Container\\Exception\\NotFoundException')
        ? 'Interop\\Container\\Exception\\NotFoundException'
        : 'Psr\\Container\\NotFoundExceptionInterface',
    'Behat\\Behat\\HelperContainer\\Exception\\NotFoundException'
);

if (false) {
    /**
     * @internal
     */
    interface NotFoundException
    {
    }
}
