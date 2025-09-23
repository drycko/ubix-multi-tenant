# Ubix Booking Management System (Multi Tenant with tenancy)

Ubix is a Laravel-based hotel/property booking management system designed for modern hospitality businesses. It provides robust tools for managing bookings, guests, rooms, packages, invoices, and analytics—all in one place.

## Features

- **Room & Package Management:** Create and manage rooms, room types, and special packages with flexible pricing and restrictions.
- **Booking Engine:** Fast, user-friendly booking creation and editing with support for shared rooms, guest assignment, and custom booking codes.
- **Guest Management:** Track guest profiles, booking history, and demographics.
- **Invoices & Payments:** Generate unique invoices, manage payments, and track financials.
- **Availability Calendar:** Visual calendar for room availability and package restrictions.
- **Stats & Analytics:** Dashboard with charts for bookings over time, revenue, and occupancy by room type.
- **Role-Based Access:** Secure admin panel with roles and permissions.
- **API Support:** RESTful API endpoints for integrations and custom workflows.
- **Customizable:** Built with Laravel, Tailwind CSS, and Chart.js for easy customization.

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- Node.js & npm
- MySQL or compatible database

### Installation

1. **Clone the repository:**
    ```bash
    git clone https://github.com/drycko/ubix-multi-tenant.git
    cd ubix
    ```

2. **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3. **Copy and configure your environment:**
    ```bash
    cp .env.example .env
    # Edit .env with your database and mail settings
    ```

4. **Generate application key:**
    ```bash
    php artisan key:generate
    ```

5. **Run migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```

6. **Build frontend assets:**
    ```bash
    npm run dev
    # or for production
    npm run build
    ```

7. **Start the development server:**
    ```bash
    php artisan serve
    ```

8. **Access the app:**
    - Visit [http://localhost:8000](http://localhost:8000) in your browser.

### API Usage

- Authenticate using Laravel Sanctum.
- Pass `X-Property-ID` in headers for property-specific endpoints.
- See `routes/api.php` for available endpoints.

### Customization

- **Calendar styles:** Edit `resources/css/calendar.css`.
- **Custom JS:** Add to `resources/js/calendar.js` and import in `app.js`.
- **Dashboard charts:** Powered by Chart.js, see `DashboardController.php` for data logic.

## Contributing

Pull requests are welcome! Please open an issue first to discuss major changes.

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

## design
Main colors: 
    #373643 - dark
    #18cb96 - green

---

*Built with [Laravel](https://laravel.com), [Tailwind CSS](https://tailwindcss.com), and [Chart.js](https://www.chartjs.org/).*