oc project $OC_PROJECT

echo "Deploying secrets to: $OC_PROJECT..."

# Check if the Helm deployment exists
if [[ `oc describe secret $APP_NAME-secrets 2>&1` =~ "NotFound" ]]; then
  echo "Secrets not found."

  echo "Creating... $APP_NAME-secrets"

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

else
  echo "Secrets already exist ($APP_NAME-secrets)."

  # echo "Deleting secrets..."
  # oc delete secret $APP_NAME-secrets
fi
