# Performance Development

## Project Status

[![Lifecycle:Experimental](https://img.shields.io/badge/Lifecycle-Experimental-339999)](https://github.com/bcgov/repomountie/blob/master/doc/lifecycle-badges.md)

## Usage

Run local developent version using Docker [Desktop]

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

`$ node ./openshift/config/lighthouse/lighthouse-auth.js`
