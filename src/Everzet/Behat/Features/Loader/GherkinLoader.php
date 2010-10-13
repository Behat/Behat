<?php

namespace Everzet\Behat\Features\Loader;

use Everzet\Gherkin\Parser;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gherkin DSL Loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinLoader implements LoaderInterface
{
    protected $parser;

    /**
     * Initialize loader.
     *
     * @param   Everzet\Gherkin\Parser              $parser Gherkin parser instance
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Load feature from specified path.
     *
     * @param   string                              $paths  features path(s)
     * 
     * @return  Everzet\Gherkin\Node\FeatureNode            feature node
     */
    public function load($path)
    {
        return $this->parser->parseFile($path);
    }
}

