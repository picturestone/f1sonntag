# F1Sonntag

## Local setup

Requirements:

-   PHP 8.3
-   Docker & Docker compose
-   Symfony CLI

Steps:

1. Start database: `docker compose up -d`
2. Execute `php bin/console doctrine:migrations:migrate`
3. Start server: `symfony server:start`
