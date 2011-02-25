@echo off
REM Behat
REM
REM This file is part of the Behat.
REM (c) Konstantin Kudryashov <ever.zet@gmail.com>
REM
REM For the full copyright and license information, please view the LICENSE
REM file that was distributed with this source code.
REM

if "%PHPBIN%" == "" set PHPBIN=@php_bin@
if not exist "%PHPBIN%" if "%PHP_PEAR_PHP_BIN%" neq "" goto USE_PEAR_PATH
GOTO RUN
:USE_PEAR_PATH
set PHPBIN=%PHP_PEAR_PHP_BIN%
:RUN
"%PHPBIN%" "@bin_dir@\behat" %*