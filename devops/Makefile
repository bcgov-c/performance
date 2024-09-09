#!make

-include .env
export $(shell sed 's/=.*//' .env)

.DEFAULT_GOAL := default

export NAMESPACE := $(or $(NAMESPACE), $$NS)
export APP_NAME := $(or $(APP_NAME), court-interpreter-scheduling)
export BUILD_ID := $(or $(BUILD_ID), 1)
export APP_KEY :=  $(or $(APP_KEY), $$KEY)
export TEMPLATE_FILE_NAME := $(or $(TEMPLATE_FILE_NAME), $$FILE)
export BRANCH := $(or $(BRANCH), dev)
export BUILD_NAMESPACE := $(or $(BUILD_NAMESPACE), $$BUILD_NS)
export DOMAIN := $(or $(DOMAIN), apps.silver.devops.gov.bc.ca)
export DB_POD_NAME := $(or $(DB_POD_NAME), court-interpreter-scheduling)
export KC_AUTH_URL := $(or $(KC_AUTH_URL), https://oidc.gov.bc.ca/auth/)
export KC_REALM := $(or $(KC_REALM), joxo7v8f)

export DEPLOYMENT_ENV := $(or $(DEPLOYMENT_ENV), dev)
export DEPLOYMENT_ENV_HIDDEN_FLAG := $(or $(DEPLOYMENT_ENV_HIDDEN_FLAG), prod)

default:
	@echo "Please see README.md for usage of make commands"

# ------------------------------------------------------------------------------
# Task Aliases
# ------------------------------------------------------------------------------

oc-build: app-check print-status build
oc-build-api: app-check print-status build-api
oc-build-app: app-check print-status build-app
oc-build-proxied-app: app-check print-status build-proxied-app
oc-deploy-db: app-check print-status deploy-db
oc-deploy-api: app-check print-status deploy-api
oc-deploy-app: app-check print-status deploy-app
oc-deploy-proxied-app: app-check print-status deploy-proxied-app
oc-delete: app-check print-status delete
DIR := ${CURDIR}

## Define build and deploy variable
define NAME
"$(APP_NAME)"
endef

define DB_SERVICE_NAME
"patroni-master"
endef

define TEMPLATE_PATH
"./openshift/$(TEMPLATE_FILE_NAME)"
endef

define BUILD_TAG
"$(BRANCH)-${BUILD_ID}"
endef

define API
"api-$(APP_NAME)"
endef

define API_INSTANCE
"api-$(APP_NAME)-$(BUILD_ID)"
endef

define APP
"app-$(APP_NAME)"
endef

# The front-end when routed through the justice proxy
define APP_PROXIED
"proxied-app-$(APP_NAME)"
endef

define APP_INSTANCE
"app-$(APP_NAME)-$(BUILD_ID)"
endef

define APP_HOST
"$(HOST_PREFIX)$(APP_NAME).$(DOMAIN)"
endef

define API_DEPLOY_TEMPLATE_PATH
"./openshift/api-deploy.yml"
endef

define DB_DEPLOY_TEMPLATE_PATH
"./openshift/patroni-deploy.yml"
endef

define APP_DEPLOY_TEMPLATE_PATH
"./openshift/app-deploy.yml"
endef

define DOCKER_BUILD_TEMPLATE_PATH
"./openshift/docker-build.yml"
endef

define KEYCLOAK_IMAGE_NAME
"courts-keycloak"
endef

define KC_NGINX_IMAGE_NAME
"$(KEYCLOAK_IMAGE_NAME)-nginx"
endef


define rollout_and_wait
@oc -n $(NAMESPACE) rollout status $1
endef

define DB_BACKUP_APP
"db-backup"
endef

# Openshift Build
app-check:
	test -n "$(BUILD_ID)"

print-status:
	@echo " +---------------------------------------------------------+ "
	@echo " | Current Settings                                        | "
	@echo " +---------------------------------------------------------+ "
	@echo " | PROJECT:      $(APP_NAME)"
	@echo " | BRANCH:       $(BRANCH)"
	@echo " | NAMESPACE:    $(NAMESPACE)"
	@echo " | BUILD_NAMESPACE:    $(BUILD_NAMESPACE)"
	@echo " +---------------------------------------------------------+ "
	@echo " | BUILD_ID: 	$(BUILD_ID) "
	@echo " | BUILD_TAG: 	$(BUILD_TAG) "
	@echo " +---------------------------------------------------------+ "
	@echo " | FILE NAME: 	$(TEMPLATE_FILE_NAME) "
	@echo " | FILE PATH: 	$(TEMPLATE_PATH) "
	@echo " +---------------------------------------------------------+ "
	@echo " | HOST NAME: 	$(APP_HOST) "
	@echo " +---------------------------------------------------------+ "

init-project:
	@echo "Run initial set-up for the project, including network security\n"
	@oc process -f openshift/networkpolicy.yml -p NAMESPACE=$$(oc project --short) | oc apply -f -
	@oc -n $(BUILD_NAMESPACE) policy add-role-to-group system:image-puller system:serviceaccounts:$$(oc project --short)



import:
	test -n "$(NAMESPACE)"
	test -n "$(BUILD_NAMESPACE)"
	test -n "$(BRANCH)"
	test -n "$(APP_KEY)"
	@echo "+\n++ Importing image from $(BUILD_NAMESPACE):$(NAME)/$(BUILD_TAG) to $(NAMESPACE):$(NAME)$/(BUILD_TAG) \n+"
	@oc tag $(BUILD_NAMESPACE)/$(NAME):$(TAG) $(NAMESPACE)/$(NAME):$(TAG)


deploy-app:
	test -n "$(NAMESPACE)"
	test -n "$(BUILD_NAMESPACE)"
	test -n "$(BRANCH)"
	@echo "+\n++ Deploying APP with tag: $(BUILD_TAG)\n+"
	@oc -n $(NAMESPACE) process -f openshift/app-deploy.yml \
		-p NAME=$(APP) \
		-p HOST_NAME=$(APP_HOST) \
		-p BUILD_NAMESPACE=$(BUILD_NAMESPACE) \
		-p IMAGE_STREAM_TAG="$(APP):$(BUILD_TAG)" | oc -n $(NAMESPACE) apply -f -
	$(call rollout_and_wait,dc/$(APP))


delete:
	@echo "+\n++ Deleting deployment $$APPLICATION from namespace $(NAMESPACE)\n+"
	@oc project $(NAMESPACE)
	@oc delete all -l app=$$APPLICATION
	@oc delete pvc $$APPLICATION --ignore-not-found

delete-project:
	@echo "+\n++ Delete all project related items\n"
	@oc project $(NAMESPACE)
	@oc delete all -l project=$(APP_NAME)