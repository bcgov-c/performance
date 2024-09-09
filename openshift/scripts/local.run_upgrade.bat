@echo off

:: This script is used for tetsing upgrade process in local Windows environment
:: This will put app into maintenance mode, copy files from build to deploy location
:: It will uninstall any missing (removed) plugins prior to upgrade
:: It will run any database upgrades
:: It will also run cron and purge caches

set html-dir="/var/www/html"
set cli-path=%html-dir%/admin/cli
set app-service-name="performance"
set php-container-name="performance-php-1"

set purge-plugins-command="php %cli-path%/uninstall_plugins.php --purge-missing --run"
set maintenance-enable-command="php %cli-path%/maintenance.php --enable"
set maintenance-disable-command="php %cli-path%/maintenance.php --disable"
set upgrade-command="php %cli-path%/upgrade.php --non-interactive"
set cron-command="php %cli-path%/cron.php"

:: Build / upgrade
:: PHP pod
echo "Enabble maintenance mode..."
docker exec -it %php-container-name% sh -c %maintenance-enable-command%

SLEEP 10

:: App pod (will automatically copy files on launch)
echo "Starting app pod... copying files from build to deploy location"
docker-compose up -d %app-service-name%

SLEEP 10

:loop
  timeout /t 1 >nul
  docker-compose ps --all --status=exited | find /i "performance-build"
if errorlevel 1 goto :loop

echo "File deployment complete."

:: PHP Pod
echo "Purge any missing plugins..."
docker exec -it %php-container-name% sh -c %purge-plugins-command%

echo "Running upgrades..."
docker exec -it %php-container-name% sh -c %upgrade-command%

echo "Purge caches..."
docker exec -it %php-container-name% sh -c %purge-command%

echo "Disable maintenance mode..."
docker exec -it %php-container-name% sh -c %maintenance-disable-command%

:: Cron must be run outside of maintenance mode
echo "Run cron..."
docker exec -it %php-container-name% sh -c %cron-command%

echo "Upgrade complete."
