#!/bin/bash

# Ensure APP_NAME is set
if [ -z "$APP_NAME" ]; then
  echo "❌ Error: APP_NAME is not set. Cannot deploy database-backups."
  exit 1
fi

# Ensure the environment variables are set
if [ -z "$DB_NAME" ]; then
  echo "Error: DB_NAME environment variable is not set."
  exit 1
fi

# Write the value of DB_NAME to a temporary file for debugging
TEMP_FILE=$(mktemp)
echo "$DB_NAME" > "$TEMP_FILE"
echo "DB_NAME: $DB_NAME"
echo "DB_NAME value written to temporary file: $TEMP_FILE"
DB_NAME_TEST=$(cat "$TEMP_FILE")
echo "Restoring database to: $DB_NAME_TEST"

echo "Deploying database backups for $APP_NAME to: $DB_BACKUP_DEPLOYMENT_NAME..."

# Debugging: Print environment variables
echo "DB_BACKUP_DEPLOYMENT_NAME: $DB_BACKUP_DEPLOYMENT_NAME"
echo "APP_NAME: $APP_NAME"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_NAME: $DB_NAME"
echo "BACKUP_HELM_CHART: $BACKUP_HELM_CHART"
echo "DB_BACKUP_IMAGE: $DB_BACKUP_IMAGE"
echo "DB_BACKUP_DEPLOYMENT_FULL_NAME: $DB_BACKUP_DEPLOYMENT_FULL_NAME"

echo "Deploying database backups to: $DB_BACKUP_DEPLOYMENT_NAME..."

# Function to convert human-readable size to bytes
convert_to_bytes() {
  local SIZE=$1
  local SIZE_IN_BYTES

  case "${SIZE: -1}" in
    K|k)
      SIZE_IN_BYTES=$(echo "${SIZE%?} * 1024" | bc 2>/dev/null)
      ;;
    M|m)
      SIZE_IN_BYTES=$(echo "${SIZE%?} * 1024 * 1024" | bc 2>/dev/null)
      ;;
    G|g)
      SIZE_IN_BYTES=$(echo "${SIZE%?} * 1024 * 1024 * 1024" | bc 2>/dev/null)
      ;;
    *)
      SIZE_IN_BYTES=$(echo "$SIZE" | bc 2>/dev/null)
      ;;
  esac

  # Check if SIZE_IN_BYTES is a valid number
  if ! [[ "$SIZE_IN_BYTES" =~ ^[0-9]+$ ]]; then
    # echo "❌ Invalid file size: $SIZE" >&2
    SIZE_IN_BYTES=0
  fi

  echo $SIZE_IN_BYTES
}

# Function to extract and display backup information
extract_backup_info() {
  local BACKUP_LIST=$1

  # Extract database name and current size
  DATABASE_NAME=$(echo "$BACKUP_LIST" | awk '/Database/ {getline; print $1}')
  CURRENT_SIZE=$(echo "$BACKUP_LIST" | awk '/Database/ {getline; print $2}')

  # Extract size, used, avail, use%, and mounted on
  SIZE=$(echo "$BACKUP_LIST" | awk '/Filesystem/ {getline; print $2}')
  USED=$(echo "$BACKUP_LIST" | awk '/Filesystem/ {getline; print $3}')
  AVAIL=$(echo "$BACKUP_LIST" | awk '/Filesystem/ {getline; print $4}')
  USE_PERCENT=$(echo "$BACKUP_LIST" | awk '/Filesystem/ {getline; print $5}')
  MOUNTED_ON=$(echo "$BACKUP_LIST" | awk '/Filesystem/ {getline; print $6}')

  # Display extracted information
  echo "Database: $DATABASE_NAME" >&2
  echo "Current Size: $CURRENT_SIZE" >&2
  echo "Size: $SIZE" >&2
  echo "Used: $USED" >&2
  echo "Avail: $AVAIL" >&2
  echo "Use%: $USE_PERCENT" >&2
  echo "Mounted on: $MOUNTED_ON" >&2

  # Prepend mounted on value to DB_INIT_FILE_LOCATION
  DB_INIT_FILE_LOCATION="$MOUNTED_ON/$DB_INIT_FILE_LOCATION"
  echo "Updated DB_INIT_FILE_LOCATION: $DB_INIT_FILE_LOCATION" >&2

  # Add notice if Use% is greater than 70% or less than 1%
  USE_PERCENT_VALUE=$(echo "$USE_PERCENT" | tr -d '%')
  if [ "$USE_PERCENT_VALUE" -gt 70 ]; then
    echo "⚠️ Warning: Use% is greater than 70%." >&2
  elif [ "$USE_PERCENT_VALUE" -lt 1 ]; then
    echo "🚫 Notice: Use% is less than 1%." >&2
  fi
}

