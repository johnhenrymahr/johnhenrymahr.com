if [ ! -d ./storage/cache ]; then
  printf "Create cache directory\n"
  mkdir ./storage/cache
  chmod 777 ./storage/cache
fi