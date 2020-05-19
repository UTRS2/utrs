Getting UTRS2 working in a dev environment

1. Clone into a directory
2. mkdir storage/framework
3. mkdir storage/framework/sessions
4. mkdir storage/framework/views
5. mkdir storage/framework/cache
6. composer install
7. cp .env.example .env
8. edit the DB details in .env
9. php artisan key:generate
10. php artisan migrate:fresh

The /public subdirectory is the webroot. You can either use 'php artisan serve' to run a standalone webserver, or point your webserver of choice at /public

== Jobs ==
***For this to work, you need to have your own urls set up for config/wikis.php***
This application requires a job queue to verify blocks and do other critical and regualar tasks. Below is a guide to setting up:

1. pecl install redis
2. Ensure jobs work with php artisan queue:work
3. sudo apt-get install supervisor
4. Add the following block at /etc/supervisor/conf.d/utrs.conf, example:

[program:utrs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/app.com/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=forge
; Warning: Only run one job at a time!
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/log/file.log
; Warning: stopwaitsecs needs to be bigger than the longest job. If not, the job may be terminated.
stopwaitsecs=3600

