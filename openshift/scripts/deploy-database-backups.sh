#!/bin/bash

# Function to list available backups
list_backups() {
  echo "Listing available backups..."

  # Connect to the backup pod and list available backups
  BACKUP_LIST=$(oc exec $(oc get pod -l app.kubernetes.io/name=backup-storage -o jsonpath='{.items[0].metadata.name}') -- ./backup.sh -l)

  # Parse the backup list into an array
  IFS=$'\n' read -rd '' -a BACKUP_ARRAY <<< "$BACKUP_LIST"

  # Filter and sort backups
  FILTERED_SORTED_BACKUPS=$(for line in "${BACKUP_ARRAY[@]}"; do
    # Extract size, date, and filename
    SIZE=$(echo "$line" | awk '{print $1}')
    DATE=$(echo "$line" | awk '{print $2 " " $3}')
    FILENAME=$(echo "$line" | awk '{print $4}')

    # Convert size to bytes for comparison
    SIZE_IN_BYTES=$(echo "$SIZE" | awk '
      /M$/ { printf "%.0f\n", $1 * 1024 * 1024 }
      /K$/ { printf "%.0f\n", $1 * 1024 }
      /G$/ { printf "%.0f\n", $1 * 1024 * 1024 * 1024 }
      !/[KMG]$/ { print $1 }
    ')

    # Only include entries with size > 1M
    if [ "$SIZE_IN_BYTES" -gt $((1 * 1024 * 1024)) ]; then
      echo "$SIZE $DATE $FILENAME"
    fi
  done | sort -k2,3r)

  # Select the latest backup
  LATEST_BACKUP=$(echo "$FILTERED_SORTED_BACKUPS" | head -n 1)

  # Output the size, date, and filename for the selected entry
  echo "Selected Backup:"
  echo "$LATEST_BACKUP"

  # Return the filename of the selected backup
  echo "$LATEST_BACKUP" | awk '{print $3}'
}

# Function to restore the backup by filename
restore_backup() {
  local FILENAME=$1
  echo "Restoring backup from file: $FILENAME"

  # Check the file extension and run the appropriate restore command
  if [[ "$FILENAME" == *.gz ]]; then
    # Run the restore command for .gz files
    oc exec $(oc get pod -l app.kubernetes.io/name=backup-storage -o jsonpath='{.items[0].metadata.name}') -- ./backup.sh -r mariadb/$DB_DATABASE -f "$FILENAME"
  elif [[ "$FILENAME" == *.sql ]]; then
    # Run the SQL restore command for .sql files
    oc exec $(oc get pod -l app.kubernetes.io/name=backup-storage -o jsonpath='{.items[0].metadata.name}') -- bash -c "mysql -h $DB_NAME -u root performance < $FILENAME"
  else
    echo "Unsupported file type: $FILENAME"
    exit 1
  fi
}

oc project $OC_PROJECT

helm repo add bcgov http://bcgov.github.io/helm-charts
helm repo update

echo "Deploying database backups to: $DB_BACKUP_DEPLOYMENT_NAME..."

# Check if the Helm deployment exists
if helm list -q | grep -q "^$DB_BACKUP_DEPLOYMENT_NAME$"; then
  echo "Helm deployment found. Updating..."

  # Create a temporary values file with the updated backupConfig
  cat <<EOF > temp-values.yaml
backupConfig: |
  mariadb=$DB_HOST:$DB_PORT/$DB_DATABASE
  0 1 * * * default ./backup.sh -s
  0 4 * * * default ./backup.sh -s -v all
EOF

  # Upgrade the Helm deployment with the new values
  if [[ `helm upgrade $DB_BACKUP_DEPLOYMENT_NAME $BACKUP_HELM_CHART --reuse-values -f temp-values.yaml 2>&1` =~ "Error" ]]; then
    echo "Backup container update FAILED."
    exit 1
  fi

  # Clean up the temporary values file
  rm temp-values.yaml

  if [[ `oc describe deployment $DB_BACKUP_DEPLOYMENT_FULL_NAME 2>&1` =~ "NotFound" ]]; then
    echo "Backup Helm exists, but deployment NOT FOUND."
    exit 1
  else
    echo "Backup deployment FOUND. Updating..."
    oc set image deployment/$DB_BACKUP_DEPLOYMENT_FULL_NAME backup-storage=$DB_BACKUP_IMAGE
  fi
  echo "Backup container updates completed."
else
  echo "Helm $DB_BACKUP_DEPLOYMENT_NAME NOT FOUND. Beginning deployment..."
  echo "
    image:
      repository: \"$BACKUP_HELM_CHART\"
      pullPolicy: Always
      tag: dev

    persistence:
      backup:
        accessModes: [\"ReadWriteMany\"]
        storageClassName: netapp-file-standard
      verification:
        storageClassName: netapp-file-standard

    backupConfig: |
      mariadb=$DB_HOST:$DB_PORT/$DB_DATABASE
      0 1 * * * default ./backup.sh -s
      0 4 * * * default ./backup.sh -s -v all

    db:
      secretName: $APP_NAME-secrets
      usernameKey: database-user
      passwordKey: database-password

    env:
      DATABASE_SERVICE_NAME:
        value: \"$DB_HOST\"
      ENVIRONMENT_FRIENDLY_NAME:
        value: \"$APP_NAME Backups\"
    " > backup-config.yaml
  helm install $DB_BACKUP_DEPLOYMENT_NAME $BACKUP_HELM_CHART --atomic --wait -f backup-config.yaml
  oc set image deployment/$DB_BACKUP_DEPLOYMENT_FULL_NAME backup-storage=$BACKUP_IMAGE
fi

sleep 15

echo "Checking if the database ($DB_NAME) is online and contains expected data..."
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
    echo "No pods found in Running state ($PODS). Retrying in $WAIT_TIME seconds..."
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
  OUTPUT=$(oc exec $DB_POD_NAME -- bash -c "mariadb -u root -e 'USE $DB_DATABASE; $DB_HEALTH_QUERY;'" 2>&1)

  # Check if the output contains an error
  if echo "$OUTPUT" | grep -qi "error"; then
    echo "❌ Database error: $OUTPUT"
    # exit 1
  fi

  # Extract the user count from the output
  CURRENT_USER_COUNT=$(echo "$OUTPUT" | grep -oP '\d+')

  # Check if CURRENT_USER_COUNT is set and greater than 0
  if [ -n "$CURRENT_USER_COUNT" ] && [ "$CURRENT_USER_COUNT" -gt 0 ]; then
    echo "Database is online and contains $CURRENT_USER_COUNT users."
    echo "No further action required."
    break
  else if [ -n "$CURRENT_USER_COUNT" ] && [ "$CURRENT_USER_COUNT" -eq 0 ]; then
    echo "Database is online but contains no users."

    # Main script execution
    echo "Starting backup restoration process..."
    # List backups and get the filename of the latest backup
    LATEST_BACKUP_FILENAME=$(list_backups)
    # Restore the backup using the filename
    restore_backup "$LATEST_BACKUP_FILENAME"
    echo "Backup restoration process completed."

    break
  else
    # Current user count is 0 or not set
    # echo "Database appears to be offline. Attempt $ATTEMPTS of $MAX_ATTEMPTS."
    sleep $WAIT_TIME
  fi
done
