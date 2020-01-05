#!/bin/bash


NAMESPACE="${NAMESPACE:-bootstrapi}"
RELEASE="${RELEASE:-release-1.2.0}"
BASE_URL="https://raw.githubusercontent.com/percona/percona-xtradb-cluster-operator/$RELEASE/deploy"

kubectl apply -f "$BASE_URL/crd.yaml"

kubectl create namespace "$NAMESPACE"

kubectl -n "$NAMESPACE" apply -f "$BASE_URL/rbac.yaml"
kubectl -n "$NAMESPACE" apply -f "$BASE_URL/operator.yaml"
kubectl -n "$NAMESPACE" apply -f "$BASE_URL/secrets.yaml"
kubectl -n "$NAMESPACE" apply -f "$BASE_URL/ssl-secrets.yaml"



kubectl -n "$NAMESPACE" apply -f mysql.yaml

watch "kubectl -n $NAMESPACE get all"

