#!/usr/bin/env bash

#Relative path to primary vagrant root
DIRECTORY=../pv/

if [ -d "$DIRECTORY" ]; then
	echo 'Changed to PV'
	cd $DIRECTORY
fi

vagrant ssh -c "echo whoami"