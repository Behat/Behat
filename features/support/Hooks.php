<?php

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class Hooks extends BehatContext
{
    /**
     * @BeforeSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
            static::rmdirRecursive($dir);
        }
    }

    /**
     * @BeforeScenario
     */
    public function prepareTestFolders()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR .
               md5(microtime() * rand(0, 10000));

        mkdir($dir, 0777, true);
        chdir($dir);
        mkdir('features');
        mkdir('features' . DIRECTORY_SEPARATOR . 'support');
        mkdir('features' . DIRECTORY_SEPARATOR . 'support' . DIRECTORY_SEPARATOR . 'i18n');
    }

    /**
     * @Given /^I have entered (\d+)$/
     */
    public function iHaveEntered($argument1)
    {
        throw new Pending();
    }

    private static function rmdirRecursive($dir) {
        $files = scandir($dir);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                static::rmdirRecursive($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
