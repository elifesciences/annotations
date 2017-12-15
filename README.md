# annotations
Annotations service backend

## Docker

The project provides two images (a `fpm` and a `cli` one) that run as the `www-data` user. They work in concert with others to allow to run the service locally, or to run tasks such as tests.

Build images with:

```
./build_images.sh
```

Run a shell as `www-data` inside a new `cli` container:

```
docker-compose run cli /bin/bash
```

Run composer:

```
docker-compose run -u $(id -u) cli composer install
```

Run a PHP interpreter:

```
docker-compose run cli /usr/bin/env php ...
```

Run PHPUnit:

```
docker-compose run cli /usr/bin/env php vendor/bin/phpunit
```

`-u` is needed to write to `vendor/`. Currently Composer prints some warning when git-cloning due to the user not being in /etc/passwd.
