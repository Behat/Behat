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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExtendedContextInterface extends SubcontextableContextInterface
{
    /**
     * Sets parent context of current context.
     *
     * @param   Behat\Behat\Context\ExtendedContextInterface    $parentContext  parent context
     */
    public function setParentContext(ExtendedContextInterface $parentContext);

    /**
     * Returns main context.
     *
     * @return  Behat\Behat\Context\ExtendedContextInterface
     */
    public function getMainContext();

    /**
     * Find current context's subcontext by alias name.
     *
     * @param   string  $alias  subcontext alias name
     *
     * @return  Behat\Behat\Context\ExtendedContextInterface
     */
    public function getSubcontext($alias);
}
