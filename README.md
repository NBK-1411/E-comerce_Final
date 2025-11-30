# Go Outside - Venue Booking Platform

A trust-focused platform for discovering, verifying, and booking venues and experiences in Ghana. Built with PHP (MVC architecture), MySQL, and Tailwind CSS.

## 🌟 Features

### For Customers
- **Browse & Search**: Filter venues by location (GhanaPost GPS), category, budget, capacity
- **Venue Details**: Comprehensive information including photos, floor plans, house rules, safety notes
- **Verified Reviews**: Badge system for reviews from verified bookings
- **Request-to-Book**: Secure MoMo escrow deposit system
- **Profile Management**: View bookings, payments, and past visits
- **QR Code Booking**: Get QR code for venue check-in

### For Venue Owners
- **Onboarding**: KYC/KYB verification with Ghana Card
- **Listing Management**: Create/edit listings, upload photos, set availability
- **Booking Management**: Approve/decline booking requests
- **Dashboard**: Track views, inquiries, bookings, and payouts
- **Flexible Policies**: Set cancellation policies (flexible/standard/strict)

### For Administrators
- **Category Management**: CRUD operations for venue categories
- **Moderation**: Review flagged content and user reports
- **Venue Approval**: Approve/reject venue listings
- **User Management**: Manage customers and venue owners

### Trust & Safety
- KYC/KYB verification for venue owners
- Verified attendee badges on reviews (tied to completed bookings)
- Report/review moderation queue
- Anti-spam and duplicate review checks
- Mobile money escrow system
- Dispute resolution flow

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ (MVC Architecture)
- **Database**: MySQL 5.7+ (dbforlab)
- **Frontend**: Tailwind CSS (CDN), Vanilla JavaScript
- **AJAX**: Fetch API for JSON endpoints
- **Libraries**: Tom Select (for searchable dropdowns), Font Awesome (icons)

## 📁 Project Structure

