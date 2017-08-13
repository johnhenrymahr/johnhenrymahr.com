#! /bin/bash

if [ ! -d ./storage ]; then
  printf "Create storage directory\n"
  mkdir ./storage
fi
if [ ! -d ./storage/logs ]; then
  printf "Create log directory\n"
  mkdir ./storage/logs
  chmod 777 ./storage/logs
fi
if [ ! -d ./storage/digest ]; then
  printf "Create digest directory\n"
  mkdir ./storage/digest
  chmod 777 ./storage/digest
fi
if [ -d ./storage/cache ]; then
  printf "Removing old cache directory\n"
  rm -Rf ./storage/cache
fi