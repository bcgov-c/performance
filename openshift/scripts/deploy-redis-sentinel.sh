#!/bin/bash
set -e # Exit on error

oc project $OC_PROJECT

helm repo add bitnami https://charts.bitnami.com/bitnami

# Create a temporary values file
cat <<EOF > values.yml
global:
  redis:
    password: "$REDIS_PASSWORD"
replica:
  replicaCount: $REDIS_REPLICAS
sentinel:
  enabled: true
EOF

# Check if the Helm deployment exists
if helm list -q | grep -q "^$REDIS_NAME$"; then
  echo "Helm deployment found. Updating..."

  # Upgrade the Helm deployment with the new values
  if [[ `helm upgrade $REDIS_NAME $REDIS_HELM_CHART --reuse-values -f values.yml 2>&1` =~ "Error" ]]; then
    echo "❌ Helm upgrade FAILED."
    exit 1
  fi

  if [[ `oc describe deployment $REDIS_NAME 2>&1` =~ "NotFound" ]]; then
    echo "Helm deployment ($REDIS_NAME) exists, but NOT FOUND."
    exit 1
  fi
else
  echo "Helm $REDIS_NAME NOT FOUND. Beginning deployment..."

  helm install $REDIS_NAME-sentinel $REDIS_HELM_CHART --values values.yml
fi

echo "Helm updates completed for $REDIS_NAME."

# Set best-effort resource limits for the deployment
echo "Setting best-effort resource limits for the deployment..."
oc set resources deployment/$REDIS_NAME --limits=cpu=0,memory=0 --requests=cpu=0,memory=0

# Clean up the temporary values file
rm values.yaml

e ho "Deploying Redis Insight..."
oc apply -f ./openshift/redis-insight.yml
