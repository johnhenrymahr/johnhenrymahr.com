#! /bin/bash

if [ ! -d ./storage ]; then
  printf "Create storage directory.\n"
  mkdir ./storage
fi
if [ ! -d ./storage/logs ]; then
  printf "Create log directory.\n"
  mkdir ./storage/logs
  chmod 777 ./storage/logs
fi
if [ ! -d ./storage/digest ]; then
  printf "Create digest directory.\n"
  mkdir ./storage/digest
  chmod 777 ./storage/digest
fi
if [ -d ./storage/cache ]; then
  printf "Removing old cache directory.\n"
  rm -Rf ./storage/cache
fi
if [ ! -d ./storage/downloads ]; then
  printf "adding downloads dir.\n"
   mkdir ./storage/downloads
  chmod 775 ./storage/downloads
fi
if [ ! -f ./storage/downloads/testfile.txt ]; then
    printf "add test download file stub.\n"
    touch ./storage/downloads/testfile.txt
    chmod 775 ./storage/downloads/testfile.txt
fi