<?php

namespace BehaviorTester;

class FeaturesTestSuite extends \PHPUnit_Framework_TestSuite
{
    protected $features = array();

    public function __construct($directory, array $options = array())
    {
        if (is_string($directory) && is_dir($directory)) {
            $this->setName($directory);

            $iterator = new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            );
            $iterator = new \RecursiveIteratorIterator(
                $iterator, \RecursiveIteratorIterator::SELF_FIRST
            );
            $parser = new \Gherkin\Parser;

            foreach ($iterator as $i => $file) {
                $casesPath = dirname($file) . '/../cases/';
                $featureName = strtr(basename($file), array('.feature' => 'FeatureCase'));
                if (is_file($casesPath . $featureName . '.php')) {
                    $class = $featureName;
                } else {
                    $class = isset($options['f_class']) ? $options['f_class'] : 'BaseFeatureCase';
                }
                require_once $casesPath . $class . '.php';

                $this->features[] = $parser->parse(file_get_contents($file));

                $this->addTestSuite($class);
//                $this->addTest(new $class($parser->parse(file_get_contents($file))));
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'directory name');
        }
    }
}
