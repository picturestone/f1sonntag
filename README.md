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
4. Add normal user: `php bin/console app:add-user`
5. Start server: `symfony server:start` and open URL given in output (usually http://localhost:8000)
6. Login (currently only admin can see things - after login with admin go to any admin page, e.g. http://localhost:8000/admin/teams - redirect from login to page is missing)

Available roles:

- User (automatically assigned)
- Admin (CRUD for sers, data for the season, and race results)
- BetsEdit (can CRUD bets for other users)

## Updating

To release log in on https://my.world4you.com/en/login, select "f1sonntag" on the top left dropdown, go to "webspace" on the left and either go to "ssh" and see the login data for the ssh connection, or go to "terminal" for an in-browser terminal. Pull from develop after merging new changes and do whatever is necessary.
