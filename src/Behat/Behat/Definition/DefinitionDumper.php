<?php

namespace Behat\Behat\Definition;

use Symfony\Component\Translation\Translator;

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
     * Translator.
     *
     * @var     Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * Initializes definition dispatcher.
     *
     * @param   Behat\Behat\Definition\DefinitionDispatcher $dispatcher
     * @param   Symfony\Component\Translation\Translator    $translator
     */
    public function __construct(DefinitionDispatcher $dispatcher, Translator $translator)
    {
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
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
            $definitions .= "'$regex'\n\n";
        }

        return $definitions;
    }
}
