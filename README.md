# Rental Management SaaS (RMS)

A multi-tenant property management platform designed for property management companies to efficiently manage properties, collect rent, and handle financial operations.

## 🏗️ Architecture Overview

This is a **Multi-Tenant Marketplace Platform** that serves property management companies with two distinct pricing models:

### 1. Payment Processing Model
- Companies process payments through the platform
- Platform fees collected (typically 10% of rent)
- Company cashouts with a 3% platform fee
- Property owners have view-only access to track earnings

### 2. Listings-Only Model
- Weekly: KES 500/week
- Monthly: KES 1,500/month
- Annual: KES 15,000/year
- Companies handle payments offline

## 📋 Project Structure

```
RMS/
├── rental_saas_final_arch.txt      # Complete architecture documentation
├── rental_saas_db_schema.sql       # PostgreSQL database schema
├── rental_saas_dev_phases.txt      # Development phases and timeline
├── rental_saas_missing_pieces.txt  # Features to implement
├── rental_saas_api_reference.txt   # API documentation
├── README.md                       # This file
└── .gitignore                      # Git ignore rules
```

## 🗄️ Database Schema

The platform uses PostgreSQL (Supabase) with the following key tables:

### Core Tables
- `platform_users` - Platform administrators
- `tenants` - Property management companies
- `company_balances` - Financial tracking for companies
- `owner_balances` - Property owner earnings tracking
- `properties` - Property listings
- `units` - Individual rental units
- `users` - Company staff members
- `property_owners` - Property owners
- `tenants` - Rental tenants
- `payments` - Payment records
- `leases` - Lease agreements

### Financial Tables
- `balance_transactions` - Audit log for all financial movements
- `owner_payment_records` - Records of owner payouts
- `company_cashouts` - Company withdrawal requests

## 💰 Revenue Model

### Platform Revenue Sources
1. **Payment Processing Companies**: 3% fee on company cashouts
2. **Listings-Only Companies**: Fixed subscription fees

### Money Flow
1. Tenant pays rent → Platform account
2. Automatic split:
   - Company platform fees (10%)
   - Owner earnings (90% minus expenses)
3. Company requests cashout → Platform takes 3% fee
4. Company pays owners offline (outside platform)

## 🚀 Getting Started

### Prerequisites
- PostgreSQL 15+
- Laravel 10+
- React 18+
- Node.js 18+
- Composer
- npm/yarn

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd RMS
```

2. **Backend Setup (Laravel)**
```bash
composer create-project laravel/laravel rental-saas-api
cd rental-saas-api
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require stripe/stripe-php
composer require safaricom/mpesa
```

3. **Frontend Setup (React)**
```bash
npm create vite@latest rental-saas-frontend -- --template react
cd rental-saas-frontend
npm install axios react-router-dom
npm install @headlessui/react @heroicons/react
npm install tailwindcss postcss autoprefixer
```

4. **Database Setup**
- Create Supabase project
- Configure database connection
- Run migrations: `php artisan migrate`

5. **Environment Configuration**
```bash
cp .env.example .env
# Configure database, Stripe, M-Pesa keys
```

## 📅 Development Phases

### Phase 0: Foundation (Weeks 1-2)
- Environment setup
- Database schema implementation
- Core models and migrations

### Phase 1: Core Features (Weeks 3-8)
- Authentication system
- Company registration
- Property management
- Tenant management
- Payment processing

### Phase 2: Advanced Features (Weeks 9-14)
- Reporting and analytics
- Owner dashboards
- Notification system
- Document management

### Phase 3: Premium Features (Weeks 15-20)
- Mobile applications
- Advanced analytics
- API integrations
- Automated workflows

## 🔧 Technology Stack

### Backend
- **Framework**: Laravel 10
- **Database**: PostgreSQL (Supabase)
- **Authentication**: Laravel Sanctum
- **Payment**: Stripe, M-Pesa API
- **Queue**: Redis

### Frontend
- **Framework**: React 18
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **UI Components**: Headless UI
- **State Management**: React Context
- **Charts**: Recharts

### Infrastructure
- **Hosting**: AWS/DigitalOcean
- **Database**: Supabase
- **File Storage**: AWS S3
- **Email**: SendGrid
- **Monitoring**: Sentry

## 📊 Key Features

### For Property Management Companies
- Multi-property management
- Tenant screening and management
- Automated rent collection
- Expense tracking
- Financial reporting
- Owner payment tracking

### For Property Owners
- View-only dashboard
- Performance analytics
- Payment history
- Occupancy reports
- Property comparisons

### For Tenants
- Online rent payments
- Maintenance requests
- Document access
- Communication portal

## 🔐 Security Features

- Multi-tenant data isolation
- Role-based access control
- Encrypted sensitive data
- Audit logging
- PCI compliance for payments
- GDPR compliance ready

## 📈 Scalability Considerations

- Horizontal scaling ready
- Database indexing optimized
- Queue system for background jobs
- Caching layer implemented
- CDN for static assets
- Load balancer friendly

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📝 License

This project is proprietary and owned by [Platform Owner].

## 📞 Support

For support and inquiries:
- Email: support@rms-platform.com
- Phone: +254 XXX XXX XXX

## 🗺️ Roadmap

- [ ] Q1 2025: MVP launch
- [ ] Q2 2025: Mobile apps
- [ ] Q3 2025: Advanced analytics
- [ ] Q4 2025: International expansion
- [ ] Q1 2026: AI-powered features

---

**Note**: This is a comprehensive documentation of the Rental Management SaaS platform. For detailed API documentation, refer to `rental_saas_api_reference.txt`. For complete database schema, see `rental_saas_db_schema.sql`.
