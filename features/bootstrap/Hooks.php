<?php

use Behat\Behat\Context\AnnotatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class Hooks extends BehatContext implements AnnotatedContextInterface
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
        mkdir('features' . DIRECTORY_SEPARATOR . 'bootstrap');
        mkdir('features' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'i18n');

        mkdir('features' . DIRECTORY_SEPARATOR . 'support');
        mkdir('features' . DIRECTORY_SEPARATOR . 'steps');
        mkdir('features' . DIRECTORY_SEPARATOR . 'steps' . DIRECTORY_SEPARATOR . 'i18n');
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
