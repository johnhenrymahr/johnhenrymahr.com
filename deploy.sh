#!/bin/bash

##################################
######## Deploy Version ##########
##################################
#								 #	
# 1) Deploy to production        #
#								 #
# 2) increment package version   #
#								 #
# 3) push tag to git 			 #
#								 #
##################################

#check we are on master
BRANCH="$(git rev-parse --abbrev-ref HEAD)"
PARENT=dirname BRANCH

printf "on branch ${BRANCH}\n"

if [ "${PARENT}" != "release" ]; then
	printf "\nNot on a release branch. Exiting.\n"
	#exit 1;
fi

printf "Deploy version to production? \n"

read -n1 -rsp $'Press any key to continue or Ctrl+C to exit...\n'

if [ "$key" = '' ]; then
   printf 'Continue \n'
else
    # Anything else pressed, do whatever else.
    # echo [$key] not empty
    printf 'Exiting.\n'
    exit 1
fi	

TS="$(date +"%s")"
##timestamp 1 hour in future
FTS=$(($TS + 3600))

printf "timestamp ${TS}, future ts = ${FTS}\n"

##set server to maintaince mode

printf "running gulp deploy --production \n"

gulp deploy --server=production || { echo "build failed exit without updating version or tags"; exit 1; }

##set server back to production mode

#bump version patch
#npm version patch

#tag and push to git origin
#git push origin "$(git describe --tags)"
#git pull origin "${BRANCH}"
#git push origin HEAD:"${BRANCH}"

exit 0
 