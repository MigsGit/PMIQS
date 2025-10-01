#!/bin/bash

# if [ ! -f "vendor/autoload.php" ]; then
#     composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
# else
#     echo "vendor/autoload.php file exists."
# fi

# if [ ! -f "node_modules" ]; then
#     npm install
# else
#     echo "node_modules file exists."
# fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
    case "$APP_ENV" in
    "local")
        echo "Copying .env.example ... "
        cp .env.example .env
    ;;
    "prod")
        echo "Copying .env.prod ... "
        cp .env.prod .env
    ;;
    esac
else
    echo "env file exists."
fi

# php artisan migrate
php artisan clear
php artisan key:generate
php artisan optimize:clear


# npm run build
npm run build
