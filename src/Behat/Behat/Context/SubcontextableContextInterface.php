<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context interface with subcontexts support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubcontextableContextInterface extends ContextInterface
{
    /**
     * Returns all added subcontexts.
     *
     * @return array
     */
    public function getSubcontexts();

    /**
     * Finds subcontext by it's name.
     *
     * @param string $className
     *
     * @return ContextInterface
     */
    public function getSubcontextByClassName($className);
}
