# Ubix Multi-Tenant Property Management System

**A comprehensive Laravel-based hospitality management platform designed for modern property businesses**

Ubix is a feature-rich, multi-tenant property management system that provides complete solutions for hotels, vacation rentals, and hospitality businesses. Built with Laravel 12, it offers robust booking management, housekeeping operations, maintenance tracking, and comprehensive reporting—all in a secure, scalable multi-tenant architecture.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

---

## 🏆 **Phase 1 Complete - Production Ready!**

We've successfully completed **Phase 1** of development, delivering a comprehensive property management system with all essential features for hospitality operations.

---

## ✨ **Core Features**

### 🏢 **Multi-Tenant Architecture**
- **Domain-based tenancy** with complete data isolation
- **Subscription management** with multiple tiers (Starter, Professional, Enterprise)
- **Property-specific access control** and context switching
- **Centralized administration** with tenant management

### 🏨 **Property & Room Management**
- **Multi-property support** for hotel chains and property groups
- **Room types and configurations** with amenities and features
- **Room rates and seasonal pricing** with advanced rate management
- **Room availability tracking** with real-time status updates
- **Package management** with flexible pricing and restrictions

### 📅 **Booking Engine**
- **Comprehensive booking system** with guest assignment and room allocation
- **Interactive availability calendar** with drag-and-drop functionality
- **Check-in/check-out processing** with automated status updates
- **Booking modifications** and room changes with audit trails
- **Invoice generation** with automatic billing and payment tracking

### 👥 **Guest Management**
- **Complete guest profiles** with booking history and preferences
- **Guest clubs and loyalty programs** with membership management
- **Communication tracking** and guest interaction history
- **Demographics and analytics** for marketing insights

### 🧹 **Housekeeping Management** *(Phase 1 Completion)*
- **Room status dashboard** with real-time housekeeping tracking
- **Task assignment and scheduling** with staff coordination
- **Cleaning checklists** with customizable standards and protocols
- **Quality inspection workflows** with completion verification
- **Maintenance request integration** with priority management

### 🔧 **Maintenance System** *(Phase 1 Completion)*
- **Maintenance request tracking** with priority and status management
- **Work order management** with detailed task breakdowns
- **Staff assignment and scheduling** with skill-based routing
- **Parts and cost tracking** with budget management
- **Maintenance history** with equipment lifecycle tracking
- **Professional reporting** with printable work orders

### 📊 **Comprehensive Reporting**
- **Financial reports** with revenue analysis and payment tracking
- **Occupancy analytics** with room utilization insights
- **Booking trends** with forecasting and demand analysis
- **User activity tracking** with detailed audit logs
- **Housekeeping performance** with efficiency metrics
- **Maintenance analytics** with cost and completion tracking
- **Export capabilities** (PDF, Excel, CSV) for all reports

### 👮 **Security & Access Control**
- **Role-based permissions** using Spatie Laravel Permission
- **Fine-grained access control** with property-level restrictions
- **User activity logging** with comprehensive audit trails
- **Secure API endpoints** with Laravel Sanctum authentication
- **CSRF protection** and input validation throughout

### 🎨 **User Experience**
- **Modern responsive design** with Bootstrap 5 and custom CSS
- **Interactive dashboards** with Chart.js visualizations
- **Intuitive navigation** with property context switching
- **Real-time notifications** and status updates
- **Print-optimized reports** with professional layouts
- **Dark/light theme support** with user preferences

---

## 🛠 **Technical Stack**

### **Backend**
- **Laravel 12.x** - Latest PHP framework with cutting-edge features
- **PHP 8.2+** - Modern PHP with improved performance and type safety
- **Multi-tenant Architecture** - Stancl Tenancy for complete isolation
- **MySQL Database** - Reliable and scalable data storage
- **Laravel Sanctum** - API authentication and SPA support

### **Frontend**
- **Bootstrap 5** - Modern responsive CSS framework
- **Chart.js** - Interactive charts and data visualizations
- **jQuery** - DOM manipulation and AJAX requests
- **Select2** - Enhanced select boxes with search capabilities
- **Summernote** - Rich text editing for descriptions and notes
- **Bootstrap Icons** - Comprehensive icon library

### **Key Packages**
- **Spatie Laravel Permission** - Role and permission management
- **Laravel DOMPDF** - PDF generation for reports and invoices
- **Tonysm Rich Text Laravel** - Rich text content management
- **Stancl Tenancy** - Multi-tenancy with domain isolation

---

## 📋 **System Requirements**

### **Server Requirements**
- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer 2.x
- Node.js 18+ & npm (for asset compilation)

### **Recommended Server Specs**
- **CPU**: 2+ cores
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 20GB+ SSD
- **Bandwidth**: Unlimited or high allocation

---

## 🚀 **Quick Start Installation**

### **1. Clone & Setup**
```bash
git clone https://github.com/drycko/ubix-multi-tenant.git
cd ubix-multi-tenant
composer install
npm install
```

### **2. Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

**Edit `.env` with your settings:**
```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ubix_central
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### **3. Database Setup**
```bash
# Create central database
php artisan migrate --seed

# Create default tenant (optional)
php artisan tenants:seed
```

### **4. Asset Compilation**
```bash
# Development
npm run dev

