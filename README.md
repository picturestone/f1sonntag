# F1Sonntag

## Local setup for development

Requirements:

-   PHP 8.3
-   Docker & Docker compose
-   Symfony CLI

Steps:

1. Start database: `docker compose up -d`
2. Execute `php bin/console doctrine:migrations:migrate`
3. Add admin user: `php bin/console app:add-user --admin`
4. Add normal user: php bin/console app:add-user`
5. Start server: `symfony server:start` and open URL given in output (usually http://localhost:8000)
6. Login (currently only admin can see things - after login with admin go to any admin page, e.g. http://localhost:8000/admin/teams - redirect from login to page is missing)
