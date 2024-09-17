oc project $OC_PROJECT

echo "Deploying NetworkPolicies to: $OC_PROJECT..."

echo "Creating NetworkPolicies: allow-from-openshift-ingress"
sed -e "s/\${DEPLOY_NAMESPACE}/$DEPLOY_NAMESPACE/g" < ./openshift/network-ingress.yml | oc apply -f -
