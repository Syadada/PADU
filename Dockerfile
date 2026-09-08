# =====================================================================
# Dockerfile untuk PADU Analytics Engine (PHP 8.2 + Python 3.11 + DuckDB)
# =====================================================================
FROM php:8.2-cli-bookworm

# 1. Install System Dependencies & Python 3 + Pip
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP Extensions (pdo_sqlite, zip, pcntl)
RUN docker-php-ext-install pdo_sqlite zip pcntl

# 3. Pre-install Python High-Speed Data Engine (DuckDB, Pandas, PyArrow, OpenPyXL)
RUN python3 -m pip install --break-system-packages --no-cache-dir duckdb pandas pyarrow openpyxl

# 4. Set Working Directory
WORKDIR /var/www/html

# 5. Copy Application Source Code
COPY . /var/www/html

# 6. Copy .env.example jika .env belum ada
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# 7. Environment configuration untuk Docker
ENV SESSION_DRIVER=file
ENV CACHE_STORE=file
ENV QUEUE_CONNECTION=sync
ENV PYTHON_BINARY=python3

# 8. Expose Port 8000
EXPOSE 8000

# 9. Startup Command: Generate Key, Migrasi, & Jalankan Server Artisan
CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=8000
