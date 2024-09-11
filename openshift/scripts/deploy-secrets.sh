oc project $OC_PROJECT

echo "Deploying secrets to: $OC_PROJECT ($APP_NAME-secrets)..."

# Check if the Helm deployment exists
if [[ `oc describe sts $APP_NAME-secrets 2>&1` =~ "NotFound" ]]; then
  echo "Secrets already exist. Moving on..."
else
  echo "Secrets not found... creating..."

  echo "
    kind: Secret
    apiVersion: v1
    metadata:
      name: $APP_NAME-secrets
      namespace: $OC_PROJECT
      labels:
        template: $APP_NAME
      stringData:
        database-name: $DATABASE_NAME
        database-password: $SECRET_DB_PASSWORD
        database-user: $DB_USER
      type: Opaque
    " > secrets.yml
  oc create -f secrets.yml
fi
