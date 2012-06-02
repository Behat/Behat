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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PearMapFileCompiler
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
     * @param string $version
     */
    public function compile($autoloaderFilename = 'autoload.php', $mapFilename = 'autoload_map.php')
    {
        if (file_exists($mapFilename)) {
            unlink($mapFilename);
        }
        $mappings = '';

        $mappings .= $this->generateMappingFor(
            $this->libPath.'/src',
            "__DIR__ . '/src/'"
        );
        $mappings .= $this->generateMappingFor(
            $this->libPath.'/vendor/behat/gherkin/src',
            "'gherkin/src/'"
        );

        $mappings .= "\nif (!defined('BEHAT_AUTOLOAD_SF2') || true === BEHAT_AUTOLOAD_SF2) {\n";
        $mappings .= $this->generateMappingFor(
            array(
                $this->libPath.'/vendor/symfony/config',
                $this->libPath.'/vendor/symfony/console',
                $this->libPath.'/vendor/symfony/dependency-injection',
                $this->libPath.'/vendor/symfony/event-dispatcher',
                $this->libPath.'/vendor/symfony/finder',
                $this->libPath.'/vendor/symfony/translation',
                $this->libPath.'/vendor/symfony/yaml',
            )
        );
        $mappings .= "}\n";

        $mapContent = <<<MAP_FILE
<?php
\$mappings = array();
$mappings
return \$mappings;
MAP_FILE;

        file_put_contents($mapFilename, $mapContent);
        file_put_contents($autoloaderFilename, $this->getAutoloadScript($mapFilename));
    }

    /**
     * Generates mapping for specific path(s).
     *
     * @param mixed       $paths      single path or array of paths
     * @param string|null $prefixCode path prefix code
     *
     * @return strings
     */
    protected function generateMappingFor($paths, $prefixCode = null)
    {
        $mappings = '';

        foreach ((array) $paths as $path) {
            $trim = $path.'/';
            foreach ($this->findPhpFile()->in($path) as $file) {
                $path  = str_replace($trim, '', $file->getRealPath());
                $class = str_replace(array('/', '.php'), array('\\', ''), $path);

                if (null !== $prefixCode) {
                    $mappings .= "\$mappings['{$class}'] = {$prefixCode} . '{$path}';\n";
                } else {
                    $mappings .= "\$mappings['{$class}'] = '{$path}';\n";
                }
            }
        }

        return $mappings;
    }

    /**
     * Returns autoload.php content.
     *
     * @param string $mapFilename
     *
     * @return string
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
     * @return Symfony\Component\Finder\Finder
     */
    private function findPhpFile()
    {
        $finder = new Finder();

        return $finder->files()->ignoreVCS(true)->name('*.php');
    }
}
