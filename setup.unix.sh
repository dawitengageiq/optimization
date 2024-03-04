#!/bin/sh
# dump-autoload and clear-compiled commands
composer dump-autoload && php artisan clear-compiled;

# change directory or file ownership
if [ -z "$1" ]
  then
    echo "No server user specified!";
  else
    sudo chown -R "$1" ./*;
fi

# change directory permission of public and storage
sudo chmod -R 777 public;
sudo chmod -R 777 storage;
sudo chmod -R 777 bootstrap;
sudo chmod -R 777 bootstrap/cache;

# restart the supervisor
if [ -z "$2" ]
  then
    echo "No worker group name specified!";
  else
    sudo supervisorctl restart "$2";
fi