oc project $OC_PROJECT

echo "Deploying secrets to: $OC_PROJECT..."

# Check if the Helm deployment exists
if [[ `oc describe secret $APP_NAME-secrets 2>&1` =~ "NotFound" ]]; then
  echo "Secrets not found."
  echo "Creating... $APP_NAME-secrets"
else
  echo "Secrets already exist ($APP_NAME-secrets)."
  echo "Deleting secrets..."
  oc delete secret $APP_NAME-secrets
  echo "Recreating... $APP_NAME-secrets"
fi

cat <<EOF > secrets.yml
kind: Secret
apiVersion: v1
metadata:
  name: $APP_NAME-secrets
  namespace: $OC_PROJECT
  labels:
    template: $APP_NAME
stringData:
  database-name: $DB_NAME
  database-password: $SECRET_DB_PASSWORD
  database-user: $DB_USER
  redis-password: $SECRET_REDIS_PASSWORD
type: Opaque
EOF

oc create -f secrets.yml

# Create docker registry secret, if it doesn't exist yet
if [[ `oc describe secret docker-registry 2>&1` =~ "NotFound" ]]; then
  echo "Docker registry secrets not found."
  echo "Creating docker-registry secrets..."
else
  echo "Docker registry secrets already exist (docker-registry)."
  echo "Deleting secrets..."
  oc delete secret docker-registry
  echo "Recreating docker-registry..."
fi
oc create secret docker-registry $IMAGE_PULL_SECRET_NAME \
  --docker-server=$IMAGE_REPO_DOMAIN \
  --docker-username=$SECRET_DOCKER_USERNAME \
  --docker-password=$SECRET_DOCKER_PASSWORD \
  --docker-email=$SECRET_DOCKER_EMAIL
# Ensure secrets are linked for pulling from Artifactory
oc secrets link default $IMAGE_PULL_SECRET_NAME --for=pull
oc secrets link builder $IMAGE_PULL_SECRET_NAME --for=pull
