# annotations
Annotations service backend

## Docker

The project provides two images (a `fpm` and a `cli` one) that run as the `www-data` user. They work in concert with others to allow to run the service locally, or to run tasks such as tests.

Build images with:

```
docker-compose build
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

Run all project tests:

```
docker-compose -f docker-compose.ci.yml build
docker-compose -f docker-compose.ci.yml run ci ./project_tests.sh
```

`-u` is needed to write to `vendor/`. Currently Composer prints some warning when git-cloning due to the user not being in /etc/passwd.

## Journey of a request to the annotations api
1. Query received via the `annotations` api.
1. Retrieve annotation listing from `hypothes.is/api` based on query parameters.
1. Denormalize the annotations into an object model.
1. Normalize the annotations to prepare the response which conforms to the eLife json schema
    1. Prepare the annotation text (if available) by:
        1. Converting the Markdown to HTML with [CommonMark](http://commonmark.thephpleague.com/).
        1. Purify the HTML with [HTMLPurifier](http://htmlpurifier.org/).
        1. Convert the HTML to Markdown with [HTML To Markdown](https://github.com/thephpleague/html-to-markdown/).
        1. Convert Markdown to content blocks that conform to schema relying on [CommonMark](http://commonmark.thephpleague.com/).
1. Return the response to the client.
