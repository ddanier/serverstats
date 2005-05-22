#!/bin/bash

# Variablen

PATH="/path/to/serverstats/services/traffic"
CHAINLIST="FILL SOMETHING IN HERE"

SLEEP_BIN="/usr/bin/sleep"
IPTABLES_BIN="/sbin/iptables"
GREP_BIN="/in/grep"
TAIL_BIN="/usr/bin/tail"
AWK_BIN="/bin/awk"

# Programm

${SLEEP_BIN} 50
${IPTABLES_BIN} -nvxL > ${PATH}/tmpfile

for CHAIN in ${CHAINLIST}
do
	TRAFFIC=`${GREP_BIN} -A 2 "Chain ${CHAIN}" ${PATH}/tmpfile | ${TAIL_BIN} -n 1 | ${AWK_BIN} '{print $2}'`
	echo -n "${TRAFFIC}" > ${PATH}/${CHAIN}
done


