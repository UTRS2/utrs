Getting UTRS2 working in a dev environment

1. Clone into a directory
2. composer install
3. php artisan migrate:fresh

The /public subdirectory is the webroot. You can either use 'php artisan serve' to run a standalone webserver, or point your webserver of choice at /public
