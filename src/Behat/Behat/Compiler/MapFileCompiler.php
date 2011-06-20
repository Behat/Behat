<?php

namespace Behat\Behat\Compiler;

use Symfony\Component\Finder\Finder;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class loader map file compiler.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class MapFileCompiler
{
    /**
     * Behat lib directory.
     *
     * @var     string
     */
    private $libPath;

    /**
     * Initializes compiler.
     */
    public function __construct()
    {
        $this->libPath = realpath(__DIR__ . '/../../../../');
    }

    /**
     * Compiles map file and autoloader.
     *
     * @param   string  $version
     */
    public function compile($autoloaderFilename = 'autoload.php', $mapFilename = 'autoload_map.php')
    {
        if (file_exists($mapFilename)) {
            unlink($mapFilename);
        }
        $mappings = '';

        foreach ($this->findPhpFile()->in($this->libPath . '/src') as $file) {
            $path   = str_replace($this->libPath . '/src/', '', $file->getRealPath());
            $class  = str_replace(array('/', '.php'), array('\\', ''), $path);
            $mappings .= "    '$class' => __DIR__ . '/src/$path',\n";
        }

        foreach ($this->findPhpFile()->in($this->libPath . '/vendor/Gherkin/src') as $file) {
            $path   = str_replace($this->libPath . '/vendor/Gherkin/src/', '', $file->getRealPath());
            $class  = str_replace(array('/', '.php'), array('\\', ''), $path);
            $mappings .= "    '$class' => __DIR__ . '/vendor/Gherkin/src/$path',\n";
        }

        foreach ($this->findPhpFile()->in($this->libPath . '/vendor/Symfony') as $file) {
            $path   = str_replace($this->libPath . '/vendor/', '', $file->getRealPath());
            $class  = str_replace(array('/', '.php'), array('\\', ''), $path);
            $mappings .= "    '$class' => __DIR__ . '/vendor/$path',\n";
        }

        $mapContent = <<<MAP_FILE
<?php
return array(
$mappings
);
MAP_FILE;

        file_put_contents($mapFilename, $mapContent);
        file_put_contents($autoloaderFilename, $this->getAutoloadScript($mapFilename));
    }

    /**
     * Returns autoload.php content.
     *
     * @param   string  $mapFilename
     *
     * @return  string
     */
    protected function getAutoloadScript($mapFilename)
    {
        return sprintf(<<<'EOF'
<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Behat\Behat\ClassLoader\MapFileClassLoader')) {
    require_once __DIR__ . '/src/Behat/Behat/ClassLoader/MapFileClassLoader.php';
}

use Behat\Behat\ClassLoader\MapFileClassLoader;

$loader = new MapFileClassLoader(__DIR__ . '/%s');
$loader->register();

EOF
        , $mapFilename);
    }

    /**
     * Creates finder instance to search php files.
     *
     * @return  Symfony\Component\Finder\Finder
     */
    private function findPhpFile()
    {
        $finder = new Finder();

        return $finder->files()->ignoreVCS(true)->name('*.php');
    }
}
