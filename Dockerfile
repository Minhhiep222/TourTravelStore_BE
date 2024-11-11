# Sử dụng PHP với Apache
FROM php:8.2-apache


# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép mã nguồn Laravel vào container
COPY . .

# Chỉnh quyền truy cập cho thư mục storage
RUN chown -R www-data:www-data /var/www/html/storage

# Cấu hình cho Laravel (nếu cần)
RUN cp .env.example .env \
    && php artisan key:generate

# Cài đặt các phụ thuộc của Laravel
# RUN composer install

EXPOSE 80

