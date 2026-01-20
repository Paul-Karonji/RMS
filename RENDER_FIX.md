# Render Deployment Fix

The error you're seeing is because Render detected Node.js instead of PHP.

## Quick Fix - Option 1: Use Render Dashboard (Easiest)

Instead of using `render.yaml`, configure directly in Render dashboard:

1. **Delete the current service** in Render (if you created one)
2. **Create a new Web Service** again
3. This time, in the configuration:
   - **Environment**: Select **"Docker"** (not Node)
   - **Dockerfile Path**: Leave empty
   - **Build Command**: 
     ```
     composer install --optimize-autoloader --no-dev
     ```
   - **Start Command**:
     ```
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```

## Quick Fix - Option 2: Create Dockerfile (Recommended)

Create a `Dockerfile` in your `backend` folder:

```dockerfile
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Cache config and routes
RUN php artisan config:cache && php artisan route:cache

# Expose port
EXPOSE 8080

# Start server
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
```

Then in Render:
- **Environment**: Docker
- **Dockerfile Path**: `backend/Dockerfile`
- Leave build and start commands empty (Docker will handle it)

## Quick Fix - Option 3: Use render.yaml (Already created)

I've created a `render.yaml` file in your root directory. 

**To use it:**
1. Commit and push the `render.yaml` file to GitHub
2. In Render dashboard, instead of "Web Service", choose **"Blueprint"**
3. Connect your repo
4. Render will automatically detect the `render.yaml` and configure everything

**Note:** You'll still need to add sensitive environment variables manually in Render dashboard (database credentials, API keys, etc.)

## Recommended Approach

I recommend **Option 2 (Dockerfile)** because:
- ✅ Most reliable
- ✅ Works consistently
- ✅ Easy to debug
- ✅ Render free tier supports Docker

Let me know which option you want to use, and I'll help you set it up!
