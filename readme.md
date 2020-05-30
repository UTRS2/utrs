# UTRS 2.0

## Getting UTRS2 working in a dev environment

1. Clone into a directory
2. composer install
3. cp .env.example .env
4. edit the DB details in .env
5. php artisan key:generate
6. php artisan migrate:fresh

The /public subdirectory is the webroot. You can either use `php artisan serve` to run a standalone webserver, or point your webserver of choice at /public

## Jobs
For this to work, you need to have your own urls set up for config/wikis.php<br/>
This application requires a job queue to verify blocks and do other critical and regualar tasks. Below is a guide to setting up:

1. pecl install redis
2. Ensure jobs work with php artisan queue:work
3. sudo adduser supervisor
4. sudo apt-get install supervisor
5. Add the following block at /etc/supervisor/conf.d/utrs.conf, example, replacing /path/tos:

DO NOT CHANGE ANY OF THESE VALUES EXCEPT THE PATHS

```
[program:utrs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=supervisor
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/log/file.log
stopwaitsecs=300
```

6. sudo supervisord