# Function to restore the backup by filename
restore_backup_from_file() {
  local FILENAME=$1
  local POD_NAME=$2
  echo "Restoring backup from file: $FILENAME"

  # Check the file extension and run the appropriate restore command
  if [[ "$FILENAME" == *.gz ]]; then
    # Run the restore command for .gz files
    oc exec $(oc get pod -l app.kubernetes.io/name=$POD_NAME -o jsonpath='{.items[0].metadata.name}') -- ./backup.sh -r $DB_SERVICE/$DB_NAME -f "$FILENAME"
  elif [[ "$FILENAME" == *.sql ]]; then
    # Run the SQL restore command for .sql files
    oc exec $(oc get pod -l app.kubernetes.io/name=$POD_NAME -o jsonpath='{.items[0].metadata.name}') -- bash -c "mysql -h $DB_HOST -u root $DB_NAME < $FILENAME"
  else
    echo "❌ Unsupported file type: $FILENAME. Restore DB failed."
  fi

  echo "Restore database from file process complete."
}

# Function to get the backup pod name
get_pod() {
  local POD_NAME=$1
  local POD=""
  local WAIT_TIME=10
  local ATTEMPTS=0
  local MAX_ATTEMPTS=30

  until [ -n "$POD" ]; do
    ATTEMPTS=$(( $ATTEMPTS + 1 ))
    POD=$(oc get pod -l app.kubernetes.io/name=$POD_NAME -o jsonpath='{.items[0].metadata.name} 2>/dev/null')

    if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
      echo "Timeout waiting for the [$POD_NAME] pod to be running." >&2
      exit 1
    fi

    if [ -z "$POD" ]; then
      # echo "No pods found in Running state ($POD). Retrying in $WAIT_TIME seconds..."
      sleep $WAIT_TIME
    fi
  done

  echo "$POD"
}

