<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\OutlineNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Outline event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var null|integer
     */
    private $result;

    /**
     * Initializes outline event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param null|integer         $result
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $result = null
    )
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->outline = $outline;
        $this->result = $result;
    }

    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
    }

    /**
     * Returns outline tester result code.
     *
     * @return null|integer
     */
    public function getResult()
    {
        return $this->result;
    }
}
