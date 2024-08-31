@echo off

:: This script is used for tetsing upgrade process in local Windows environment
:: This will send a request to each pod (php, moodle) to run the installed shell script
:: The combines scripts will
::  1. Put moodle into maintenance mode
::  2. Copy files from build to deploy location
::  3. Run moodle upgrades
:: It will uninstall any missing (removed) plugins prior to upgrade
:: It will run any database upgrades
:: It will also run cron and purge caches

set app=performance
set service-name=%app%
@REM set php-container-name=%app%-%app%-1

set migrate-build-files-command=/usr/local/bin/migrate-build-files.sh
set test-migration-complete-command=/usr/local/bin/test-migration-complete.sh
@REM set upgrade-command=/usr/local/bin/moodle-upgrade.sh

@REM docker-compose exec performance sh -c /usr/local/bin/migrate-build-files.sh

:: Build / upgrade moodle
:: PHP pod
@REM echo Enabble maintenance mode on %php-container-name%...
@REM docker exec -it %php-container-name% sh -c %enable-maintenance-command%

@REM SLEEP 10

:: Moodle pod
echo Starting pod: (%service-name%)...
docker-compose up -d %service-name%

SLEEP 10

echo Migrating files (%service-name%)...
docker-compose exec %service-name% sh -c %migrate-build-files-command%

SLEEP 30

echo Testing for completion of file migration...

:: Test for file migration to complete (once config.php exists)
:loop
  timeout /t 1 >nul
  docker-compose exec %service-name% sh -c %test-migration-complete-command%  | find /i "bin"
if errorlevel 1 goto :loop

echo File migration complete.

SLEEP 2

:: PHP pod
@REM echo Upgrading database (%php-container-name%)...
@REM docker exec -it %php-container-name% sh -c %upgrade-command%

echo Upgrade complete.
