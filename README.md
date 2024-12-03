# TourTravelStore

TourTravelStore is a travel tour management project built with a web API.

-   **Server**: Laravel version 10
-   **Client**: Vue 3

## Features:

-   Travel tour, user, order, and booking management
-   Payment integration
-   Search functionality
-   Booking system
-   Real-time Chat with GPT
-   SEO optimization
-   Google Maps integration

---

# TourTravelStore_BE (Backend)

This is the server-side application for **TourTravelStore**.

## Installation:

1. **Install Composer**:  
   Download Composer from [here](https://getcomposer.org/download).

2. **Install XAMPP**:  
   Download XAMPP from [here](https://www.apachefriends.org/download.html).

## Usage Server Repository:

1. **Clone the server repository**:

```bash
  git clone https://github.com/Minhhiep222/TourTravelStore_BE.git
```

2. **run "composer install" in terminal or cmd, git bash**

```bash
composer install
```

3. **run "php artisan key:generate" in terminal or cmd, git bash**

```bash
php artisan key:generate
```

4. **Phpmyadmin create a table database**

5. **In file .env look for line 14: DB_DATABASE="your-name-database"**

6. **run "php artisan migrate --seed" in terminal or cmd, git bash for database and data**

```bash
php artisan migrate --seed
```

7. **run "php artisan serve" for run sever**

```bash
php artisan serve
```

## Usage Client Repository:

1. **Clone the repository**
    ```bash
    git clone https://github.com/Minhhiep222/TourTravelStore_FE.git
    ```
2. **run "npm install"**

    ```bash
    npm install
    ```

3. **run "npm run serve"**
    ```bash
    npm run serve
    ```
