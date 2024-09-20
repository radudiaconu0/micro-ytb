# MicroYTB application

This README provides detailed instructions on how to set up and run our Laravel application, including all necessary components and dependencies.



## Prerequisites

Ensure you have the following installed on your system:

- PHP 8.2 or higher
- Composer
- MySQL or PostgreSQL
- Node.js and NPM
- Git

## PHP Extensions

Install the following PHP extensions:

```bash
sudo apt-get update
sudo apt-get install php-imagick php-redis php-mysql php-xml php-curl php-mbstring php-zip php-gd
```

Verify the installation:

```bash
php -m | grep -E "imagick|redis|pdo_mysql|xml|curl|mbstring|zip|gd"
```

## Database Setup

1. Create a new database for the application:

```sql
CREATE DATABASE your_database_name;
```

2. Create a new user and grant privileges:

```sql
CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

## Laravel Installation

1. Clone the repository:

```bash
git clone https://your-repository-url.git
cd your-project-directory
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy the `.env.example` file:

```bash
cp .env.example .env
```

4. Generate an application key:

```bash
php artisan key:generate
```

## Environment Configuration

Edit the `.env` file and update the following variables:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

MINIO_ENDPOINT=http://127.0.0.1:9000
MINIO_KEY=your_minio_key
MINIO_SECRET=your_minio_secret
MINIO_REGION=us-east-1
MINIO_BUCKET=your_bucket_name

MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_meilisearch_key

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Minio Setup

1. Install Minio:

```bash
wget https://dl.min.io/server/minio/release/linux-amd64/minio
chmod +x minio
sudo mv minio /usr/local/bin/
```

2. Create a directory for Minio data:

```bash
mkdir ~/minio-data
```

3. Run Minio:

```bash
minio server ~/minio-data
```

4. Access the Minio console at `http://127.0.0.1:9000` and create a new bucket.

## MeiliSearch Setup

1. Install MeiliSearch:

```bash
curl -L https://install.meilisearch.com | sh
```

2. Run MeiliSearch:

```bash
./meilisearch
```

## FFmpeg Installation

Install FFmpeg:

```bash
sudo apt update
sudo apt install ffmpeg
```

Verify the installation:

```bash
ffmpeg -version
```

## Node.js and NPM

1. Install Node.js dependencies:

```bash
npm install
```

2. Compile assets:

```bash
npm run dev
```

## Redis Setup

1. Install Redis:

```bash
sudo apt update
sudo apt install redis-server
```

2. Start Redis:

```bash
sudo systemctl start redis-server
```

3. Verify Redis is running:

```bash
redis-cli ping
```

You should receive a "PONG" response.

## Running the Application

1. Run database migrations:

```bash
php artisan migrate
```

2. Seed the database (if applicable):

```bash
php artisan db:seed
```

3. Generate jwt secret:

```bash
php artisan jwt:secret
```

4. Start the Laravel development server:

```bash
php artisan serve
```

5. In a separate terminal, start the queue listener:

```bash
 php artisan queue:listen --queue=scout,video-processing
```

5. Access the application at `http://localhost:8000`

## Troubleshooting

- If you encounter any "Class not found" errors, try running:

```bash
composer dump-autoload
```

- For any JavaScript-related issues, ensure all dependencies are installed and compiled:

```bash
npm install
npm run dev
```

- If you're having issues with file permissions, you may need to set the correct permissions for the storage and bootstrap/cache directories:

```bash
chmod -R 775 storage bootstrap/cache
```

- If MeiliSearch is not working, ensure it's running and the correct host and key are set in your `.env` file.

- For Minio issues, check that the service is running and that you've set the correct endpoint, key, secret, and bucket name in your `.env` file.

If you encounter any other issues, please check the Laravel log file at `storage/logs/laravel.log` for more detailed error messages.
