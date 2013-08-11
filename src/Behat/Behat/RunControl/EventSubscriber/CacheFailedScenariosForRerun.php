<?php

namespace Behat\Behat\RunControl\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cache failed scenarios for rerun.
 * Subscribes to specific events and caches failed scenarios list to the file.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CacheFailedScenariosForRerun implements EventSubscriberInterface
{
    /**
     * @var null|string
     */
    private $cache;
    /**
     * @var null|string
     */
    private $key;
    /**
     * @var string[]
     */
    private $lines = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_SCENARIO        => array('collectFailedScenario', -50),
            EventInterface::AFTER_OUTLINE_EXAMPLE => array('collectFailedOutlineExample', -50),
            EventInterface::AFTER_EXERCISE        => array('writeCache', -50),
        );
    }

    /**
     * Sets cache directory.
     *
     * @param string $cache
     */
    public function setCache($cache)
    {
        $this->cache = rtrim($cache, DIRECTORY_SEPARATOR);
    }

    /**
     * Sets unique key for the run.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Records scenario if it is failed.
     *
     * @param ScenarioEvent $event
     */
    public function collectFailedScenario(ScenarioEvent $event)
    {
        if (!$this->getFileName()) {
            return;
        }
        if (StepEvent::FAILED !== $event->getResult()) {
            return;
        }

        $scenario = $event->getScenario();

        $this->lines[] = $scenario->getFile() . ':' . $scenario->getLine();
    }

    /**
     * Records outline example if it is failed.
     *
     * @param OutlineExampleEvent $event
     */
    public function collectFailedOutlineExample(OutlineExampleEvent $event)
    {
        if (!$this->getFileName()) {
            return;
        }
        if (StepEvent::FAILED !== $event->getResult()) {
            return;
        }

        $outline = $event->getOutline();
        $examples = $outline->getExamples();
        $lines = $examples->getRowLines();

        $this->lines[] = $outline->getFile() . ':' . $lines[$event->getIteration() + 1];
    }

    /**
     * Writes failed scenarios cache.
     */
    public function writeCache()
    {
        if (!$this->getFileName()) {
            return;
        }

        if (0 === count($this->lines) && file_exists($this->getFileName())) {
            unlink($this->getFileName());

            return;
        }

        file_put_contents($this->getFileName(), trim(implode("\n", $this->lines)));
    }

    /**
     * Returns cache filename (if exists).
     *
     * @return null|string
     */
    public function getFileName()
    {
        if (null === $this->cache || null === $this->key) {
            return null;
        }

        return $this->cache . DIRECTORY_SEPARATOR . $this->key . '.scenarios';
    }
}
