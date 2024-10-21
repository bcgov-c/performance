#!/bin/bash
export REDISCLI_AUTH=$SECRET_REDIS_PASSWORD
export REDIS_HOST=$(oc get svc redis-sentinel -o jsonpath='{.spec.clusterIP}')
while true
do
    CURRENT_PRIMARY=$(redis-cli -h $REDIS_HOST -p 26379 SENTINEL get-master-addr-by-name mymaster)
    CURRENT_PRIMARY_HOST=$(echo $CURRENT_PRIMARY | cut -d' ' -f1 | head -n 1)
    echo "Current master's host: $CURRENT_PRIMARY_HOST"
    redis-cli -h ${CURRENT_PRIMARY_HOST} -p 6379 INCR mycounter
    sleep 1
done
