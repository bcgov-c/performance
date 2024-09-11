oc project $OC_PROJECT

echo "Deploying env configmap: $APP_NAME-env ..."

if [[ `oc describe configmap $APP_NAME-env 2>&1` =~ "NotFound" ]]; then
  oc create configmap $APP_NAME-env --from-file=.env=./example.env
else
  oc delete configmap $APP_NAME-env
  oc create configmap $APP_NAME-env --from-file=.env=./example.env
fi
