oc project $OC_PROJECT

echo "Deploying secrets to: $OC_PROJECT..."

# Check if the Helm deployment exists
if [[ `oc describe secret $APP_NAME-secrets 2>&1` =~ "NotFound" ]]; then
  echo "Secrets not found."

  echo "Creating... $APP_NAME-secrets"

cat <<EOF > secrets.yml
kind: Secret
apiVersion: v1
metadata:
  name: $APP_NAME-secrets
  namespace: $OC_PROJECT
  labels:
    template: $APP_NAME
stringData:
  database-name: $DB_DATABASE
  database-password: $SECRET_DB_PASSWORD
  database-user: $DB_USER
type: Opaque
EOF

oc create -f secrets.yml

else
  echo "Secrets already exist ($APP_NAME-secrets)."

  # echo "Deleting secrets..."
  # oc delete secret $APP_NAME-secrets
fi
