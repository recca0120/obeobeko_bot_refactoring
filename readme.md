<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img width="150"src="https://laravel.com/laravel.png">
    </a>
</p>
<p align="center">
    <a href="https://travis-ci.org/laravel/framework">
        <img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License">
    </a>
</p>

## Install

```bash
$ sudo -s
$ apt-get -y install git
$ git clone https://github.com/fukuball/obeobeko.git
$ cd obeobeko
$ sh system_env_script/build.sh
$ composer install
$ php artisan key:generate
$ npm install
$ npm rebuild node-sass
$ npm run dev
$ php artisan migrate
$ php artisan db:seed
```

### Set ENV

### Set Nginx

### Set Supervisor

```bash
$ sudo service supervisor start
$ sudo supervisorctl reread
$ sudo supervisorctl update
$ sudo supervisorctl start obeobeko-worker:*

$ php artisan cache:clear
$ sudo supervisorctl restart all
```

### Create Seed

```bash
$ php artisan iseed failed_jobs,follower_records,jobs,migrations,obeobeko_comments,obeobeko_like_records,obeobeko_play_records,obeobekos,password_resets,user_activations,user_fb_records,users,youtube_items
```

### Migrate Seed

```bash
$ php artisan db:seed
```

## Run Test

### Run whole tests:

```
php vendor/bin/phpunit
```

### Run one tests case:

```
php vendor/bin/phpunit --filter testBasicTest
```

## Run Code Sniff

```
php vendor/bin/phpcs --standard=PSR2 app
```

## Install PHP-CS-Fixer

```
composer global require friendsofphp/php-cs-fixer
```

```
php-cs-fixer fix app --dry-run
```

```
php-cs-fixer fix app
```

## Note

Find [^\x00-\x7F]

/Users/fukuball/Projects/obeobeko/app,/Users/fukuball/Projects/obeobeko/resources

## About ObeObeKo

ObeObeKo, 謳歌
