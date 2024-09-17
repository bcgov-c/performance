oc project $OC_PROJECT

# Create a temporary file with substituted environment variables
TEMP_ENV_FILE=$(mktemp)
envsubst < ./example.env > $TEMP_ENV_FILE

if [[ `oc describe configmap $APP_NAME-env 2>&1` =~ "NotFound" ]]; then
  # Nothing to delete
else
  echo "ConfigMap exists... Deleting: $APP_NAME-env"
  oc delete configmap $APP_NAME-env
fi

echo "Deploying env configmap: $APP_NAME-env ..."
oc create configmap $APP_NAME-env --from-file=.env=$TEMP_ENV_FILE

# Clean up the temporary file
rm $TEMP_ENV_FILE
