#!/bin/bash
# $Id$
#
# Author: David Danier, david.danier@team23.de
# Project: Serverstats, http://serverstats.berlios.de/
# License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
#
# Copyright (C) 2005 David Danier
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


# Variablen

LOGPATH="/path/to/serverstats/sources/traffic"
# Example:
#  CHAINLIST="HTTP SSH DNS"
CHAINLIST="FILL SOMETHING IN HERE"

SLEEP_BIN="/usr/bin/sleep"
IPTABLES_BIN="/sbin/iptables"
GREP_BIN="/bin/grep"
TAIL_BIN="/usr/bin/tail"
AWK_BIN="/bin/awk"

# Programm

${SLEEP_BIN} 50
${IPTABLES_BIN} -nvxL > ${LOGPATH}/tmpfile

for CHAIN in ${CHAINLIST}
do
	TRAFFIC=`${GREP_BIN} -A 2 "Chain ${CHAIN} " ${LOGPATH}/tmpfile | ${TAIL_BIN} -n 1 | ${AWK_BIN} '{print $2}'`
	echo -n "${TRAFFIC}" > ${LOGPATH}/${CHAIN}
done


