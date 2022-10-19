#!/usr/bin/env bash

cd /var/www/html

# Queue 
nohup php artisan queue:work --tries=3 --timeout=0 > ./storage/logs/queue-work.log &

# Schedule (required manual start)
nohup php artisan schedule:work --verbose --no-interaction &