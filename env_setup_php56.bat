@echo off
REM env_setup.bat
REM
REM This batch file initializes the mapguide-rest dev environemnt to match your version/installation of PHP
REM Modify the variables below to match your specific PHP install

SET PHPRC=C:\mg-312-install\Web\Php\php.ini
SET PATH=%PATH%;C:\mg-312-install\Web\Php
SET MG_WEBCONFIG_INI_PATH=C:\mg-312-install\Web\www\webconfig.ini
SET COMPOSER=composer.php5.json
SET MG_REST_ROOT_URL=http://localhost:8018/mapguide/rest

REM SET PHPRC=C:\mg-4.0-install\Web\Php\php.ini
REM SET PATH=%PATH%;C:\mg-4.0-install\Web\Php
REM SET MG_WEBCONFIG_INI_PATH=C:\mg-4.0-install\Web\www\webconfig.ini
REM SET MG_REST_ROOT_URL=http://localhost:8018/mapguide/rest

echo PHP environment set using php.ini from %PHPRC%