# Function to list available backups and return the latest valid backup file path
list_backups() {
  # Connect to the backup pod and list available backups
  # echo "Checking if the database ($DB_HOST) is online and contains expected data..."
  ATTEMPTS=0
  WAIT_TIME=10
  MAX_ATTEMPTS=30 # wait up to 5 minutes

  BACKUP_POD=$(get_pod backup-storage)
  echo "Backup pod is running: $BACKUP_POD." >&2

  BACKUP_LIST=$(oc exec $BACKUP_POD -- ./backup.sh -l)

  # echo "Backup list: $BACKUP_LIST" >&2

  # Extract and display backup information
  extract_backup_info "$BACKUP_LIST"

  echo "Testing backup file location: $DB_INIT_FILE_LOCATION" >&2

  # Initialize an array to store valid backup entries
  declare -a VALID_BACKUPS

  # Parse each line of the backup list using a here string to avoid subshell issues
  while IFS= read -r line; do
    # Extract size, date-time, and file path
    SIZE=$(echo "$line" | awk '{print $1}')
    DATE=$(echo "$line" | awk '{print $2}')
    TIME=$(echo "$line" | awk '{print $3}')
    FILE_PATH=$(echo "$line" | awk '{print $4}')

    # Concatenate date and time with a colon
    DATE_TIME="${DATE}:${TIME}"

    if [[ "$FILE_PATH" =~ \.(gz|sql|sql\.gz)$ ]]; then
      # Convert size to bytes for comparison
      SIZE_IN_BYTES=$(convert_to_bytes "$SIZE")

      # Check if size is greater than 1M (1048576 bytes)
      if [[ "$SIZE_IN_BYTES" -gt 1048576 ]]; then
        echo "✔️ Valid backup found: Size=$SIZE, Date-Time=$DATE_TIME, File-Path=$FILE_PATH" >&2
        VALID_BACKUPS+=("$SIZE $DATE_TIME $FILE_PATH")
      fi
    else
      continue
    fi
  done <<< "$BACKUP_LIST"

  # Echo the value of VALID_BACKUPS
  echo "VALID_BACKUPS: ${VALID_BACKUPS[*]}" >&2

  # Sort the valid backups array by date-time
   if [ ${#VALID_BACKUPS[@]} -eq 0 ]; then
    echo "No valid backups found." >&2
    return 1
  fi

  IFS=$'\n' sorted_backups=($(sort -k2,3 <<<"${VALID_BACKUPS[*]}"))
  unset IFS

  # Debugging output
  echo "Sorted backups: ${sorted_backups[*]}" >&2

  # Get the latest valid backup file path
  LATEST_BACKUP=$(echo "${sorted_backups[-1]}" | awk '{print $3}')

  echo "Latest valid backup: $LATEST_BACKUP" >&2

  # Return the latest valid backup file path
  echo "$LATEST_BACKUP"
}

restore_database_from_backup() {
  echo "Attempting to restore the database from the latest backup..."

  # List backups and get the filename of the latest backup
  echo "Listing available backups..."
  LATEST_BACKUP_FILENAME=$(list_backups)

  # Check if the file exists on the pod
  BACKUP_POD=$(get_pod backup-storage)

  FILE_TEST=$(oc exec $BACKUP_POD -- ls "$LATEST_BACKUP_FILENAME" 2>&1)
  if echo "$FILE_TEST" | grep -qi "terminated"; then
    if echo "$FILE_TEST" | grep -qi "No such file"; then
      echo "File ($LATEST_BACKUP_FILENAME) not found on pod: $BACKUP_POD." >&2
    else
      echo "❌ Error: $FILE_TEST" >&2
    fi
  else
    # Restore the backup using the filename
    restore_backup_from_file "$LATEST_BACKUP_FILENAME" backup-storage
  fi
}

oc project $OC_PROJECT

helm repo add bcgov http://bcgov.github.io/helm-charts
helm repo update

# Check if the Helm deployment exists
if helm list -q | grep -q "^$DB_BACKUP_DEPLOYMENT_NAME$"; then
  echo "Helm deployment found. Updating..."

  # Create a temporary values file with the updated backupConfig
  cat <<EOF > temp-values.yaml
backupConfig: |
  mariadb=$DB_HOST:$DB_PORT/$DB_NAME
  0 1 * * * default ./backup.sh -s
  0 4 * * * default ./backup.sh -s -v all
EOF

  # Upgrade the Helm deployment with the new values
  if [[ `helm upgrade $DB_BACKUP_DEPLOYMENT_NAME $BACKUP_HELM_CHART --reuse-values -f temp-values.yaml 2>&1` =~ "Error" ]]; then
    echo "❌ Backup container update FAILED."
    exit 1
  fi

  # Clean up the temporary values file
  rm temp-values.yaml

  if [[ `oc describe deployment $DB_BACKUP_DEPLOYMENT_FULL_NAME 2>&1` =~ "NotFound" ]]; then
    echo "Backup Helm exists, but deployment NOT FOUND."
    exit 1
  else
    echo "Backup deployment FOUND. Updating..."
    oc set image deployment/$DB_BACKUP_DEPLOYMENT_FULL_NAME backup-storage=$BACKUP_IMAGE
  fi
  echo "✔️ Backup container updates completed."
else
  echo "Helm $DB_BACKUP_DEPLOYMENT_NAME NOT FOUND. Beginning deployment..."

  # Create a temporary values file for backupConfig
  cat <<EOF > backup-config.yaml
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
  mariadb=$DB_HOST:$DB_PORT/$DB_NAME
  0 1 * * * default ./backup.sh -s
  0 4 * * * default ./backup.sh -s -v all

db:
  secretName: $APP_NAME-secrets
  usernameKey: database-user
  passwordKey: database-password
  databaseKey: database-name

env:
  DATABASE_SERVICE_NAME:
    value: \"$DB_HOST\"
  ENVIRONMENT_FRIENDLY_NAME:
    value: \"$APP_NAME Backups\"
EOF
  # helm install $DB_BACKUP_DEPLOYMENT_NAME $BACKUP_HELM_CHART --atomic --wait -f backup-config.yaml
  helm install $DB_BACKUP_DEPLOYMENT_NAME $BACKUP_HELM_CHART -f backup-config.yaml
  echo "Waiting for backup installation..."
  # Clean up the temporary values file
  rm backup-config.yaml
  # For some reason the defaault image doesn't work, and we prefer the mariadb image anyway
  echo "Setting backup deployment image to: $BACKUP_IMAGE ..."
  oc set image deployment/$DB_BACKUP_DEPLOYMENT_FULL_NAME backup-storage=$BACKUP_IMAGE
  # Set best-effort resource limits for the backup deployment
  echo "Setting best-effort resource limits for the backup deployment..."
  oc set resources deployment/$DB_BACKUP_DEPLOYMENT_FULL_NAME --limits=cpu=0,memory=0 --requests=cpu=0,memory=0
fi

# Function to wait for a statefulset to be ready
wait_for_statefulset() {
  local STATEFULSET_NAME=$1
  local ATTEMPTS=0
  local MAX_ATTEMPTS=30
  local WAIT_TIME=10

  until oc rollout status statefulset/$STATEFULSET_NAME | grep -q "successfully rolled out"; do
    ATTEMPTS=$((ATTEMPTS + 1))

    if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
      echo "Timeout waiting for the $STATEFULSET_NAME statefulset to be ready."
      exit 1
    fi

    echo "Waiting for statefulset $STATEFULSET_NAME to be ready. Retrying in $WAIT_TIME seconds..."
    sleep $WAIT_TIME
  done

  echo "✔️ Statefulset $STATEFULSET_NAME is ready."
}

# Function to wait for a deployment to be ready
wait_for_rollout() {
  local DEPLOYMENT_NAME=$1
  local DEPLOYMENT_TYPE=$2
  local ATTEMPTS=0
  local MAX_ATTEMPTS=30
  local WAIT_TIME=10

  until oc rollout status $DEPLOYMENT_TYPE/$DEPLOYMENT_NAME | grep -q "successfully rolled out"; do
    ATTEMPTS=$((ATTEMPTS + 1))

    if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
      echo "Timeout waiting for the $DEPLOYMENT_NAME deployment to be ready."
      exit 1
    fi

    echo "Waiting for $DEPLOYMENT_TYPE/$DEPLOYMENT_NAME to be ready. Retrying in $WAIT_TIME seconds..."
    sleep $WAIT_TIME
  done

  echo "✔️ Roll-out of $DEPLOYMENT_NAME is ready."
}

echo "Checking if the database ($DB_HOST) is online and contains expected data..."

ATTEMPTS=0
WAIT_TIME=10
MAX_ATTEMPTS=30 # wait up to 5 minutes

# Get the name of the first pod in the StatefulSet
DB_POD_NAME=""
until [ -n "$DB_POD_NAME" ]; do
  ATTEMPTS=$(( $ATTEMPTS + 1 ))
  PODS=$(oc get pods -l app=$DB_HOST --field-selector=status.phase=Running -o jsonpath='{.items[*].metadata.name}')

  if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
    echo "Timeout waiting for the pod to have status.phase:Running. Exiting..."
    exit 1
  fi

  if [ -z "$PODS" ]; then
    echo "No [app=$DB_HOST] pods found in Running state. Retrying in $WAIT_TIME seconds..."
    sleep $WAIT_TIME
  else
    DB_POD_NAME=$(echo $PODS | awk '{print $1}')
  fi
done

echo "Database pod found and running: $DB_POD_NAME."

TOTAL_USER_COUNT=0
CURRENT_USER_COUNT=0
DATABASE_IS_ONLINE=0
ATTEMPTS=0
OUTPUT=""
until [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; do
  ATTEMPTS=$(( $ATTEMPTS + 1 ))
  echo "Waiting for database to come online... $(($ATTEMPTS * $WAIT_TIME)) seconds..."

  # Capture the output of the mariadb command
  OUTPUT=$(oc exec $DB_POD_NAME -- bash -c "mariadb -u root -e 'USE $DB_NAME; $DB_HEALTH_QUERY;'" 2>&1)
  # Debugging: Print the output of the mariadb command
  # echo "Mariadb command output: $OUTPUT"

  # Check if the output contains an error
  if echo "$OUTPUT" | grep -qi "error"; then
    if echo "$OUTPUT" | grep -qi "doesn't exist"; then
      echo "Database not found."
    else
      echo "❌ Database error: $OUTPUT"
    fi

    CURRENT_USER_COUNT=0
  else
    # Extract the user count from the output
    CURRENT_USER_COUNT=$(echo "$OUTPUT" | grep -oP '\d+')
    # Debugging: Print the current user count
    echo "Current user count: $CURRENT_USER_COUNT"
  fi

  echo "Validate user count: $CURRENT_USER_COUNT"

  # Check if CURRENT_USER_COUNT is set and greater than 0
  if [ -n "$CURRENT_USER_COUNT" ] && [ "$CURRENT_USER_COUNT" -gt 0 ]; then
    echo "Database is online and contains $CURRENT_USER_COUNT users."
    TOTAL_USER_COUNT=$CURRENT_USER_COUNT
    break
  elif [ -n "$CURRENT_USER_COUNT" ] && [ "$CURRENT_USER_COUNT" -eq 0 ]; then
    echo "Database is online but contains no users."
    DATABASE_IS_ONLINE=1
    break
  else
    # Current user count is 0 or not set, wait longer...
    sleep $WAIT_TIME
  fi
done

echo "Validate total user count: $TOTAL_USER_COUNT"

if [ $TOTAL_USER_COUNT -eq 0 ]; then
  if [ $DATABASE_IS_ONLINE -eq 1 ]; then
    # Database does not contain any users (likley empty)
    # Restore from backup...
    echo "Restoring from backup: DB_INIT_FILE_LOCATION: $DB_INIT_FILE_LOCATION ..."

    sleep 10

    # Wait for the database backup deployment to be ready (DB_BACKUP_DEPLOYMENT_FULL_NAME)
    wait_for_rollout "$DB_BACKUP_DEPLOYMENT_FULL_NAME" "deployment"

    sleep 15

    # Restore the database from the latest backup
    restore_database_from_backup
  else
    echo "Database is offline."
  fi
else
  echo "Database appears to be healthy. No further action required."
fi