# Production
npm run build
```

### **5. Launch Application**
```bash
php artisan serve
```

**Access your application:**
- **Central Admin**: http://localhost:8000
- **Tenant Sites**: Use configured domains or subdomains

---

## 🏗 **Production Deployment**

### **Web Server Configuration**

**Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### **Production Optimizations**
```bash
# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Asset optimization
npm run build

# Database optimization
php artisan migrate --force
```

---

## 📚 **API Documentation**

### **Authentication**
All API endpoints require authentication using Laravel Sanctum tokens.

**Headers Required:**
```
Authorization: Bearer {your-api-token}
X-Property-ID: {property-id}
Content-Type: application/json
Accept: application/json
```

### **Key API Endpoints**
```
GET    /api/bookings          # List bookings
POST   /api/bookings          # Create booking
GET    /api/bookings/{id}     # Get booking details
PUT    /api/bookings/{id}     # Update booking
DELETE /api/bookings/{id}     # Cancel booking

GET    /api/rooms             # List rooms
GET    /api/rooms/available   # Check availability
GET    /api/rooms/{id}        # Room details

GET    /api/guests            # List guests
POST   /api/guests            # Create guest
GET    /api/guests/{id}       # Guest profile

GET    /api/housekeeping      # Housekeeping tasks
POST   /api/housekeeping      # Create task
PUT    /api/housekeeping/{id} # Update task status

GET    /api/maintenance       # Maintenance requests
POST   /api/maintenance       # Create request
```

**See `routes/api.php` for complete endpoint documentation.**

---

## 🎯 **Business Features**

### **Hospitality Operations**
- ✅ **Booking Management** - Complete reservation system
- ✅ **Room Operations** - Inventory and availability management
- ✅ **Guest Services** - Profile and loyalty management
- ✅ **Housekeeping** - Cleaning and maintenance coordination
- ✅ **Financial Tracking** - Revenue and payment management

### **Administrative Control**
- ✅ **Multi-Property Support** - Centralized management
- ✅ **User Management** - Role-based access control
- ✅ **Reporting Suite** - Comprehensive analytics
- ✅ **Audit Logging** - Complete activity tracking
- ✅ **Data Export** - Flexible reporting formats

### **System Integration**
- ✅ **API Architecture** - RESTful endpoints
- ✅ **Webhook Support** - External system integration
- ✅ **Multi-Tenant Security** - Complete data isolation
- ✅ **Scalable Design** - Enterprise-ready architecture

---

## 🔮 **Phase 2 Development Roadmap**

### **Communication System** 
- Automated booking confirmations and notifications
- Email template management and customization
- SMS integration for guest communications
- Marketing campaign management

### **Channel Management**
- OTA integration (Booking.com, Airbnb, Expedia)
- Rate and availability synchronization
- Channel performance analytics
- Commission tracking and management

### **Payment Gateway Integration**
- Multiple payment processor support
- Automated payment processing
- Refund and chargeback management
- PCI compliance features

### **Advanced PMS Features**
- Guest folio management with itemized charges
- Advanced cancellation policy handling
- No-show processing and automation
- Revenue management and dynamic pricing

### **Mobile & Guest Portal**
- Mobile-responsive guest portal
- Online check-in/check-out capabilities
- Digital key integration
- Guest request management system

---

## 🎨 **Customization**

### **Styling & Branding**
- **CSS Variables**: Modify `resources/css/app.css` for color schemes
- **Logo/Branding**: Update tenant-specific branding assets
- **Dashboard Widgets**: Customize chart configurations in controllers

### **Feature Extensions**
- **Custom Reports**: Extend `ReportController` for specialized analytics
- **Additional Modules**: Follow existing controller patterns
- **API Extensions**: Add endpoints in `routes/api.php`

### **Theme Customization**
```css
/* Custom CSS Variables */
:root {
  --primary-color: #373643;
  --accent-color: #18cb96;
  --background-color: #f8f9fa;
}
```

---

## 🔒 **Security Features**

- **Multi-tenant data isolation** with domain-based separation
- **Role-based access control** with granular permissions
- **CSRF protection** on all forms and API endpoints
- **Input validation** and sanitization throughout
- **Activity logging** with comprehensive audit trails
- **Secure file uploads** with type and size validation
- **API rate limiting** to prevent abuse
- **Database encryption** for sensitive data

---

## 📈 **Performance Features**

- **Database query optimization** with eager loading
- **Caching strategies** for frequently accessed data
- **Asset optimization** with Vite bundling
- **Lazy loading** for large datasets
- **Background job processing** for heavy operations
- **CDN-ready** static asset serving

---

## 🤝 **Contributing**

We welcome contributions to the Ubix platform! Please follow these guidelines:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### **Development Standards**
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Ensure responsive design compatibility

---

## 📄 **License**

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 🆘 **Support & Documentation**

### **Getting Help**
- **GitHub Issues**: Report bugs and request features
- **Documentation**: Comprehensive guides and API docs
- **Community**: Join our developer community

### **Commercial Support**
For enterprise support, custom development, or consulting services, please contact our team.

---

## 🏆 **Acknowledgments**

- **Laravel Framework** - The foundation of our application
- **Stancl Tenancy** - Multi-tenancy architecture
- **Spatie Laravel Permission** - Authorization system
- **Bootstrap Team** - UI framework
- **Chart.js** - Data visualization
- **All Contributors** - Community support and development

---

**Built with ❤️ by the Ubix Development Team**

*Powering the future of hospitality management*