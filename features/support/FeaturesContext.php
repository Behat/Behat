<?php

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class FeaturesContext extends BaseFeaturesContext
{
    private $command;
    private $output;
    private $return;

    public function __construct()
    {
        $this->addSubcontext(new Hooks());
    }

    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        file_put_contents($filename, $content);
    }

    public function iRunBehat($command)
    {
        $php     = 0 === mb_strpos(BEHAT_PHP_BIN_PATH, '/usr/bin/env')
                 ? BEHAT_PHP_BIN_PATH
                 : escapeshellarg(BEHAT_PHP_BIN_PATH);
        $command = strtr($command, array('\'' => '"'));

        exec($php . ' ' . escapeshellarg(BEHAT_BIN_PATH) . ' --no-time --no-colors ' . $command, $output, $return);

        $this->command = $command;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;
    }

    /**
     * @Then /^it should (fail|pass) with:$/
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        if ('fail' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
        }

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback('/ features\/[^\n ]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
            $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
            $text = preg_replace_callback('/\+[fd] [^ ]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
        }

        try {
            assertEquals((string) $text, $this->output);
        } catch (Exception $e) {
            $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
            throw new Exception($diff, $e->getCode(), $e);
        }
    }

    /**
     * @Then /^display last command output$/
     */
    public function displayLastCommandOutput()
    {
        $this->printDebug("`" . $this->command . "`:\n" . $this->output);
    }

    /**
     * @Then /^the output should contain:$/
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback('/ features\/[^\n ]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
            $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
            $text = preg_replace_callback('/\+[fd] [^ ]+/', function($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string) $text);
        }

        try {
            assertContains((string) $text, $this->output);
        } catch (Exception $e) {
            $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
            throw new Exception($diff, $e->getCode(), $e);
        }
    }

    /**
     * @Given /^I am in the "([^"]*)" path$/
     */
    public function iAmInThePath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            chdir($path);
        }
    }

    /**
     * @Given /^file "([^"]*)" should exist$/
     */
    public function fileShouldExist($path)
    {
        assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * @Then /^it should (fail|pass)$/
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
        }
    }

    /**
     * @Then /^I must have (\d+)$/
     */
    public function iMustHave($argument1)
    {
        throw new Pending();
    }
}
