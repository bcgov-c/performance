#!/bin/bash
set -e # Exit on error

oc project $OC_PROJECT

helm repo add bitnami https://charts.bitnami.com/bitnami

cat > values.yaml<<EOF
global:
  redis:
    password: $REDIS_PASSWORD
replica:
  replicaCount: $REPLICAS
sentinel:
  enabled: true
EOF

helm install $REDIS_NAME-sentinel bitnami/redis --values values.yml

oc apply -f ./openshift/redis-insight.yml
