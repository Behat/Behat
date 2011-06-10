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
        <api>1.0.0</api>
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

            <file role="script" baseinstalldir="/" name="bin/behat.php">
                <tasks:replace from="/usr/bin/env php" to="php_bin" type="pear-config" />
                <tasks:replace from="DEV" to="version" type="package-info" />
            </file>

            <file role="script" baseinstalldir="/" name="bin/behat.bat">
                <tasks:replace from="@php_bin@" to="php_bin" type="pear-config" />
                <tasks:replace from="@bin_dir@" to="bin_dir" type="pear-config" />
            </file>

            <file role="php" baseinstalldir="behat" name="autoload.php.dist" />
            <file role="php" baseinstalldir="behat" name="behat.yml" />

            <file role="php" baseinstalldir="behat" name="CHANGES.md" />
            <file role="php" baseinstalldir="behat" name="LICENSE" />
            <file role="php" baseinstalldir="behat" name="README.md" />

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
                <min>1.0.5</min>
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
            <install as="behat" name="bin/behat.php" />
            <install as="behat.bat" name="bin/behat.bat" />
        </filelist>
    </phprelease>
    <phprelease>
        <filelist>
            <install as="behat" name="bin/behat.php" />
            <ignore name="bin/behat.bat" />
        </filelist>
     </phprelease>
</package>
