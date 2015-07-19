#!/usr/bin/env bash

# ${0} => file itself
# ${1} => action
# ${2} => absolute path to node binary
# ${2} => absolute path for pid
# ${3} => absolute path to logfile

if [ $# -lt 4 ]; then
	echo "Usage: ${0} {start|stop} <node binary> <path to pidfile> <path to logfile>"
	exit
fi

if [ -z "${2}" ]; then
	echo "No path to binary"
	exit
fi

if [ -z "${3}" ]; then
	echo "No path to pidfile"
	exit
fi

if [ -z "${4}" ]; then
	echo "No path to logfile"
	exit
fi

if [ ! -f ${3} ]; then
	touch ${3}
fi

case "${1}" in
	start)
		PID=$(ps aux | awk '{ print $2 }' | grep $(cat ${2}) 2> /dev/null)
		if [ -z "${PID}" ]; then
			FILENAME="$(date +"%Y-%m-%d_%H-%M-%S").log"
			cd ${WD}
			${3} main.js &> logs/${FILENAME} &
			echo $! > ${2}
			echo "NodeJS started with pid #$(cat ${2})"
		else
			echo "NodeJS already running"
		fi
		;;
	stop)
		PID=$(ps aux | awk '{ print $2 }' | grep $(cat ${2}) 2> /dev/null)
		if [ -n "${PID}" ]; then
			kill -9 ${PID}
			echo "NodeJS stopped"
			echo "" > ${2}
		else
			echo "NodeJS not running"
		fi
		;;
	*)
		echo "Usage: ${0} {start|stop} [PIDFILE] [NODEPATH]"
		;;
esac