```
Event-Management-Website-Template/
├── index.php                 # Landing page
├── dbforlab.sql             # Database schema with seed data
├── README.md                # This file
│
├── settings/                # Configuration
│   ├── db_cred.php         # Database credentials
│   ├── db_class.php        # Database connection class
│   └── core.php            # Session & authorization functions
│
├── classes/                 # Data Access Layer
│   ├── customer_class.php
│   ├── category_class.php
│   ├── venue_class.php
│   ├── booking_class.php
│   ├── payment_class.php
│   └── review_class.php
│
├── controllers/             # Business Logic Layer
│   ├── customer_controller.php
│   ├── category_controller.php
│   ├── venue_controller.php
│   ├── booking_controller.php
│   ├── payment_controller.php
│   └── review_controller.php
│
├── actions/                 # JSON API Endpoints
│   ├── login_action.php
│   ├── register_customer_action.php
│   ├── logout_action.php
│   ├── fetch_category_action.php
│   ├── add_category_action.php
│   ├── update_category_action.php
│   ├── delete_category_action.php
│   ├── search_venues_action.php
│   ├── venue_get_action.php
│   ├── booking_request_action.php
│   └── review_add_action.php
│
├── public/                  # User-facing pages
│   ├── login.php
│   ├── register.php
│   ├── search.php
│   ├── venue_detail.php
│   ├── profile.php
│   ├── venue_dashboard.php  # For venue owners
│   └── js/
│       └── category.js
│
├── admin/                   # Admin pages
│   ├── category.php
│   └── moderation.php
│
├── images/                  # Static assets
├── css/                     # Legacy CSS (from template)
└── js/                      # Legacy JS (from template)
```

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (or any Apache + MySQL + PHP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   # Place the project folder in your XAMPP htdocs directory
   # Path: /Applications/XAMPP/xamppfiles/htdocs/Event-Management-Website-Template/
   ```

2. **Start XAMPP Services**
   - Start Apache server
   - Start MySQL server

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `dbforlab`
   - Import the SQL file: `dbforlab.sql`
   - This will create all tables and insert seed data

4. **Configure Database Connection**
   - Open `settings/db_cred.php`
   - Update credentials if needed (default settings work with XAMPP):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'EventWave');
   ```

5. **Configure Google Maps API Key (Required for Address Search)**
   - Get a Google Maps API key:
     1. Go to [Google Cloud Console](https://console.cloud.google.com/)
     2. Create a new project or select an existing one
     3. Enable the **Geocoding API**:
        - Navigate to "APIs & Services" → "Library"
        - Search for "Geocoding API"
        - Click "Enable"
     4. Create credentials:
        - Go to "APIs & Services" → "Credentials"
        - Click "Create Credentials" → "API Key"
        - Copy your API key
     5. (Optional) Restrict the API key:
        - Click on the API key to edit it
        - Under "API restrictions", select "Restrict key"
        - Choose "Geocoding API"
        - Under "Application restrictions", you can restrict by IP or HTTP referrer
   - Update `settings/db_cred.php`:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE');
   ```
   - **Note**: Google Maps API has a free tier (usually $200/month credit), which is sufficient for most use cases.

6. **Access the Application**
   - Main site: http://localhost/Event-Management-Website-Template/index.php
   - Or if renamed: http://localhost/go-outside/index.php

### Default Credentials

**Admin Account**
- Email: `admin@gooutside.com`
- Password: `admin123`

**Venue Owner Account**
- Email: `owner@example.com`
- Password: `password123`

> ⚠️ **Important**: Change these passwords in production!

## 📖 User Guide

### For Customers

1. **Register an Account**
   - Go to Register page
   - Fill in your details (name, email, password, country, city, contact)
   - Select "Customer" as account type
   - Submit to create account

2. **Browse Venues**
   - Use the search bar on homepage
   - Or visit the Browse page to filter by category, location, price, capacity

3. **View Venue Details**
   - Click "View Details" on any venue
   - See photos, description, rules, reviews, and ratings
   - Check availability and pricing

4. **Book a Venue**
   - Select date, time, and guest count
   - Review booking summary and deposit amount
   - Submit booking request
   - Make MoMo payment (placeholder - actual integration needed)
   - Receive booking confirmation with QR code

5. **Leave a Review**
   - After your event, go to your profile
   - Find the completed booking
   - Leave a rating and review (will be verified)

### For Venue Owners

1. **Register as Venue Owner**
   - Register and select "Venue Owner" as account type
   - Complete KYC verification (Ghana Card)

2. **Create a Listing**
   - Go to Venue Dashboard
   - Click "Add New Venue"
   - Fill in all details (title, description, category, location, capacity, pricing)
   - Upload photos
   - Set house rules, cancellation policy, deposit percentage
   - Submit for admin approval

3. **Manage Bookings**
   - View incoming booking requests
   - Approve or decline requests
   - Track confirmed bookings
   - Mark bookings as completed after event

4. **View Analytics**
   - See booking statistics
   - Track revenue and payouts
   - View reviews and ratings

### For Administrators

1. **Login as Admin**
   - Use admin credentials
   - Access Admin Panel from navigation

2. **Manage Categories**
   - Add, edit, or delete venue categories
   - Categories help customers find venues

3. **Moderate Content**
   - Review flagged reviews
   - Approve/reject venue listings
   - Handle user reports
   - Resolve disputes

## 🗃️ Database Schema

### Main Tables

- **customer**: Users (customers, venue owners, admins)
- **categories**: Venue categories
- **venue**: Venue listings
- **venue_availability**: Time slot availability
- **booking**: Booking requests and confirmations
- **payment**: Payment transactions (MoMo escrow)
- **review**: Venue reviews with verification
- **review_report**: Reported reviews
- **dispute**: Booking disputes
- **venue_tags**: Tags for venue features
- **venue_tag_relation**: Many-to-many relationship

### User Roles

- `1` = Admin
- `2` = Customer (default)
- `3` = Venue Owner

## 🔒 Security Features

- **Password Hashing**: Using PHP `password_hash()` with bcrypt
- **Prepared Statements**: All database queries use prepared statements
- **Input Validation**: Client-side and server-side validation
- **Session Management**: Secure session handling
- **CSRF Protection**: Token-based CSRF protection (placeholders in code)
- **Role-Based Access**: Authorization checks on all protected pages

## 🎨 Design & UI

- **Mobile-First**: Fully responsive design
- **Dark Theme**: Modern dark interface with orange (#ff5518) accents
- **Tailwind CSS**: Utility-first CSS framework via CDN
- **Font Awesome**: Icon library
- **Smooth Animations**: Hover effects and transitions
- **Accessibility**: Semantic HTML, keyboard navigation support

## 🌍 Localization

The platform supports English by default. Placeholder structure for multi-language support (Twi, Ga, Ewe) can be added using i18n keys.

## 📱 Mobile Features

- Responsive navigation with mobile menu
- Touch-friendly buttons and forms
- Optimized images for low data usage
- WhatsApp integration placeholder for notifications

## 🔄 Future Enhancements

- [ ] Actual MoMo API integration (MTN, Vodafone, AirtelTigo)
- [ ] WhatsApp notification integration
- [ ] Email verification
- [ ] Two-factor authentication
- [ ] Advanced analytics dashboard
- [ ] Calendar view for availability
- [ ] Multi-language support (Twi, Ga, Ewe)
- [ ] Real-time chat between customers and owners
- [ ] Advanced search with map view
- [ ] Push notifications
- [ ] Social media integration
- [ ] Voucher/coupon system
- [ ] Guest list management
- [ ] Photo gallery with lightbox
- [ ] Video tours
- [ ] 360° venue views

## 🐛 Known Issues

- MoMo payment integration is placeholder-only (needs actual API)
- Photo upload functionality needs to be implemented
- QR code generation needs implementation
- Email notifications are not yet configured
- WhatsApp integration is placeholder

## 📝 Development Notes

### Adding a New Feature

1. **Database**: Add/modify tables in `dbforlab.sql`
2. **Class**: Create data access methods in `classes/`
3. **Controller**: Add business logic in `controllers/`
4. **Action**: Create JSON endpoint in `actions/`
5. **UI**: Build user interface in `public/` or `admin/`
6. **JS**: Add frontend logic if needed

### Code Conventions

- Use prepared statements for all database queries
- Return JSON from all action endpoints
- Follow MVC separation strictly
- Use meaningful variable and function names
- Comment complex logic
- Validate input on both client and server side

## 📄 License

This project is built for educational purposes as part of a web development course.

## 👥 Contributors

- Development Team: [Your Name/Team Name]
- Original Template: WebThemez (Event Management Template)

## 📞 Support

For issues or questions:
- Email: info@gooutside.com
- Phone: +233 24 123 4567

## 🙏 Acknowledgments

- Event Management Website Template by WebThemez (for design inspiration)
- Tailwind CSS for the utility-first CSS framework
- Font Awesome for icons
- Tom Select for enhanced dropdowns

---

**Built with ❤️ in Ghana**

