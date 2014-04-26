<?php

use Behat\Behat\Context\BehatContext;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat test suite hooks.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Hooks extends BehatContext
{
    /**
     * @BeforeScenario
     *
     * Checks that we have access to main context (FeatureContext).
     */
    public function checkThatWeHaveMainContext()
    {
        \PHPUnit_Framework_Assert::assertInstanceOf('FeatureContext', $this->getMainContext());
        \PHPUnit_Framework_Assert::assertEquals('Hello, zet', $this->getMainContext()->getSubcontext('support')->hello('zet'));
    }

    /**
     * @BeforeSuite
     *
     * Cleans test folders in the temporary directory.
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
            self::rmdirRecursive($dir);
        }
    }

    /**
     * @BeforeScenario
     *
     * Prepares test folders in the temporary directory.
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

    /**
     * Removes files and folders recursively at provided path.
     *
     * @param   string  $path
     */
    private static function rmdirRecursive($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::rmdirRecursive($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
