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
 * Extended context interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExtendedContextInterface extends SubcontextableContextInterface
{
    /**
     * Sets parent context of current context.
     *
     * @param ExtendedContextInterface $parentContext
     */
    public function setParentContext(ExtendedContextInterface $parentContext);

    /**
     * Returns main context.
     *
     * @return ExtendedContextInterface
     */
    public function getMainContext();

    /**
     * Find current context's subcontext by alias name.
     *
     * @param string $alias
     *
     * @return ExtendedContextInterface
     */
    public function getSubcontext($alias);
}
