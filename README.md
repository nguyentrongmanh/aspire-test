Follow me on github [https://github.com/nguyentrongmanh](https://github.com/nguyentrongmanh)

## Required enviroiment version

$ php >= 8.0.x

## Installation

``` bash
# install app's dependencies
$ composer install
# copy env
$ cp .env.example .env
# docker run
$ ./vendor/bin/sail up -d
# generate laravel APP_KEY
$ ./vendor/bin/sail artisan key:generate
# run database migration and seed
$ ./vendor/bin/sail artisan migrate --seed
# generate symbolic link
$ ./vendor/bin/sail artisan storage:link
```
## Usage
[postman collection](https://api.postman.com/collections/7110532-eaab21e5-b0f5-4ac8-ae95-884b0cea8cd8?access_key=PMAT-01H59HFBQW8XFBA9DHQ1FGJYPV)

### Admin account
``` bash
admin@gmail.com
password
```

### User account
``` bash
test_account@example.com
password
```

## Format code

``` bash
$ ./vendor/bin/pint
```

## Run test

``` bash
$ ./vendor/bin/sail artisan test
```

