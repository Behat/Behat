<?php

namespace BehaviorTester;

class ResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter
{
    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof TestCase) {
            if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $this->successful++;
                $success = TRUE;
            } else {
                $success = FALSE;
            }

            static $featureNum = 0;
            $features = $test->getFeatures();
            $feature = $features[$featureNum++];

            $this->onTest($this->currentTestMethodPrettified, $success, $feature);
        }
    }

    /**
     */
    protected function doEndClass()
    {
        $this->endClass($this->testClass);
    }

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     * @param  array   $steps
     */
    protected function onTest($name, $success = TRUE, array $steps = array())
    {
    }
}
