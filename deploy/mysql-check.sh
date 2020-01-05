#!/bin/bash


NAMESPACE="${NAMESPACE:-bootstrapi}"

kubectl -n $NAMESPACE run -i --rm --tty percona-client --image=percona:5.7 --restart=Never -- bash -c "mysql -h bootstrapi-mysql-proxysql -uroot -proot_password"

