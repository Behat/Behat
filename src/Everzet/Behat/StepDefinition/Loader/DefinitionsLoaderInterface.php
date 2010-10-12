<?php

namespace Everzet\Behat\StepDefinition\Loader;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definitions Loader Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionsLoaderInterface
{
    /**
     * Initialize loader. 
     * 
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function __construct(EventDispatcher $dispatcher);

    /**
     * Load definitions from file. 
     * 
     * @param   string          $path       plain php file path
     * @return  array                       array of Definitions & Transformations
     */
    public function load($path);
}

