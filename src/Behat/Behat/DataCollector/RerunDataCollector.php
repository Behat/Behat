<?php

namespace Behat\Behat\DataCollector;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat --rerun data collector.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RerunDataCollector implements EventSubscriberInterface
{
    /**
     * Rerun file path.
     *
     * @var     string
     */
    private $rerunFile;
    /**
     * List of failed scenarios with lines.
     *
     * @var     array
     */
    private $scenarios = array();

    /**
     * Sets rerun file path.
     *
     * @param   string  $path
     */
    public function setRerunFile($path)
    {
        $this->rerunFile = $path;
    }

    /**
     * Checks whether rerun data collector has collected some failed scenarios previously.
     *
     * @return  Boolean
     */
    public function hasFailedScenarios()
    {
        return count($this->getFailedScenariosPaths());
    }

    /**
     * Returns collected previously failed scenarios.
     *
     * @return  array
     */
    public function getFailedScenariosPaths()
    {
        if (null === $this->rerunFile || !file_exists($this->rerunFile)) {
            return array();
        }

        if ('' === trim($contents = file_get_contents($this->rerunFile))) {
            return array();
        }

        return explode("\n", $contents);
    }

    /**
     * Writes rerun data file.
     */
    public function __destruct()
    {
        if (null !== $this->rerunFile) {
            file_put_contents($this->rerunFile, implode("\n", $this->scenarios));
        }
    }

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterOutline');

        return array_combine($events, $events);
    }

    /**
     * Listends to scenario event and record failed scenario to rerun dataset.
     *
     * @param   Behat\Behat\Event\ScenarioEvent   $event
     */
    public function afterScenario(ScenarioEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $scenario = $event->getScenario();
            $this->scenarios[] = $scenario->getFile() . ':' . $scenario->getLine();
        }
    }

    /**
     * Listends to outline event and record failed scenario to rerun dataset.
     *
     * @param   Behat\Behat\Event\OutlineEvent  $event
     */
    public function afterOutline(OutlineEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $outline = $event->getOutline();
            $this->scenarios[] = $outline->getFile() . ':' . $outline->getLine();
        }
    }
}
