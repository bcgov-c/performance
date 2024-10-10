#!/bin/bash

# Set environment variables
export APP_NAME="${APP_NAME}"
export DB_BACKUP_DEPLOYMENT_NAME="${DB_BACKUP_DEPLOYMENT_NAME}"
export DB_BACKUP_DEPLOYMENT_FULL_NAME="${DB_BACKUP_DEPLOYMENT_FULL_NAME}"
export BACKUP_HELM_CHART="${BACKUP_HELM_CHART}"
export DB_BACKUP_IMAGE="${DB_BACKUP_IMAGE}"
export DB_HOST="${DB_HOST}"
export DB_DATABASE="${DB_DATABASE}"
export DB_PORT="${DB_PORT}"
export DB_HEALTH_QUERY="${DB_HEALTH_QUERY}"
export OC_PROJECT="${OC_PROJECT}"
export CLEAN_PVC="${CLEAN_PVC}"

# Debugging: Print environment variables
echo "DB_BACKUP_DEPLOYMENT_NAME: $DB_BACKUP_DEPLOYMENT_NAME"
echo "APP_NAME: $APP_NAME"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "BACKUP_HELM_CHART: $BACKUP_HELM_CHART"
echo "DB_BACKUP_IMAGE: $DB_BACKUP_IMAGE"
echo "DB_BACKUP_DEPLOYMENT_FULL_NAME: $DB_BACKUP_DEPLOYMENT_FULL_NAME"

# Ensure APP_NAME is set
if [ -z "$APP_NAME" ]; then
  echo "Error: APP_NAME is not set."
  exit 1
fi

oc project $OC_PROJECT

if [[ `oc describe configmap $DB_NAME 2>&1` =~ "NotFound" ]]; then
  # Create configmap from the resources directory
  oc create configmap $DB_NAME --from-file=./openshift/config/mariadb/resources
fi

if [[ `oc describe sts $DB_NAME 2>&1` =~ "NotFound" ]]; then
  echo "$DB_NAME NOT FOUND: Beginning deployment..."
  envsubst < ./openshift/config/mariadb/config.yaml | oc create -f -
else
  echo "$DB_NAME Installation found...Scaling to 0..."
  oc scale sts/$DB_NAME --replicas=0

  ATTEMPTS=0
  MAX_ATTEMPTS=60
  while [[ $(oc get sts $DB_NAME -o jsonpath='{.status.replicas}') -ne 0 && $ATTEMPTS -ne $MAX_ATTEMPTS ]]; do
    echo "Waiting for $DB_NAME to scale to 0..."
    sleep 10
    ATTEMPTS=$((ATTEMPTS + 1))
  done
  if [[ $ATTEMPTS -eq $MAX_ATTEMPTS ]]; then
    echo "Timeout waiting for $DB_NAME to scale to 0"
    exit 1
  fi

  echo "Recreating $DB_NAME from image: $IMAGE_REPO_URL$DB_IMAGE"
  oc delete sts $DB_NAME
  oc delete configmap $DB_NAME
  oc delete service $DB_NAME

  # Create configmap from the resources directory
  oc create configmap $DB_NAME --from-file=./openshift/config/mariadb/resources

  # Substitute variables in the config.yaml file and create the deployment
  envsubst < ./openshift/config/mariadb/config.yaml | oc create -f -

  sleep 10

  oc scale sts/$DB_NAME --replicas=1

  sleep 15

  # Wait for the deployment to scale to 1
  ATTEMPTS=0
  MAX_ATTEMPTS=60
  while [[ $(oc get sts $DB_NAME -o jsonpath='{.status.replicas}') -ne 1 && $ATTEMPTS -ne $MAX_ATTEMPTS ]]; do
    echo "Waiting for $DB_NAME to scale to 1..."
    sleep 10
    ATTEMPTS=$((ATTEMPTS + 1))
  done
  if [[ $ATTEMPTS -eq $MAX_ATTEMPTS ]]; then
    echo "Timeout waiting for $DB_NAME to scale to 1"
    exit 1
  fi
fi

echo "$DB_NAME Database deployment is complete."
