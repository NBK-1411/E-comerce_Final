# Go Outside Platform - Complete Documentation

## Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Technology Stack](#technology-stack)
4. [Installation & Setup](#installation--setup)
5. [Configuration](#configuration)
6. [Database Schema](#database-schema)
7. [API Documentation](#api-documentation)
8. [File Structure](#file-structure)
9. [Features Documentation](#features-documentation)
10. [Security](#security)
11. [Deployment](#deployment)
12. [Development Guidelines](#development-guidelines)
13. [Troubleshooting](#troubleshooting)

---

## Project Overview

**Go Outside** is a comprehensive venue and activity booking platform designed for the Ghanaian market. It connects customers with verified venues and experiences, providing a secure, trust-focused booking system with integrated payment processing.

### Key Features

- **Multi-User System**: Customers, Venue Owners, and Administrators
- **Venue & Activity Listings**: Comprehensive search and discovery
- **Secure Booking System**: Multi-step booking with Paystack payment integration
- **Review System**: Verified reviews with moderation
- **Notification System**: Real-time in-app notifications
- **Theme Support**: Dark/Light mode toggle
- **AI Chatbot**: Intelligent assistant for user support
- **Admin Dashboard**: Complete management interface

### Project Status

- **Completion**: ~95%
- **Production Ready**: Yes (with proper configuration)
- **Last Updated**: January 2025

---

## Architecture

### MVC Pattern

The project follows a strict **Model-View-Controller (MVC)** architecture:

```
┌─────────────────────────────────────────┐
│           Presentation Layer            │
│  (public/, admin/, includes/)           │
│  - Views/Pages                          │
│  - UI Components                        │
│  - JavaScript                           │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│         Business Logic Layer           │
│         (controllers/)                  │
│  - Validation                           │
│  - Business Rules                       │
│  - Data Transformation                 │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│         Data Access Layer               │
│         (classes/)                      │
│  - Database Operations                  │
│  - CRUD Methods                         │
│  - Query Building                       │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│         Database Layer                  │
│         (MySQL)                         │
│  - Tables                               │
│  - Relationships                        │
│  - Constraints                          │
└─────────────────────────────────────────┘
```

### Directory Structure

```
Event-Management-Website/
├── actions/              # JSON API Endpoints
├── admin/                # Admin Panel Pages
├── classes/              # Data Access Layer (Models)
├── controllers/          # Business Logic Layer
├── database/             # SQL Scripts & Seed Data
├── includes/             # Reusable Components
├── public/               # Public-Facing Pages
├── settings/             # Configuration Files
├── uploads/              # User-Uploaded Files
└── index.php             # Landing Page
```

### Request Flow

1. **User Request** → Browser sends HTTP request
2. **Routing** → PHP determines which page/action to load
3. **Authentication** → `core.php` checks session and permissions
4. **Controller** → Business logic processes the request
5. **Model** → Database operations via classes
6. **Response** → JSON (for AJAX) or HTML (for pages)

---

## Technology Stack

### Backend

- **PHP**: 7.4+ (Object-oriented, MVC pattern)
- **MySQL**: 5.7+ (Relational database)
- **Apache**: Web server (via XAMPP)

### Frontend

- **HTML5**: Semantic markup
- **CSS3**: Tailwind CSS (via CDN)
- **JavaScript**: Vanilla JS (ES6+)
- **Libraries**:
  - Tailwind CSS (Utility-first CSS)
  - Font Awesome (Icons)
  - Leaflet.js (Maps)
  - FullCalendar (Calendar views)
  - Tom Select (Enhanced dropdowns)

### Third-Party Integrations

- **Paystack**: Payment gateway
- **Google Maps API**: Geocoding and maps
- **OpenAI API**: AI chatbot (optional)

---

## Installation & Setup

### Prerequisites

- **XAMPP** (or similar: Apache + MySQL + PHP)
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Browser**: Chrome, Firefox, Safari, or Edge
- **Text Editor**: VS Code, PhpStorm, or similar

### Step-by-Step Installation

#### 1. Download/Clone Project

```bash
# Place project in XAMPP htdocs directory
/Applications/XAMPP/xamppfiles/htdocs/Event-Management-Website/
```

#### 2. Start XAMPP Services

- Open XAMPP Control Panel
- Start **Apache** server
- Start **MySQL** server

#### 3. Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database: `EventWave` (or your preferred name)
3. Import SQL file:
   - `EventWave.sql` (main schema)
   - `EventWave_PAYSTACK_UPGRADE.sql` (payment upgrades)
   - `database/create_notifications_table.sql` (notifications)
   - `database/update_activity_types_enum.sql` (activity types)

#### 4. Configure Database Connection

Edit `settings/db_cred.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default is empty
define('DB_NAME', 'EventWave');
```

#### 5. Configure API Keys

Edit `settings/db_cred.php`:

```php
// Google Maps API (Required for geocoding)
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// Paystack API (Required for payments)
define('PAYSTACK_SECRET_KEY', 'sk_test_...');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_...');
define('PAYSTACK_TEST_MODE', true);  // false for production

// OpenAI API (Optional - for chatbot)
define('OPENAI_API_KEY', 'sk-...');  // Optional
```

**Getting API Keys:**

- **Google Maps**: [Google Cloud Console](https://console.cloud.google.com/)
  - Enable "Geocoding API"
  - Create API key
- **Paystack**: [Paystack Dashboard](https://dashboard.paystack.com/)
  - Get test keys from Settings → API Keys & Webhooks
- **OpenAI**: [OpenAI Platform](https://platform.openai.com/)
  - Create API key from API Keys section

#### 6. Set File Permissions

```bash
# Make uploads directory writable
chmod -R 755 uploads/
chmod -R 755 uploads/venues/
chmod -R 755 uploads/activities/
```

#### 7. Access Application

- **Homepage**: `http://localhost/Event-Management-Website/index.php`
- **Admin Panel**: `http://localhost/Event-Management-Website/admin/dashboard.php`

### Default Credentials

**Admin Account:**
- Email: `admin@gooutside.com`
- Password: `admin123`

> ⚠️ **Security**: Change default passwords in production!

---

## Configuration

### Environment Variables

All configuration is in `settings/db_cred.php`:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'EventWave');

// Google Maps
define('GOOGLE_MAPS_API_KEY', 'YOUR_KEY');

// Paystack
define('PAYSTACK_SECRET_KEY', 'sk_test_...');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_...');
define('PAYSTACK_TEST_MODE', true);
define('PAYSTACK_INITIALIZE_URL', 'https://api.paystack.co/transaction/initialize');
define('PAYSTACK_VERIFY_URL', 'https://api.paystack.co/transaction/verify/');

// OpenAI (Optional)
define('OPENAI_API_KEY', 'sk-...');
```

### Session Configuration

Session settings in `settings/core.php`:

```php
// Session lifetime (2 hours)
ini_set('session.gc_maxlifetime', 7200);
session_set_cookie_params(7200);
```

### File Upload Settings

PHP configuration (php.ini):

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

---

## Database Schema

### Core Tables

#### `customer`
User accounts (customers, owners, admins)

| Column | Type | Description |
|--------|------|-------------|
| customer_id | INT | Primary key |
| customer_name | VARCHAR(100) | Full name |
| customer_email | VARCHAR(100) | Email (unique) |
| customer_pass | VARCHAR(255) | Hashed password |
| customer_role | ENUM | 'admin', 'customer', 'owner' |
| customer_country | VARCHAR(50) | Country |
| customer_city | VARCHAR(50) | City |
| customer_contact | VARCHAR(20) | Phone number |
| customer_image | VARCHAR(255) | Profile image path |

#### `venue`
Venue listings

| Column | Type | Description |
|--------|------|-------------|
| venue_id | INT | Primary key |
| title | VARCHAR(255) | Venue name |
| description | TEXT | Full description |
| category_id | INT | Foreign key to categories |
| location_text | VARCHAR(255) | Address |
| gps_code | VARCHAR(50) | GPS coordinates |
| capacity | INT | Max capacity |
| price_per_event | DECIMAL(10,2) | Base price |
| photos_json | JSON | Array of image paths |
| status | ENUM | 'pending', 'approved', 'rejected' |
| created_by | INT | Foreign key to customer |

#### `booking`
Booking requests and confirmations

| Column | Type | Description |
|--------|------|-------------|
| booking_id | INT | Primary key |
| venue_id | INT | Foreign key to venue |
| user_id | INT | Foreign key to customer |
| booking_date | DATE | Event date |
| start_time | TIME | Start time |
| end_time | TIME | End time |
| number_of_guests | INT | Guest count |
| special_requirements | TEXT | Special requests |
| status | ENUM | 'pending', 'confirmed', 'cancelled', 'completed' |
| qr_reference | VARCHAR(50) | QR code reference |

#### `payment`
Payment transactions

| Column | Type | Description |
|--------|------|-------------|
| payment_id | INT | Primary key |
| booking_id | INT | Foreign key to booking |
| user_id | INT | Foreign key to customer |
| amount | DECIMAL(10,2) | Payment amount |
| currency | VARCHAR(3) | Currency code (GHS) |
| payment_method | VARCHAR(50) | 'paystack', 'reservation' |
| paystack_reference | VARCHAR(100) | Paystack transaction ref |
| channel | VARCHAR(50) | Payment channel |
| status | ENUM | 'pending', 'completed', 'failed' |
| meta_json | JSON | Additional payment data |

#### `review`
Venue reviews

| Column | Type | Description |
|--------|------|-------------|
| review_id | INT | Primary key |
| venue_id | INT | Foreign key to venue |
| user_id | INT | Foreign key to customer |
| booking_id | INT | Foreign key to booking (verification) |
| rating | INT | 1-5 stars |
| comment | TEXT | Review text |
| status | ENUM | 'pending', 'approved', 'rejected' |
| is_verified | BOOLEAN | Verified booking badge |

#### `notifications`
In-app notifications

| Column | Type | Description |
|--------|------|-------------|
| notification_id | INT | Primary key |
| recipient_id | INT | Foreign key to customer |
| notification_type | VARCHAR(50) | Type identifier |
| title | VARCHAR(255) | Notification title |
| message | TEXT | Notification message |
| is_read | BOOLEAN | Read status |
| related_booking_id | INT | Optional foreign key |
| related_venue_id | INT | Optional foreign key |
| created_at | TIMESTAMP | Creation time |

### Relationships

```
customer (1) ──< (many) venue
venue (1) ──< (many) booking
booking (1) ──< (many) payment
venue (1) ──< (many) review
customer (1) ──< (many) notifications
```

---

## API Documentation

### Authentication Endpoints

#### `POST /actions/login_action.php`

User login.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "customer"
  }
}
```

#### `POST /actions/register_customer_action.php`

User registration.

**Request:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "country": "Ghana",
  "city": "Accra",
  "contact": "+233241234567",
  "role": "customer"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful"
}
```

### Venue Endpoints

#### `GET /actions/venue_get_action.php?id={venue_id}`

Get venue details.

**Response:**
```json
{
  "success": true,
  "venue": {
    "venue_id": 1,
    "title": "Venue Name",
    "description": "...",
    "category": "Restaurants",
    "location": "Accra, Ghana",
    "capacity": 100,
    "price": 500.00,
    "photos": ["path1.jpg", "path2.jpg"],
    "rating": 4.5,
    "review_count": 10
  }
}
```

#### `POST /actions/search_venues_action.php`

Search venues.

**Request:**
```json
{
  "query": "restaurant",
  "category": "Restaurants",
  "location": "Accra",
  "min_price": 0,
  "max_price": 1000,
  "min_capacity": 10
}
```

**Response:**
```json
{
  "success": true,
  "venues": [...],
  "total": 25
}
```

### Booking Endpoints

#### `POST /actions/booking_create_action.php`

Create booking.

**Request:**
```json
{
  "venue_id": 1,
  "booking_date": "2025-02-15",
  "start_time": "18:00:00",
  "end_time": "22:00:00",
  "number_of_guests": 10,
  "special_requirements": "Birthday party",
  "payment_method": "paystack"
}
```

**Response:**
```json
{
  "success": true,
  "booking_id": 123,
  "message": "Booking created successfully"
}
```

### Payment Endpoints

#### `POST /actions/payment_init_paystack_action.php`

Initialize Paystack payment.

**Request:**
```json
{
  "booking_id": 123,
  "amount": 500.00,
  "callback_url": "http://localhost/Event-Management-Website/actions/payment_verify_paystack_action.php"
}
```

**Response:**
```json
{
  "success": true,
  "authorization_url": "https://checkout.paystack.com/...",
  "reference": "EVT_123_1234567890",
  "payment_id": 456
}
```

#### `GET /actions/payment_verify_paystack_action.php?reference={ref}`

Verify Paystack payment (callback).

**Response:**
- Redirects to booking confirmation on success
- Redirects to booking page on failure

### Notification Endpoints

#### `GET /actions/get_notifications_action.php`

Get user notifications.

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "notification_id": 1,
      "title": "New Booking",
      "message": "You have a new booking request",
      "is_read": false,
      "created_at": "2025-01-15 10:30:00"
    }
  ],
  "unread_count": 5
}
```

#### `POST /actions/mark_notification_read_action.php`

Mark notification as read.

**Request:**
```json
{
  "notification_id": 1
}
```

### Chatbot Endpoint

#### `POST /actions/chatbot_action.php`

AI chatbot interaction.

**Request:**
```json
{
  "message": "Find me a restaurant",
  "history": [
    {"role": "user", "content": "Hello"},
    {"role": "assistant", "content": "Hi! How can I help?"}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "response": "You can search for venues by name, location, or category. <a href='/Event-Management-Website/public/search.php'>Browse venues</a> or use the search bar on the homepage."
}
```

---

## File Structure

### Core Files

```
Event-Management-Website/
│
├── index.php                          # Landing page
├── README.md                          # Quick start guide
├── DOCUMENTATION.md                   # This file
├── PROJECT_SUMMARY.md                 # Feature summary
│
├── settings/                          # Configuration
│   ├── core.php                      # Session, auth, helpers
│   ├── db_class.php                  # Database wrapper
│   └── db_cred.php                   # Credentials & API keys
│
├── classes/                           # Data Access Layer
│   ├── customer_class.php            # User operations
│   ├── venue_class.php               # Venue operations
│   ├── booking_class.php             # Booking operations
│   ├── payment_class.php             # Payment operations
│   ├── review_class.php              # Review operations
│   ├── category_class.php            # Category operations
│   ├── activity_class.php            # Activity operations
│   └── notification_class.php        # Notification operations
│
├── controllers/                       # Business Logic Layer
│   ├── customer_controller.php
│   ├── venue_controller.php
│   ├── booking_controller.php
│   ├── payment_controller.php
│   ├── review_controller.php
│   ├── category_controller.php
│   ├── activity_controller.php
│   └── notification_controller.php
│
├── actions/                           # API Endpoints
│   ├── login_action.php
│   ├── register_customer_action.php
│   ├── logout_action.php
│   ├── venue_get_action.php
│   ├── search_venues_action.php
│   ├── booking_create_action.php
│   ├── payment_init_paystack_action.php
│   ├── payment_verify_paystack_action.php
│   ├── review_add_action.php
│   ├── get_notifications_action.php
│   ├── chatbot_action.php
│   └── ... (25+ endpoints)
│
├── public/                            # Public Pages
│   ├── login.php                     # Login page
│   ├── register.php                  # Registration page
│   ├── search.php                    # Search/browse page
│   ├── venue_detail.php              # Venue details
│   ├── booking.php                   # Booking form
│   ├── booking_confirmation.php      # Confirmation page
│   ├── profile.php                   # User profile
│   ├── owner_dashboard.php           # Owner dashboard
│   ├── venue_dashboard.php           # Venue management
│   └── create_venue.php              # Create venue form
│
├── admin/                             # Admin Panel
│   ├── dashboard.php                 # Admin dashboard
│   ├── venues.php                    # Venue management
│   ├── bookings.php                  # Booking management
│   ├── reviews.php                   # Review moderation
│   ├── users.php                     # User management
│   ├── category.php                  # Category management
│   ├── reports.php                   # Reports & analytics
│   └── includes/
│       └── sidebar.php               # Admin sidebar
│
├── includes/                          # Reusable Components
│   ├── site_nav.php                  # Navigation bar
│   └── chatbot_widget.php            # AI chatbot widget
│
├── database/                          # Database Scripts
│   ├── EventWave.sql                 # Main schema
│   ├── EventWave_PAYSTACK_UPGRADE.sql
│   ├── create_notifications_table.sql
│   ├── update_activity_types_enum.sql
│   └── populate_*.php                # Seed data scripts
│
└── uploads/                           # User Uploads
    ├── venues/                        # Venue images
    └── activities/                    # Activity images
```

---

## Features Documentation

### 1. User Authentication

**Files:**
- `public/login.php`
- `public/register.php`
- `actions/login_action.php`
- `actions/register_customer_action.php`

**Features:**
- Email/password authentication
- Role-based registration (Customer, Owner, Admin)
- Session management
- Password hashing (bcrypt)
- Remember me functionality

### 2. Venue Discovery

**Files:**
- `index.php` (Homepage)
- `public/search.php`
- `public/venue_detail.php`

**Features:**
- Search by name, location, category
- Advanced filters (price, capacity, rating)
- Category-based browsing
- Venue detail pages with:
  - Photo galleries
  - Reviews and ratings
  - Availability calendar
  - Location map
  - Booking form

### 3. Booking System

**Files:**
- `public/booking.php`
- `public/booking_confirmation.php`
- `actions/booking_create_action.php`

**Features:**
- Multi-step booking form
- Date/time selection
- Guest count
- Special requirements
- Payment integration (Paystack)
- Booking confirmation with QR code
- Email notifications (optional)

### 4. Payment Processing

**Files:**
- `actions/payment_init_paystack_action.php`
- `actions/payment_verify_paystack_action.php`
- `controllers/payment_controller.php`

**Features:**
- Paystack integration
- Secure payment flow
- Transaction verification
- Payment history
- Refund processing (manual)

### 5. Review System

**Files:**
- `actions/review_add_action.php`
- `admin/reviews.php`
- `controllers/review_controller.php`

**Features:**
- Verified reviews (only from completed bookings)
- Rating system (1-5 stars)
- Review moderation
- Report functionality
- Review display on venue pages

### 6. Admin Dashboard

**Files:**
- `admin/dashboard.php`
- `admin/venues.php`
- `admin/bookings.php`
- `admin/reviews.php`
- `admin/users.php`
- `admin/category.php`

**Features:**
- Dashboard overview
- Venue approval/rejection
- Booking management
- Review moderation
- User management
- Category CRUD
- Analytics (basic)

### 7. Owner Dashboard

**Files:**
- `public/owner_dashboard.php`
- `public/venue_dashboard.php`
- `public/create_venue.php`

**Features:**
- Venue creation/editing
- Photo upload
- Availability management
- Booking requests
- Revenue tracking
- Performance analytics

### 8. Notification System

**Files:**
- `actions/get_notifications_action.php`
- `includes/site_nav.php` (notification bell)
- `classes/notification_class.php`

**Features:**
- Real-time notifications
- Notification dropdown
- Mark as read
- Notification types:
  - Booking requests
  - Booking confirmations
  - Review approvals
  - Venue approvals

### 9. Theme Toggle

**Files:**
- `includes/site_nav.php`
- Theme-aware CSS variables

**Features:**
- Dark/Light mode
- Persistent theme preference (localStorage)
- Theme-aware components
- Smooth transitions

### 10. AI Chatbot

**Files:**
- `includes/chatbot_widget.php`
- `actions/chatbot_action.php`

**Features:**
- OpenAI integration (optional)
- Rule-based fallback
- Context-aware responses
- Database integration
- Clickable links in responses
- User-specific context

---

## Security

### Implemented Security Measures

1. **Password Security**
   - Bcrypt hashing
   - Minimum password requirements
   - No plaintext storage

2. **SQL Injection Prevention**
   - Prepared statements for all queries
   - Parameter binding
   - Input sanitization

3. **XSS Prevention**
   - `htmlspecialchars()` for output
   - Content Security Policy (recommended)
   - HTML sanitization for user input

4. **Session Security**
   - Secure session handling
   - Session timeout
   - CSRF token support (structure in place)

5. **Access Control**
   - Role-based authorization
   - Page-level access checks
   - API endpoint protection

6. **File Upload Security**
   - File type validation
   - File size limits
   - Secure file storage

### Security Best Practices

- ✅ Use HTTPS in production
- ✅ Keep PHP and MySQL updated
- ✅ Regular security audits
- ✅ Monitor error logs
- ✅ Implement rate limiting (recommended)
- ✅ Use environment variables for secrets (recommended)

---

## Deployment

### Production Checklist

- [ ] Update database credentials
- [ ] Set `PAYSTACK_TEST_MODE` to `false`
- [ ] Configure production Paystack keys
- [ ] Set up SSL certificate (HTTPS)
- [ ] Update `GOOGLE_MAPS_API_KEY` restrictions
- [ ] Configure file upload limits
- [ ] Set proper file permissions
- [ ] Enable error logging
- [ ] Disable debug mode
- [ ] Set up database backups
- [ ] Configure email notifications
- [ ] Test payment flow
- [ ] Load test the application

### Server Requirements

- **PHP**: 7.4+ (8.0+ recommended)
- **MySQL**: 5.7+ (8.0+ recommended)
- **Apache**: 2.4+ (or Nginx)
- **Extensions**: 
  - mysqli
  - curl
  - json
  - mbstring
  - gd (for image processing)

### Deployment Steps

1. **Upload Files**
   ```bash
   # Upload all files to server
   scp -r Event-Management-Website/ user@server:/var/www/html/
   ```

2. **Set Permissions**
   ```bash
   chmod -R 755 /var/www/html/Event-Management-Website/
   chmod -R 777 /var/www/html/Event-Management-Website/uploads/
   ```

3. **Configure Database**
   - Create production database
   - Import SQL files
   - Update `db_cred.php`

4. **Configure Apache**
   ```apache
   <VirtualHost *:80>
       ServerName gooutside.com
       DocumentRoot /var/www/html/Event-Management-Website
       
       <Directory /var/www/html/Event-Management-Website>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. **SSL Setup**
   - Install Let's Encrypt certificate
   - Redirect HTTP to HTTPS

---

## Development Guidelines

### Code Style

- **Naming Conventions**:
  - Classes: `PascalCase` (e.g., `CustomerClass`)
  - Functions: `snake_case` (e.g., `get_user_by_id`)
  - Variables: `snake_case` (e.g., `$user_id`)
  - Constants: `UPPER_CASE` (e.g., `DB_HOST`)

- **File Organization**:
  - One class per file
  - Controllers handle business logic
  - Classes handle data access
  - Actions return JSON

### Adding New Features

1. **Database**: Add/modify tables
2. **Class**: Create data access methods
3. **Controller**: Add business logic
4. **Action**: Create JSON endpoint
5. **View**: Build UI
6. **JavaScript**: Add frontend logic

### Example: Adding a New Feature

**Feature**: User favorites/bookmarks

1. **Database**:
   ```sql
   CREATE TABLE saved_venues (
       id INT PRIMARY KEY AUTO_INCREMENT,
       user_id INT,
       venue_id INT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES customer(customer_id),
       FOREIGN KEY (venue_id) REFERENCES venue(venue_id)
   );
   ```

2. **Class** (`classes/saved_venue_class.php`):
   ```php
   class SavedVenue {
       public function save_venue($user_id, $venue_id) { ... }
       public function get_saved_venues($user_id) { ... }
   }
   ```

3. **Controller** (`controllers/saved_venue_controller.php`):
   ```php
   function save_venue_ctr($user_id, $venue_id) { ... }
   ```

4. **Action** (`actions/save_venue_action.php`):
   ```php
   // Handle AJAX request, return JSON
   ```

5. **View**: Add UI elements to venue detail page

6. **JavaScript**: Handle save/unsave actions

### Testing

- **Manual Testing**: Test all user flows
- **Browser Testing**: Chrome, Firefox, Safari, Edge
- **Mobile Testing**: Responsive design
- **Payment Testing**: Use Paystack test keys
- **Error Handling**: Test error scenarios

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Error

**Error**: `Connection failed: Access denied`

**Solution**:
- Check `settings/db_cred.php` credentials
- Verify MySQL is running
- Check database name exists

#### 2. Payment Not Working

**Error**: Payment initialization fails

**Solution**:
- Verify Paystack API keys
- Check `PAYSTACK_TEST_MODE` setting
- Verify callback URL is correct
- Check server can make HTTPS requests

#### 3. Images Not Uploading

**Error**: Upload fails or images don't display

**Solution**:
- Check `uploads/` directory permissions (755 or 777)
- Verify PHP `upload_max_filesize` setting
- Check file path in database
- Verify image paths are relative to project root

#### 4. Session Issues

**Error**: User logged out unexpectedly

**Solution**:
- Check `session.gc_maxlifetime` in php.ini
- Verify session directory is writable
- Check browser cookie settings

#### 5. Google Maps Not Loading

**Error**: Maps don't display

**Solution**:
- Verify `GOOGLE_MAPS_API_KEY` is set
- Check API key restrictions
- Verify Geocoding API is enabled
- Check browser console for errors

#### 6. Chatbot Not Responding

**Error**: Chatbot returns errors

**Solution**:
- Check `OPENAI_API_KEY` is set (if using OpenAI)
- Verify API key is valid
- Check fallback responses work
- Review error logs

### Debug Mode

Enable debug mode in `settings/core.php`:

```php
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

> ⚠️ **Disable in production!**

### Error Logs

- **PHP Errors**: `/Applications/XAMPP/xamppfiles/logs/php_error_log`
- **Apache Errors**: `/Applications/XAMPP/xamppfiles/logs/error_log`
- **MySQL Errors**: Check MySQL error log

### Performance Optimization

1. **Database**:
   - Add indexes on frequently queried columns
   - Use `EXPLAIN` to analyze queries
   - Optimize JOIN queries

2. **Caching**:
   - Implement Redis/Memcached (recommended)
   - Cache category lists
   - Cache venue search results

3. **Images**:
   - Compress images before upload
   - Use CDN for static assets
   - Implement lazy loading

---

## Additional Resources

### Documentation Files

- `README.md` - Quick start guide
- `PROJECT_SUMMARY.md` - Feature summary
- `GOOGLE_MAPS_SETUP.md` - Maps API setup
- `UPGRADE_SUMMARY.md` - Upgrade notes

### External Documentation

- [Paystack API Docs](https://paystack.com/docs/api/)
- [Google Maps API Docs](https://developers.google.com/maps/documentation)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [PHP Manual](https://www.php.net/manual/)

---

## Support & Contact

For issues, questions, or contributions:

- **Email**: info@gooutside.com
- **Documentation**: See this file and README.md
- **Code Comments**: Check inline comments in code

---

**Last Updated**: January 2025  
**Version**: 2.0  
**Status**: Production Ready

---

*Built with ❤️ for the Ghanaian market*

