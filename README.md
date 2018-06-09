
## Require packages
```
sudo apt-get install fonts-freefont-ttf php7.0-fpm php7.0-gd php7.0-dom
```

## PHP-FPM Pool settings
```
[www]
user = www-data
group = www-data
listen = 127.0.0.1:9000;
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 100
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 20
pm.process_idle_timeout = 10s
pm.max_requests = 0
```


## Composer install
```
sudo php /home/reddingsmonitor/composer.phar install --no-plugins --no-scripts
```
