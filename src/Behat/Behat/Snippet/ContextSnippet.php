<?php

namespace Behat\Behat\Snippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context definition snippet.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextSnippet implements ContextSnippetInterface
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $template;
    /**
     * @var string[]
     */
    private $contextClasses = array();

    /**
     * Initializes definition snippet.
     *
     * @param string   $type           Step interested in snippet
     * @param string   $template       Definition snippet template
     * @param string[] $contextClasses Context classes this snippet belongs to
     */
    public function __construct($type, $template, array $contextClasses)
    {
        $this->type = in_array($type, array('Given', 'When', 'Then')) ? $type : 'Given';
        $this->template = $template;
        $this->contextClasses = $contextClasses;
    }

    /**
     * Returns snippet unique hash (ignoring step type).
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->template);
    }

    /**
     * Returns definition snippet text.
     *
     * @return string
     */
    public function getSnippet()
    {
        return sprintf($this->template, $this->type);
    }

    /**
     * Returns array of context classes this snippet belongs to.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return $this->contextClasses;
    }

    /**
     * Sets snippet context classes.
     *
     * @param string[] $contextClasses
     */
    public function setContextClasses(array $contextClasses)
    {
        $this->contextClasses = $contextClasses;
    }
}
