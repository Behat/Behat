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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PharCompiler
{
    /**
     * Behat lib directory.
     *
     * @var     string
     */
    private $libPath;
    private $phpParser;
    private $traverser;
    private $printer;
    private $rewriter;
    private $classMap = array();

    /**
     * Initializes compiler.
     */
    public function __construct()
    {
        $this->libPath = realpath(__DIR__ . '/../../../../');
        $this->phpParser = new \PHPParser_Parser(new \PHPParser_Lexer());
        $this->traverser = new \PHPParser_NodeTraverser();
        $this->traverser->addVisitor($this->rewriter = new ClassNameRewritingVisitor('Behat\\Dependency\\', array('Symfony\\', 'Composer')));
        $this->printer = new \PHPParser_PrettyPrinter_Zend();
    }

    /**
     * Compiles phar archive.
     *
     * @param string $version
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
            ->name('LICENSE')
            ->notName('PharCompiler.php')
            ->notName('PearCompiler.php')
            ->in($this->libPath . '/src')
            ->in($this->libPath . '/vendor');

        foreach ($finder as $file) {
            // don't compile test suites
            if (!preg_match('/\/tests\/|\/test\//i', $file->getRealPath())) {
                $this->addFileToPhar($file, $phar);
            }
        }

        // license and autoloading
        $this->addFileToPhar(new \SplFileInfo($this->libPath . '/LICENSE'), $phar);
        $this->addFileToPhar(new \SplFileInfo($this->libPath . '/i18n.php'), $phar);

        // autoloader class map
        $classMap = '<?php return '.var_export($this->classMap, true).';';
        $phar->addFromString('vendor/composer/autoload_classmap.php', $classMap);

        // stub
        $phar->setStub($this->getStub($version));
        $phar->stopBuffering();

        unset($phar);
    }

    /**
     * Adds a file to phar archive.
     *
     * @param SplFileInfo $file file info
     * @param Phar        $phar phar packager
     */
    protected function addFileToPhar(\SplFileInfo $file, \Phar $phar)
    {
        $path = str_replace(realpath($this->libPath).DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $content = file_get_contents($file);
        $extension = substr($path, -4);
        if ('.php' === $extension) {
            $ast = $this->phpParser->parse($content);

            $this->rewriter->reset();
            $ast = $this->traverser->traverse($ast);
            foreach ($this->rewriter->getClassNames() as $name) {
                $this->classMap[$name] = 'phar://behat.phar/'.$path;
            }

            $content = '<?php '.$this->printer->prettyPrint($ast);
        } else if ('.yml' === $extension || '.xml' === $extension) {
            $content = preg_replace('#Symfony\\\\#', 'Behat\\Dependency\\Symfony\\', $content);
        }

        $phar->addFromString($path, $content);
    }

    /**
     * Replaces tokens in specified path.
     *
     * @param string|array $files       files array or single file
     * @param string       $tokenStart  token start symbol
     * @param string       $tokenFinish token finish symbol
     * @param array        $tokens      replace tokens array
     */
    protected function replaceTokens($files, $tokenStart, $tokenFinish, array $tokens)
    {
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($tokens as $key => $value) {
                $content = str_replace($tokenStart . $key . $tokenFinish, $value, $content, $count);
            }
            file_put_contents($file, $content);
        }
    }

    /**
     * Returns cli stub.
     *
     * @param string $version
     *
     * @return string
     */
    protected function getStub($version)
    {
        return sprintf(<<<'EOF'
#!/usr/bin/env php
<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('BEHAT_PHP_BIN_PATH', 'php');
define('BEHAT_BIN_PATH',     __FILE__);
define('BEHAT_VERSION',      '%s');

Phar::mapPhar('behat.phar');
require_once 'phar://behat.phar/vendor/autoload.php';

// internal encoding to utf8
mb_internal_encoding('utf8');

if (!defined('BEHAT_PHAR_CLI') || true === BEHAT_PHAR_CLI) {
    // check, that we are in CLI
    if ('cli' === php_sapi_name()) {
        $app = new Behat\Behat\Console\BehatApplication(BEHAT_VERSION);
        $app->run();
    } else {
        throw new RuntimeException('Behat can be runned only as CLI utility');
    }
    exit(0);
}

__HALT_COMPILER();
EOF
        , $version);
    }
}
