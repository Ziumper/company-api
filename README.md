# Company REST-API

Project that allows to save company data with rest api.

## Getting Started

Based on official symfony docker env. Check [symfony/docker](https://github.com/dunglas/symfony-docker) if more info is needed.

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.


## Setup for Unit Tests

To run unit tests following has to be done:

```
# login into container
docker compose exec php sh
# make sure bin/console file is executable file as well phpunit bin
chomd +x bin/console bin/phpunit
# setup database and run migrations
php bin/console doctrine:database:create --env=test
# migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=test
# run unit test
php bin/phpunit
```