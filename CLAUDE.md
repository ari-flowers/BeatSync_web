# BeatSync Web App - Development Guide

## Commands
- **Run app**: `php artisan serve`
- **Dev environment**: `npm run dev` (Vite) in parallel with `php artisan serve`
- **Tests**: `php artisan test` or `./vendor/bin/phpunit`
- **Single test**: `php artisan test --filter TestName`
- **Linting**: `./vendor/bin/pint` (Laravel Pint)
- **DB migrations**: `php artisan migrate`
- **Queue worker**: `php artisan queue:work`
- **Spotify token refresh**: `php artisan app:refresh-spotify-tokens`

## Code Guidelines
- Follow PSR-12 standards for PHP code
- Use type hints and return types for all methods
- Organize code by domain in app/ directory structure
- Blade templates in resources/views/
- Livewire components in resources/views/livewire/
- CSS with Tailwind classes in views, custom styles in resources/css/
- Handle API failures gracefully with try/catch blocks
- DB queries should use models or query builder (no raw SQL)
- Keep controller methods small and focused
- Use Laravel's validation features for all form inputs