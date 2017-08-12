#! /bin/bash

if [ ! -d ./storage ]; then
  printf "Create storage directory\n"
  mkdir ./storage
fi
if [ ! -d ./storage/logs ]; then
  printf "Create log directory\n"
  mkdir ./storage/logs
fi
if [ ! -f ./storage/logs/{{logfile}} ]; then
   printf "Create system log\n"
   touch ./storage/logs/{{logfile}}
   chmod 666 ./storage/logs/{{logfile}}
fi
if [ -d ./storage/cache ]; then
  printf "Removing old cache directory\n"
  rm -Rf ./storage/cache
fi