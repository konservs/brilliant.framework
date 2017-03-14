@echo off
rem -------------------------------------------------------------
rem  Brilliant Framework command line init script for Windows.
rem -------------------------------------------------------------
@setlocal
set BRILLIANT_PATH=%~dp0
if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe
"%PHP_COMMAND%" "%BRILLIANT_PATH%init" %*
@endlocal
