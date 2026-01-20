# Week 8: Public Marketplace & Reservations

## 📋 Overview

This week focuses on building the **public-facing marketplace** where potential tenants can:
- Browse available units without authentication
- Search and filter units by location, price, bedrooms, etc.
- View unit details with photos and amenities
- Submit rental inquiries
- Reserve units with deposit payment

---

## 🎯 Objectives

1. **Public Unit Search** - Allow anyone to search available units
2. **Unit Details** - Display comprehensive unit information publicly
3. **Rental Inquiries** - Accept inquiries from potential tenants
4. **Reservations** - Allow unit reservation with deposit (7-day hold)

---

## 📁 Files Created/Modified

### Backend

#### Controllers
- `app/Http/Controllers/Api/Public/PublicUnitController.php` - Public unit search & details
- `app/Http/Controllers/Api/Public/PublicRentalInquiryController.php` - Inquiry submission
- `app/Http/Controllers/Api/Public/PublicReservationController.php` - Reservation creation

#### Form Requests
- `app/Http/Requests/Public/SearchUnitsRequest.php` - Search validation
- `app/Http/Requests/Public/StoreRentalInquiryRequest.php` - Inquiry validation
- `app/Http/Requests/Public/StoreReservationRequest.php` - Reservation validation

#### API Resources
- `app/Http/Resources/PublicUnitResource.php` - Sanitized unit data
- `app/Http/Resources/PublicUnitCollection.php` - Paginated unit list
- `app/Http/Resources/RentalInquiryResource.php` - Inquiry response
- `app/Http/Resources/ReservationResource.php` - Reservation response

#### Routes
- `routes/api.php` - Added public routes prefix

### Frontend

#### Pages
- `src/pages/public/UnitSearch.jsx` - Main search page
- `src/pages/public/UnitDetails.jsx` - Unit details page
- `src/pages/public/InquiryForm.jsx` - Rental inquiry form
- `src/pages/public/ReservationCheckout.jsx` - Reservation checkout

#### Components
- `src/components/public/SearchFilters.jsx` - Filter sidebar
- `src/components/public/PublicUnitCard.jsx` - Unit card for listings
- `src/components/public/AmenityList.jsx` - Amenity display
- `src/components/public/PhotoGallery.jsx` - Image gallery
- `src/components/public/PublicNavbar.jsx` - Public navigation

#### Services
- `src/services/publicService.js` - Public API calls

---

## 🔌 API Endpoints

### Public Routes (No Authentication Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/public/units` | Search available units |
| GET | `/api/public/units/{id}` | Get unit details |
| POST | `/api/public/inquiries` | Submit rental inquiry |
| POST | `/api/public/units/{id}/reserve` | Reserve a unit |
| GET | `/api/public/reservations/{id}` | Check reservation status |

### Search Parameters

```
GET /api/public/units?
  location_city=Nairobi
  &location_area=Westlands
  &min_rent=20000
  &max_rent=60000
  &bedrooms=2
  &bathrooms=1
  &property_type=apartment
  &amenities=parking,wifi
  &sort=rent_asc
  &page=1
  &per_page=20
```

### Inquiry Request Body

```json
{
  "unit_id": "uuid",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+254722123456",
  "message": "I'm interested in viewing this unit",
  "preferred_move_in_date": "2025-02-01"
}
```

### Reservation Request Body

```json
{
  "tenant_name": "John Doe",
  "tenant_email": "john@example.com",
  "tenant_phone": "+254722123456",
  "move_in_date": "2025-02-15",
  "payment_method": "mpesa",
  "notes": "Optional notes"
}
```

---

## 🔒 Business Rules

### Unit Visibility
- Only units with `is_publicly_listed = true` appear in search
- Only units with `status = 'available'` can be reserved
- Only units from `approved` properties are shown
- Sensitive data (owner details, internal notes) is hidden

### Reservations
- Reservation holds unit for **7 days**
- Deposit amount = unit's `deposit_amount` field
- Unit status changes to `reserved` upon successful reservation
- Expired reservations auto-release unit (via scheduled job)
- One active reservation per unit at a time

### Inquiries
- No duplicate inquiries from same email for same unit within 24 hours
- Status flow: `pending` → `contacted` → `approved`/`rejected`

---

## 🧪 Testing

### Backend Tests
```bash
php artisan test --filter=PublicUnitTest
php artisan test --filter=RentalInquiryTest
php artisan test --filter=ReservationTest
```

### Test Scenarios
1. Search units with various filters
2. View unit details (public vs authenticated)
3. Submit inquiry with valid/invalid data
4. Create reservation for available unit
5. Attempt reservation for occupied unit (should fail)
6. Check reservation expiry logic

---

## 📝 Progress Log

### Day 1 - Backend Implementation
- [x] Create PublicUnitController
- [x] Create PublicRentalInquiryController
- [x] Create PublicReservationController
- [x] Create form request validators
- [x] Create API resources
- [x] Add public routes

### Day 2 - Frontend Setup
- [x] Create publicService.js
- [x] Create PublicNavbar component
- [x] Create SearchFilters component
- [x] Create PublicUnitCard component

### Day 3 - Frontend Pages
- [x] Create UnitSearch page
- [x] Create UnitDetails page
- [x] Create PhotoGallery component
- [x] Create AmenityList component

### Day 4 - Inquiry & Reservation
- [x] Create InquiryForm page
- [x] Create ReservationCheckout page
- [x] Update App.jsx routes

### Day 5 - Testing & Polish
- [ ] Write backend tests
- [ ] Test all flows end-to-end
- [ ] Fix bugs and polish UI

---

## 🚀 Next Steps (Week 9)

After completing the public marketplace:
- **Week 9**: Tenant Onboarding & Lease Management
  - Inquiry approval → Tenant creation
  - Lease creation with pro-rated rent
  - Reservation → Lease conversion

