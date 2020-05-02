Getting UTRS2 working in a dev environment

1. Clone into a directory
2. mkdir storge/framework
3. mkdir storge/framework/sessions
4. mkdir storge/framework/views
5. mkdir storge/framework/cache
6. composer install
7. cp .env.example .env
8. edit the DB details in .env
9. php artisan key:generate
10. php artisan migrate:fresh

The /public subdirectory is the webroot. You can either use 'php artisan serve' to run a standalone webserver, or point your webserver of choice at /public
