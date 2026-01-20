# 🏠 Rental SaaS App — UI/UX Design Specification

## 📌 Overview

This document outlines the **complete UI/UX design system** and **workflow** for the Rental SaaS application. It includes visual styles, design tokens, user journeys, components, page layouts, and responsive behaviors. The goal is to guide designers and frontend developers in implementing a scalable and modern design system aligned with the app's backend and business logic.

---

## 👥 User Roles

- **Admin**: Full control over users, listings, analytics, and revenue.
- **Host**: Can create, edit, and manage rental listings.
- **Renter**: Can browse, search, book, and review rentals.

---

## 🎨 Design Tokens

### Colors

| Role         | Token             | Hex        |
|--------------|-------------------|------------|
| Primary      | `--color-primary` | `#2563EB`  |
| Secondary    | `--color-secondary` | `#64748B` |
| Background   | `--color-bg`      | `#F8FAFC`  |
| Surface      | `--color-surface` | `#FFFFFF`  |
| Text Primary | `--color-text`    | `#0F172A`  |
| Text Muted   | `--color-muted`   | `#475569`  |
| Success      | `--color-success` | `#22C55E`  |
| Warning      | `--color-warning` | `#F59E0B`  |
| Error        | `--color-error`   | `#EF4444`  |

> Supports light & dark mode via `prefers-color-scheme`

### Typography

- **Font family**: `Inter`, `Poppins`, `sans-serif`
- **Font sizes**: 12px, 14px, 16px, 20px, 24px, 32px
- **Font weights**: 400 (regular), 600 (semibold), 700 (bold)
- **Line height**: 1.5

### Spacing Scale

- `4px`, `8px`, `16px`, `24px`, `32px`, `48px`

---

## 📱 Responsive Layout

- **Mobile First**: Fully responsive
- **Breakpoints**: `sm (640px)`, `md (768px)`, `lg (1024px)`, `xl (1280px)`
- **Grid**: 12-column fluid grid

---

## 🧱 UI Components

### Buttons

- Primary: Solid blue
- Secondary: Outline
- Disabled: Grayed out
- States: Hover, Focus, Loading

### Form Elements

- Input (with label and hint)
- Select dropdown
- Date picker
- Textarea
- Toggle switch
- Error / success states

### Cards

- Listing card (image, name, price, location)
- Booking card (date, renter info)
- Payment summary

### Modals

- Booking confirmation
- Delete confirmation
- Admin actions

### Notifications

- Inline alerts (success, warning, error)
- Toasts (booking success, payment error)

---

## 📄 Pages & Layouts

### 🏠 Home Page

- Hero with search bar (location, dates, guests)
- Featured listings (carousel or grid)
- Categories (apartments, villas, etc.)
- Call to action: "Become a host"

### 🔍 Search Results

- Filters: Price, type, rating, date
- Listings grid
- Pagination or infinite scroll

### 🏘️ Listing Details Page

- Image gallery / carousel
- Listing info (title, host, location, description)
- Calendar availability widget
- Price breakdown
- Book Now button

### 🧾 Booking Flow

1. Select dates
2. Enter user details (or login)
3. Payment method
4. Booking confirmation page

### 📋 User Dashboard

- Tabs: Listings | Bookings | Reviews | Payouts
- Summary cards: Earnings, Active bookings
- Manage listings (edit, delete, view calendar)

### 🛠 Admin Panel

- Users list (block, delete)
- Listings overview
- Reports & analytics dashboard

---

## 🔄 User Workflows

### 🧑 Renter Journey

1. Lands on homepage
2. Uses search bar to find listings
3. Views details, selects date
4. Clicks “Book Now”
5. Logs in / registers
6. Enters payment info
7. Booking confirmed, receives email

### 🏡 Host Journey

1. Logs in
2. Creates a new listing
3. Uploads images
4. Sets price and availability
5. Publishes listing
6. Views bookings from dashboard

### 👨‍💼 Admin Journey

1. Logs into admin dashboard
2. Monitors listings and bookings
3. Reviews flagged content
4. Generates reports
5. Manages users and payout logs

---

## 🧩 Advanced Features (From `missing_pieces.txt`)

- Real-time notifications (e.g., booking approved)
- Booking calendar with blocked dates
- Review and rating system (with moderation)
- Upload reliability (multiple image support)
- Admin analytics (top cities, hosts, revenue)

---

## 📦 Backend Alignment

- Fully RESTful API (`/api/listings`, `/api/bookings`, etc.)
- Role-based access control
- Schema aligned with normalized DB
- Soft delete not implemented (suggested)
- Lacks logging, media compression, versioning (can be added)

---

## 🧪 QA & Accessibility (Recommended)

- WCAG 2.1 AA contrast for text & buttons
- Keyboard nav (Tab, Shift+Tab, Enter)
- Alt tags for images
- Responsive tests on mobile, tablet, desktop

---

## 📚 Tech Stack Suggestions (Frontend)

- **React + Next.js**
- **Tailwind CSS** or Chakra UI
- **Figma** for design system
- **Stripe** or **Razorpay** for payments
- **Heroicons** / Lucide for icons

---

## 📌 Next Steps

1. Implement this spec in **Figma** or **Storybook**
2. Use Tailwind's `theme.extend` to map these tokens
3. Begin frontend development with this spec as base
4. Add feature-specific design once MVP is stable

---

