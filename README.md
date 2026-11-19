<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Local Development

<details>
<summary>Installation and project configuration</summary>

### Requirements

- PHP 8.5 and Composer 2
- Node.js 22.12+ and npm
- PostgreSQL 17
- PHP extensions for PostgreSQL, GD, Intl, Mbstring, DOM, and ZIP

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
```

On PowerShell, use `Copy-Item .env.example .env` to copy the environment file. Create your PostgreSQL database and configure `.env` before running migrations.

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_URL=postgresql://USER:PASSWORD@127.0.0.1:5432/app_database
MAIL_MAILER=log
```

Percent-encode special characters in database credentials. The `log` mailer writes development emails to `storage/logs/laravel.log`. Configure SMTP to send email.

```bash
php artisan migrate
php artisan storage:link
php artisan db:seed
php artisan serve
```

The storefront is available at `http://127.0.0.1:8000` and the administration panel at `/admin`. The repeatable development seeder provides 24 sample products and synthetic customer, payment-account, and order data.

Run each development process in a separate terminal.

```bash
npm run dev
php artisan queue:work
php artisan schedule:work
```

### Administration

```bash
php artisan admin:create admin@example.com
```

Enter a password when prompted. Sign in, enable an authenticator at `/account/security`, then sign out and sign in with a two-factor code to access the admin panel.

### Testing

```bash
php artisan test
```

Configure a dedicated PostgreSQL test database using `phpunit.xml`. The current test suite requires host `test-db` and database `borongin_test`. Its connection guard runs before test migrations.

### Deployment

Point the web server document root to `public`, enable HTTPS, configure database and SMTP credentials, and set `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE_COOKIE=true`. Keep `storage` and `bootstrap/cache` writable. Run the queue worker and schedule `php artisan schedule:run` every minute.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:restart
```

</details>
