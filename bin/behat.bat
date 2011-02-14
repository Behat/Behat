@echo off
REM Behat
REM
REM This file is part of the Behat.
REM (c) Konstantin Kudryashov <ever.zet@gmail.com>
REM
REM For the full copyright and license information, please view the LICENSE
REM file that was distributed with this source code.
REM

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set SCRIPT_DIR=%~dp0

goto init

:init

if "%PHP_COMMAND%" == "" goto no_phpcommand

if "%SCRIPT_DIR%" == "" (
  %PHP_COMMAND% "symfony" %*
) else (
  %PHP_COMMAND% "%SCRIPT_DIR%\behat" %*
)
goto cleanup

:no_phpcommand
rem echo ------------------------------------------------------------------------
rem echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
rem echo          executable (e.g. C:\PHP\php.exe).  (assuming php.exe on PATH)
rem echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
rem pause