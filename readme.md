# UTRS 2.0

## Getting UTRS2 working in a dev environment

1. Clone into a directory
2. composer install
3. cp .env.example .env
4. edit the DB details in .env
5. php artisan key:generate
6. php artisan migrate:fresh

The /public subdirectory is the webroot. You can either use `php artisan serve` to run a standalone webserver, or point your webserver of choice at /public

## Setting up MediaWiki integration (for testing)

### API calls

You have a couple of options here. Either

#### Create a bot password for your own testing MediaWiki installation
Use `Special:BotPasswords` to create a bot password. If you have configured e-mail sending to mediawiki, access to that is useful.

In `.env`, set `MEDIAWIKI_USERNAME` to the bot password username and `MEDIAWIKI_PASSWORD` to the password.
Also set `WIKI_URL_GLOBAL`, `WIKI_URL_ENWIKI`, and `WIKI_URL_PTWIKI` to be `http://your-mediawiki/w/api.php`. 

#### Create a bot password on [Beta Cluster](https://deployment.wikimedia.beta.wmflabs.org)
This is useful because Beta Cluster is really similar to beta. Use `Special:BotPasswords` on some wiki to create a bot password (they are global AFAIK).

In `.env`, set `MEDIAWIKI_USERNAME` to the bot password username and `MEDIAWIKI_PASSWORD` to the password.
Also set
* `WIKI_URL_GLOBAL=https://deployment.wikimedia.beta.wmflabs.org/w/api.php` Note deployment as that's the preferred option of locking/unlocking stuff
* `WIKI_URL_ENWIKI=https://en.wikipedia.beta.wmflabs.org/w/api.php`
* `WIKI_URL_PTWIKI=https://pt.wikipedia.beta.wmflabs.org/w/api.php`

### OAuth
You need a wiki with `Extension:OAuth` installed. It's easier if you use the same wiki used with API calls.

Use the following settings:
* Protocol version: 1.0a
* Callback URL: `http://utrs.test/oauth/callback`
* "Allow consumer to specify a callback in requests and use "callback" URL above as a required prefix." should be yes
* Types of grants being requested: "User identity verification only"

Set up `.env`:

```dotenv
OAUTH_CALLBACK_URL="http://utrs.test/oauth/callback"

OAUTH_BASE_URL="https://deployment.wikimedia.beta.wmflabs.org"
OAUTH_CLIENT_ID="some-client-id"
OAUTH_CLIENT_SECRET="some-client-secret"
```

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

