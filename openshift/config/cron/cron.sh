#!/usr/bin/env bash

cd /var/www/html

echo "List of Scheduled Jobs:"
php artisan schedule:list

while true
do
  # Run Scheduled Jobs
  date_time=$(date +"%Y-%m-%d %H:%M:%S")
  echo "Running Scheduled Jobs [$date_time]"
  php artisan schedule:run --verbose --no-interaction </dev/null>/dev/null 2>&1 &
  # nohup php artisan queue:work  --daemon --tries=3 --timeout=0 > ./storage/logs/queue-work.log </dev/null>/dev/null 2>&1 &
  #nohup php artisan schedule:work --verbose --no-interaction </dev/null>/dev/null 2>&1 &
  sleep 60
done
