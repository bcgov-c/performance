# Performance Development

## Project Status

[![Lifecycle:Experimental](https://img.shields.io/badge/Lifecycle-Experimental-339999)](https://github.com/bcgov/repomountie/blob/master/doc/lifecycle-badges.md)

## Usage

Run local developent version using Docker [Desktop]

## Initialize database

The automated deployment will attempt to initialize the database using the backup script.

For example, from the backup (temp) file: ./temp/db-backups/dev-mysql-performance-db_2024-09-04_12-00-34.sql.gz

Which would get copied to: performance-db-backup-storage-[DEPLOYED_POD_ID]:/backups/init.sql.gz

`$ docker compose up --build -d`

## Test puppeteer / lighthouse scripts locally

Ensure node / npm is installed, then install via npm
devDependencies for puppeteer, lighthouse are in package.json

`$ npm install`

Create a temporary directory:

`$ mkdir -p tmp/artifacts`

To run lighthouse test(s) using puppeteer in node locally,
Ensure the following environment variables are set (included in .env.example):

- APP_HOST_URL,
- TESTER_USERNAME,
- TESTER_PASSWORD

Note that NODE_ENV=development is required for local tetsing
Prerequisites are installed as devDependencies for node in package.json
Or use:

`$ npm config set -g production false`

Run lighthouse test script

`$ node ./openshift/config/lighthouse/lighthouse-auth.js`
