@echo off
rem -------------------------------------------------------------
rem  Brilliant migrations maker
rem -------------------------------------------------------------
@setlocal
set BRILLIANT_PATH=%~dp0
if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe
"%PHP_COMMAND%" "%BRILLIANT_PATH%dbschemedump" %*
@endlocal
