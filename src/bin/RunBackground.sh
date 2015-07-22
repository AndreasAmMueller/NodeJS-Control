#!/usr/bin/env bash

# ${0} => file itself
# ${1} => action
# ${2} => absolute path to node binary
# ${3} => absolute path to script file
# ${4} => absolute path for pid file
# ${5} => absolute path to logfile

if [ $# -lt 5 ]; then
	echo "Usage: ${0} {start|stop} <path to binary> <path to script> <path to pidfile> <path to logfile>"
	exit
fi

if [ -z "${2}" ]; then
	echo "No path to binary"
	exit
fi

if [ -z "${3}" ]; then
	echo "No path to script"
	exit
fi

if [ -z "${4}" ]; then
	echo "No path to pidfile"
	exit
fi

if [ -z "${5}" ]; then
	echo "No path to logfile"
	exit
fi

if [ ! -f ${4} ]; then
	touch ${4}
fi

if [ ! -f ${5} ]; then
	touch ${5}
fi

scrdir=$(cd "$(dirname "${3}")"; pwd)
scrfile=$(basename "${3}")

PIDF=$(cat ${4} 2> /dev/null)
PID=$(ps aux | awk '{ print $2 }' | grep "${PIDF}")

if [ ${#PID} -gt 5 ]; then
	PID=""
fi

case "${1}" in
	start)
		if [ -z "${PID}" ]; then
			cd ${scrdir}
			EXEC="${2} ${scrfile} 2>&1 1> ${5} &"
			eval ${EXEC}
			echo $! > ${4}
			echo "NodeJS started with pid #$(cat ${4})"
		else
			echo "NodeJS already running"
		fi
		;;
	stop)
		if [ -n "${PID}" ]; then
			kill -9 ${PID}
			echo "NodeJS stopped"
			echo "" > ${4}
		else
			echo "NodeJS not running"
		fi
		;;
	status)
		if [ -z "${PID}" ]; then
			echo "NodeJS stopped"
		else
			echo "NodeJS running"
		fi
		;;
	*)
		echo "Usage: ${0} {start|stop} <path to binary> <path to script> <path to pidfile> <path to logfile>"
		;;
esac