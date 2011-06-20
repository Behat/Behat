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
 * behat.phar package compiler.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PharCompiler
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
     * Compiles phar archive.
     *
     * @param   string  $version
     */
    public function compile($version)
    {
        if (file_exists($package = "behat-$version.phar")) {
            unlink($package);
        }

        // create phar
        $phar = new \Phar($package, 0, 'behat.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.xsd')
            ->name('*.xml')
            ->name('*.xliff')
            ->name('LICENSE')
            ->notName('PharCompiler.php')
            ->notName('PearCompiler.php')
            ->in($this->libPath . '/src')
            ->in($this->libPath . '/i18n')
            ->in($this->libPath . '/vendor');

        foreach ($finder as $file) {
            // don't compile test suites
            if (!preg_match('/\/tests\/|\/test\//', $file->getRealPath())) {
                $this->addFileToPhar($file, $phar);
            }
        }

        // license and autoloading
        $this->addFileToPhar(new \SplFileInfo($this->libPath . '/LICENSE'), $phar);
        $this->addFileToPhar(new \SplFileInfo($this->libPath . '/autoload.php'), $phar);
        $this->addFileToPhar(new \SplFileInfo($this->libPath . '/autoload_map.php'), $phar);

        // stub
        $phar->setStub($this->getStub($version));
        $phar->stopBuffering();

        unset($phar);
    }

    /**
     * Adds a file to phar archive.
     *
     * @param   SplFileInfo $file   file info
     * @param   Phar        $phar   phar packager
     */
    protected function addFileToPhar(\SplFileInfo $file, \Phar $phar)
    {
        $path = str_replace($this->libPath . '/', '', $file->getRealPath());
        $phar->addFromString($path, file_get_contents($file));
    }

    /**
     * Returns cli stub.
     *
     * @param   string  $version
     *
     * @return  string
     */
    protected function getStub($version)
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

define('BEHAT_PHP_BIN_PATH',    'php');
define('BEHAT_BIN_PATH',        __FILE__);
define('BEHAT_VERSION',         '%s');

Phar::mapPhar('behat.phar');
require_once 'phar://behat.phar/autoload.php';

// internal encoding to utf8
mb_internal_encoding('utf8');

// check, that we are in CLI
if ('cli' === php_sapi_name()) {
    $app = new Behat\Behat\Console\BehatApplication(BEHAT_VERSION);
    $app->run();
} else {
    throw new RuntimeException('Behat can be runned only as CLI utility');
}

exit(0);
__HALT_COMPILER();
EOF
        , $version);
    }
}
