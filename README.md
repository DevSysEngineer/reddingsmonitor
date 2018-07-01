
## Require packages
```
sudo apt-get install php7.0-fpm php7.0-dom
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

## Sudoers services file
```
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service ssh *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service mysql *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service php7.0-fpm *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service nginx *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service cron *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service atd *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service rsyslog *
%sudo   ALL=(ALL) NOPASSWD: /usr/sbin/service anacron *
```

## Autostart script
```
#!/bin/bash
sudo service rsyslog start
sudo service ssh start
sudo service mysql start
sudo service php7.0-fpm start
sudo service nginx start
sudo service cron start
sudo service atd start
sudo service anacron start
```
