#!/bin/bash

oc project $OC_PROJECT

if [[ `oc describe configmap $DB_SERVICE 2>&1` =~ "NotFound" ]]; then
  # Create configmap from the resources directory
  oc create configmap $DB_SERVICE --from-file=./openshift/config/mariadb/resources
fi

if [[ `oc describe sts $DB_SERVICE 2>&1` =~ "NotFound" ]]; then
  echo "$DB_SERVICE NOT FOUND: Beginning deployment..."
  envsubst < ./openshift/config/mariadb/config.yaml | oc create -f -
else
  echo "$DB_SERVICE Installation found...Scaling to 0..."
  oc scale sts/$DB_SERVICE --replicas=0

  ATTEMPTS=0
  MAX_ATTEMPTS=60
  while [[ $(oc get sts $DB_SERVICE -o jsonpath='{.status.replicas}') -ne 0 && $ATTEMPTS -ne $MAX_ATTEMPTS ]]; do
    echo "Waiting for $DB_SERVICE to scale to 0..."
    sleep 10
    ATTEMPTS=$((ATTEMPTS + 1))
  done
  if [[ $ATTEMPTS -eq $MAX_ATTEMPTS ]]; then
    echo "Timeout waiting for $DB_SERVICE to scale to 0"
    exit 1
  fi

  echo "Recreating $DB_SERVICE from image: $IMAGE_REPO_URL$DB_IMAGE"
  oc delete sts $DB_SERVICE
  oc delete configmap $DB_SERVICE
  oc delete service $DB_SERVICE

  # Create configmap from the resources directory
  oc create configmap $DB_SERVICE --from-file=./openshift/config/mariadb/resources

  # Substitute variables in the config.yaml file and create the deployment
  envsubst < ./openshift/config/mariadb/config.yaml | oc create -f -

  sleep 10

  oc scale sts/$DB_SERVICE --replicas=1

  sleep 15

  # Wait for the deployment to scale to 1
  ATTEMPTS=0
  MAX_ATTEMPTS=60
  while [[ $(oc get sts $DB_SERVICE -o jsonpath='{.status.replicas}') -ne 1 && $ATTEMPTS -ne $MAX_ATTEMPTS ]]; do
    echo "Waiting for $DB_SERVICE to scale to 1..."
    sleep 10
    ATTEMPTS=$((ATTEMPTS + 1))
  done
  if [[ $ATTEMPTS -eq $MAX_ATTEMPTS ]]; then
    echo "Timeout waiting for $DB_SERVICE to scale to 1"
    exit 1
  fi
fi

echo "$DB_SERVICE Database deployment is complete."
