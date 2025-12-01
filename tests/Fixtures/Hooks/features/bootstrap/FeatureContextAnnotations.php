<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class FeatureContextAnnotations implements Context
{
    private $number;
    private $scenarioFilter;

    /**
     * @BeforeFeature
     */
    public static function doSomethingBeforeFeature($event)
    {
        $params = $event->getSuite()->getSetting('parameters');
        echo '= do something ' . $params['before_feature'];
    }

    /**
     * @AfterFeature
     */
    public static function doSomethingAfterFeature($event)
    {
        $params = $event->getSuite()->getSetting('parameters');
        echo '= do something ' . $params['after_feature'];
    }

    /**
     * @BeforeFeature @someFeature
     */
    public static function doSomethingBeforeSomeFeature($event)
    {
        echo '= do something before SOME feature';
    }

    /**
     * @AfterFeature @someFeature
     */
    public static function doSomethingAfterSomeFeature($event)
    {
        echo '= do something after SOME feature';
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario($event)
    {
        $this->number = 50;
    }

    /**
     * @BeforeScenario @thirty
     */
    public function beforeScenarioThirty($event)
    {
        $this->number = 30;
    }

    /**
     * @BeforeScenario @exception
     */
    public function beforeScenarioException($event)
    {
        throw new Exception('Exception');
    }

    /**
     * @BeforeScenario @filtered
     */
    public function beforeScenarioFiltered($event)
    {
        $this->scenarioFilter = 'filtered';
    }

    /**
     * @BeforeScenario @~filtered
     */
    public function beforeScenarioNotFiltered($event)
    {
        $this->scenarioFilter = '~filtered';
    }

    /**
     * @BeforeStep I must have 100
     */
    public function beforeStep100($event)
    {
        $this->number = 100;
    }

    /**
     * @Given /^I have entered (\d+)$/
     */
    public function iHaveEntered($number)
    {
        $this->number = intval($number);
    }

    /**
     * @Then /^I must have (\d+)$/
     */
    public function iMustHave($number)
    {
        PHPUnit\Framework\Assert::assertEquals(intval($number), $this->number);
    }

    /**
     * @Then I must have a scenario filter value of :value
     */
    public function iMustHaveScenarioFilter($filterValue)
    {
        PHPUnit\Framework\Assert::assertEquals($filterValue, $this->scenarioFilter);
    }

    /**
     * @BeforeScenario
     */
    public function passingBeforeScenarioHook()
    {
        echo 'Is passing';
    }

    /**
     * @AfterScenario
     */
    public function passingAfterScenarioHook()
    {
        echo 'Is passing';
    }

    /**
     * @BeforeScenario @failing-before-hook
     */
    public function failingBeforeScenarioHook()
    {
        throw new RuntimeException('Is failing');
    }

    /**
     * @BeforeStep
     */
    public function passingBeforeStep()
    {
        echo 'Is passing';
    }

    /**
     * @BeforeStep passing step with failing hook
     */
    public function failingBeforeStep()
    {
        throw new RuntimeException('Is failing');
    }

    /**
     * @AfterStep
     */
    public function passingAfterStep()
    {
        echo 'Is passing';
    }

    /**
     * @Given passing step
     * @Given passing step with failing hook
     */
    public function passingStep()
    {
        echo 'Is passing';
    }

    /**
     * @Given failing step
     */
    public function failingStep()
    {
        throw new RuntimeException('Failing');
    }
}
