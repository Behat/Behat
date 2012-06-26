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
 * Pear package compiler.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PearCompiler
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
     * Compiles pear package.
     *
     * @param string $version
     */
    public function compile($version, $stability)
    {
        if (file_exists('package.xml')) {
            unlink('package.xml');
        }
        file_put_contents('package.xml', $this->getPackageTemplate());

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.xsd')
            ->name('*.xml')
            ->name('LICENSE')
            ->notName('PharCompiler.php')
            ->notName('PearCompiler.php')
            ->in($this->libPath . '/src');

        $xmlSourceFiles = '';
        foreach ($finder as $file) {
            $path = str_replace($this->libPath . '/', '', $file->getRealPath());
            $xmlSourceFiles .=
                '<file role="php" baseinstalldir="behat" install-as="'.$path.'" name="'.$path.'" />'."\n";
        }

        $this->replaceTokens('package.xml', '##', '##', array(
            'BEHAT_VERSION' => $version,
            'CURRENT_DATE'  => date('Y-m-d'),
            'SOURCE_FILES'  => $xmlSourceFiles,
            'STABILITY'     => $stability
        ));

        system('pear package');
        unlink('package.xml');
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
     * Returns pear package template.
     *
     * @return string
     */
    protected function getPackageTemplate()
    {
        return <<<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.8.0" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
    http://pear.php.net/dtd/tasks-1.0.xsd
    http://pear.php.net/dtd/package-2.0
    http://pear.php.net/dtd/package-2.0.xsd">
    <name>behat</name>
    <channel>pear.behat.org</channel>
    <summary>Behat is BDD framework for PHP</summary>
    <description>
        Behat is an open source behavior driven development framework for php 5.3.
    </description>
    <lead>
        <name>Konstantin Kudryashov</name>
        <user>everzet</user>
        <email>ever.zet@gmail.com</email>
        <active>yes</active>
    </lead>
    <date>##CURRENT_DATE##</date>
    <version>
        <release>##BEHAT_VERSION##</release>
        <api>2.0.0</api>
    </version>
    <stability>
        <release>##STABILITY##</release>
        <api>##STABILITY##</api>
    </stability>
    <license uri="http://www.opensource.org/licenses/mit-license.php">MIT</license>
    <notes>-</notes>
    <contents>
        <dir name="/">
            ##SOURCE_FILES##

            <file role="script" baseinstalldir="/" name="bin/behat">
                <tasks:replace from="/usr/bin/env php" to="php_bin" type="pear-config" />
                <tasks:replace from="DEV" to="version" type="package-info" />
            </file>
            <file role="script" baseinstalldir="/" name="bin/behat.bat">
                <tasks:replace from="@php_bin@" to="php_bin" type="pear-config" />
                <tasks:replace from="@bin_dir@" to="bin_dir" type="pear-config" />
            </file>

            <file role="php" baseinstalldir="behat" name="i18n.php" />
            <file role="php" baseinstalldir="behat" name="autoload.php" />
            <file role="php" baseinstalldir="behat" name="autoload_map.php" />
            <file role="php" baseinstalldir="behat" name="behat.yml" />
            <file role="php" baseinstalldir="behat" name="CHANGES.md" />
            <file role="php" baseinstalldir="behat" name="README.md" />
            <file role="php" baseinstalldir="behat" name="LICENSE" />
        </dir>
    </contents>
    <dependencies>
        <required>
            <php>
                <min>5.3.1</min>
            </php>
            <pearinstaller>
                <min>1.4.0</min>
            </pearinstaller>
            <package>
                <name>gherkin</name>
                <channel>pear.behat.org</channel>
                <min>2.2.1</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>Config</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>Console</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>DependencyInjection</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>EventDispatcher</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>Finder</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>Translation</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <package>
                <name>Yaml</name>
                <channel>pear.symfony.com</channel>
                <min>2.0.0</min>
                <max>2.3.0</max>
                <exclude>2.3.0</exclude>
            </package>
            <extension>
                <name>pcre</name>
            </extension>
            <extension>
                <name>simplexml</name>
            </extension>
            <extension>
                <name>xml</name>
            </extension>
            <extension>
                <name>mbstring</name>
            </extension>
        </required>
    </dependencies>
    <phprelease>
        <installconditions>
            <os>
                <name>windows</name>
            </os>
        </installconditions>
        <filelist>
            <install as="behat" name="bin/behat" />
            <install as="behat.bat" name="bin/behat.bat" />
        </filelist>
    </phprelease>
    <phprelease>
        <filelist>
            <install as="behat" name="bin/behat" />
            <ignore name="bin/behat.bat" />
        </filelist>
     </phprelease>
</package>
EOF;
    }
}
