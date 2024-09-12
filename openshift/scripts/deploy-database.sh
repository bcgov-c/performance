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

echo "Checking if the database is online and contains expected data..."
ATTEMPTS=0
WAIT_TIME=10
MAX_ATTEMPTS=30 # wait up to 5 minutes

# Get the name of the first pod in the StatefulSet
DB_POD_NAME=""
until [ -n "$DB_POD_NAME" ]; do
  ATTEMPTS=$(( $ATTEMPTS + 1 ))
  PODS=$(oc get pods -l app=$DB_NAME --field-selector=status.phase=Running -o jsonpath='{.items[*].metadata.name}')

  if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
    echo "Timeout waiting for the pod to have status.phase:Running. Exiting..."
    exit 1
  fi

  if [ -z "$PODS" ]; then
    echo "No pods in Running state found. Retrying in $WAIT_TIME seconds..."
    sleep $WAIT_TIME
  else
    DB_POD_NAME=$(echo $PODS | awk '{print $1}')
  fi
done

echo "Database pod found and running: $DB_POD_NAME."

ATTEMPTS=0
until [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; do
  ATTEMPTS=$(( $ATTEMPTS + 1 ))
  echo "Waiting for database to come online... $(($ATTEMPTS * $WAIT_TIME)) seconds..."

  # Capture the output of the mariadb command
  OUTPUT=$(oc exec $DB_NAME -- bash -c "mariadb -u root -e 'USE $DB_DATABASE; SELECT COUNT(*) FROM users;'" 2>&1)

  # Check if the output contains an error
  if echo "$OUTPUT" | grep -qi "error"; then
    echo "❌ Database error: $OUTPUT"
    # exit 1
  fi

  # Extract the user count from the output
  CURRENT_USER_COUNT=$(echo "$OUTPUT" | grep -oP '\d+')

  if [ $CURRENT_USER_COUNT -gt 0 ]; then
    echo "Database is online and contains $CURRENT_USER_COUNT users."
    break
  else
    echo "Database is offline. Attempt $ATTEMPTS out of $MAX_ATTEMPTS."
    sleep $WAIT_TIME
  fi
done

if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
  echo "❌ Timeout waiting for the database to be online. Exiting..."
  exit 1
fi

echo "$DB_NAME Database deployment is complete."
