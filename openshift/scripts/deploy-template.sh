#!/bin/bash
#set -e # Exit on error

test -n $DEPLOY_NAMESPACE
oc project $DEPLOY_NAMESPACE
echo "Current namespace is $DEPLOY_NAMESPACE"
echo "Deploying site: $SITE_URL"

echo "Delete cron job if it exists..."
# Check if cron exists
if oc get deployment $CRON_NAME; then
  echo "$CRON_NAME Installation FOUND...Deleting..."
  oc delete deployment $CRON_NAME
fi

# Only use 1 db replica for deployment / upgrade to avoid conflicts
echo "Scale down $DB_SERVICE to 1 replica..."
oc scale sts/$DB_SERVICE --replicas=1

# Only use 1 redis replica for deployment / upgrade to avoid conflicts
echo "Scale down $REDIS_NAME to 1 replica..."
oc scale sts/$REDIS_NAME --replicas=1

# Create ConfigMaps (first delete, if necessary)
if [[ ! `oc describe configmap $WEB_NAME-config 2>&1` =~ "NotFound" ]]; then
  echo "ConfigMap exists... Deleting: $WEB_NAME-config"
  oc delete configmap $WEB_NAME-config
fi

sleep 5

echo "Creating configMap: $WEB_NAME-config"
oc create configmap $WEB_NAME-config --from-file=./openshift/config/nginx/default.conf

if [[ ! `oc describe configmap $APP_NAME-config 2>&1` =~ "NotFound" ]]; then
  echo "ConfigMap exists... Deleting: $APP_NAME-config"
  oc delete configmap $APP_NAME-config
fi

sleep 5

echo "Checking for: deployment/$WEB_NAME in $DEPLOY_NAMESPACE"

if [[ `oc describe deployment/$WEB_NAME 2>&1` =~ "NotFound" ]]; then
  echo "$WEB_NAME NOT FOUND..."
else
  echo "$WEB_NAME installation found...updating..."
  oc annotate --overwrite  deployment/$WEB_NAME kubectl.kubernetes.io/restartedAt=`date +%FT%T`
fi

sleep 10

echo "Deploy Template to OpenShift ..."

oc process -f ./openshift/template.json \
  -p APP_NAME=$APP_NAME \
  -p DB_USER=$DB_USER \
  -p DB_PASSWORD=$DB_PASSWORD \
  -p SITE_URL=$SITE_URL \
  -p APP_HOST_URL=$APP_HOST_URL \
  -p BUILD_NAMESPACE=$BUILD_NAMESPACE \
  -p DEPLOY_NAMESPACE=$DEPLOY_NAMESPACE \
  -p IMAGE_REPO_URL=$IMAGE_REPO_URL \
  -p WEB_NAME=$WEB_NAME \
  -p WEB_IMAGE=$WEB_IMAGE \
  -p CRON_NAME=$CRON_NAME \
  -p PHP_NAME=$PHP_NAME \
  -p IMAGE_PULL_SECRET_NAME=$IMAGE_PULL_SECRET_NAME | \
oc apply -f -

# Only use 1 db replica for deployment / upgrade to avoid conflicts
echo "Scale down $DB_SERVICE to 1 replica..."
oc scale sts/$DB_SERVICE --replicas=1

sleep 15

# Check PHP deployment rollout status until complete.
ATTEMPTS=0
WAIT_TIME=30
ROLLOUT_STATUS_CMD="oc rollout status deployments/$PHP_NAME"
until $ROLLOUT_STATUS_CMD || [ $ATTEMPTS -eq 6 ]; do
  $ROLLOUT_STATUS_CMD
  ATTEMPTS=$((attempts + 1))
  echo "Waiting for deployments/$PHP_NAME: $(($ATTEMPTS * $WAIT_TIME)) seconds..."
  sleep $WAIT_TIME
done

# Check if the upgrade job exists, if so, delete it
if [[ `oc describe job $APP_NAME-upgrade 2>&1` =~ "NotFound" ]]; then
  echo "$APP_NAME-upgrade job NOT FOUND..."
else
  echo "$APP_NAME-upgrade job found... deleting..."
  oc delete job $APP_NAME-upgrade
fi

# Check if the migrate-build-files job exists, if so, delete it
if [[ `oc describe job migrate-build-files 2>&1` =~ "NotFound" ]]; then
  echo "migrate-build-files job NOT FOUND..."
else
  echo "migrate-build-files job FOUND...Deleting..."
  oc delete job/migrate-build-files
fi

sleep 10

echo "Create and run migrate-build-files job..."
oc process -f ./openshift/migrate-build-files.yml \
  -p APP_NAME=$APP_NAME \
  -p IMAGE_REPO_URL=$IMAGE_REPO_URL \
  -p IMAGE_PULL_SECRET_NAME=$IMAGE_PULL_SECRET_NAME \
  -p BUILD_NAME=$PHP_NAME \
  -p BUILD_NAMESPACE=$BUILD_NAMESPACE \
  | oc create -f -

sleep 10

# Get the name of the pod created by the job
pod_name=$(oc get pods --selector=job-name=migrate-build-files -o jsonpath='{.items[0].metadata.name}')

# Wait until the pod is in the "Running" state
while [[ $(oc get pod $pod_name -o 'jsonpath={..status.phase}') != "Running" ]]; do
  echo "Waiting for pod $pod_name to be running."
  sleep 30
done

# Wait for the migrate-build-files job to complete
echo "Pod $pod_name is now running."

echo "Waiting for $pod_name job to complete..."

sleep 60

COUNT=0
SLEEP=10
while true; do
  # Make sure we have the most current name of the pod created by the job
  job_status=$(oc get jobs migrate-build-files -o 'jsonpath={..status.failed}')
  pod_name=$(oc get pods --selector=job-name=migrate-build-files -o jsonpath='{.items[0].metadata.name}')
  message=$(oc logs $pod_name)
  if [[ $job_status > 0 ]]; then
    echo "migrate-build-files job has failed... Exiting due to error: $message"
    exit 1
  fi
  if [[ $(oc get jobs migrate-build-files -o 'jsonpath={..status.active}') != "1" ]]; then
    break
  fi
  echo "migrate-build-files job is still running... $(($COUNT * $SLEEP + 60)) seconds..."
  COUNT=$((COUNT + 1))
  sleep $SLEEP
done
echo "migrate-build-files job has completed."

sleep 15

# DB was scaled-down for deployment and maintenance, scale it back up
echo "Scaling up $DB_SERVICE to 3 replicas..."
oc scale sts/$DB_SERVICE --replicas=3

# Right-sizing cluster, according to environment
bash ./openshift/scripts/right-sizing.sh

sleep 30

echo "Deployment complete."

# Wait for things to warm up a bit before proceeding with the [lighthouse] tests
sleep 120
