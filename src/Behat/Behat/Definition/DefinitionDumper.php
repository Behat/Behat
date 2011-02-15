<?php

namespace Behat\Behat\Definition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definition dumper.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionDumper
{
    /**
     * Definition dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    protected $dispatcher;

    /**
     * Initializes definition dispatcher.
     *
     * @param   Behat\Behat\Definition\DefinitionDispatcher $dispatcher
     */
    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dump available definitions into string.
     *
     * @param   string  $language   default definitions language
     */
    public function dump($language = 'en')
    {
        $definitions = '';

        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {
            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);

            $definitions .= "'$regex'\n\n";
        }

        return $definitions;
    }
}
