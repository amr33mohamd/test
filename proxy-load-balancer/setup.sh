#!/bin/bash

echo "========================================="
echo "ProxyScrape Load Balancer - Setup"
echo "========================================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "Error: Composer is not installed."
    echo "Please install Composer: https://getcomposer.org/download/"
    exit 1
fi

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "PHP Version: $PHP_VERSION"

# Install dependencies
echo ""
echo "Installing dependencies..."
composer install --no-interaction --prefer-dist

# Copy .env file
echo ""
echo "Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created"
else
    echo ".env file already exists"
fi

# Generate application key
echo ""
echo "Generating application key..."
php artisan key:generate

# Create storage directories
echo ""
echo "Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs

# Set permissions
echo ""
echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache 2>/dev/null || true

echo ""
echo "========================================="
echo "Setup complete!"
echo "========================================="
echo ""
echo "To start the development server, run:"
echo "  php artisan serve"
echo ""
echo "Then visit: http://localhost:8000"
echo ""
echo "To run tests:"
echo "  vendor/bin/phpunit"
echo ""
echo "Good luck with the interview! 🚀"
echo ""
