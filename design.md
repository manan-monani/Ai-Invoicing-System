# AI Invoicing System - Frontend Design Documentation

This document outlines the design token system, typography, layout architecture, and screen layouts for the **AI Invoicing System** project. Use this documentation to create precise Figma mockups.

---

## 🎨 Color Palette & Design Tokens

The application supports both **Light Mode** and **Dark Mode**, and features a customizable dynamic theme where branding colors are driven by database configurations mapping to CSS variables.

### 1. Brand & Accent Colors
* **Primary / Brand Color (`--brand-primary`):** `#4f7fa6` (Soft Sky Blue)
  * Used for main action buttons, active navigation markers, focus rings, and primary highlights.
* **Primary Light (`--brand-primary-light` / `--admin-active-item-bg`):** `#e9f1f7` (Misty Blue Tint)
  * Used for background highlights of active lists/menu items in Light Mode.
* **Primary Dark Text (`--brand-primary-dark-text`):** `#b9d3e6`
  * Used for active navigation text in Dark Mode.
* **Secondary Color (`--brand-secondary`):** `#6a747d` (Muted Steel / Slate)
* **Success Color (`--brand-success`):** `#4a9c88` (Muted Emerald)
* **Danger Color (`--brand-danger`):** `#c96b6b` (Soft Crimson)
* **Warning Color (`--brand-warning`):** `#c9a35a` (Warm Ochre)
* **Info Color (`--brand-info`):** `#4f85a6` (Soft Blue)

### 2. Admin Portal Theme Customization
| CSS Variable | Light Mode Value | Dark Mode Value | Usage Description |
| :--- | :--- | :--- | :--- |
| `--admin-sidebar-bg` | `#ffffff` | `#0f172a` (Slate 900) | Secondary submenu background |
| `--admin-sidebar-border` | `#e2e8f0` | `#1e293b` (Slate 800) | Sidebar divider borders |
| `--admin-sidebar-rail-bg` | `#2f4f66` (Deep Slate) | `#213746` (Dark Slate) | Left-most navigation icon rail BG |
| `--admin-sidebar-icon` | `#ffffff` | `#ffffff` | Icon fill on navigation rail |
| `--admin-header-bg` | `#ffffffcc` (80% opacity) | `#0f172acc` (80% opacity) | Main dashboard header bar |
| `--admin-content-bg` | `#f8fafc` (Slate 50) | `#020617` (Slate 950) | Core page background |
| `--admin-card-bg` | `#ffffff` | `#0f172a` (Slate 900) | Data table & dashboard cards |
| `--admin-card-border` | `#f1f5f9` (Slate 100) | `#1e293b` (Slate 800) | Borders around data cards/tables |
| `--admin-active-item-bg` | `#e9f1f7` | `#4f7fa620` (~12% opacity) | Active sidebar link highlight |
| `--admin-active-item-text` | `#4f7fa6` | `#b9d3e6` | Active sidebar link text |

---

## 🔠 Typography & Fonts

The application leverages dynamic Google fonts loaded via `app.blade.php` and applies specific type styles depending on reading directions:

* **Primary LTR (Left-to-Right / English) Font:** `Instrument Sans` or `Plus Jakarta Sans`, fallback `sans-serif`
  * Applied to headers, sidebars, dashboard metrics, and default page text.
* **Primary RTL (Right-to-Left / Arabic) Font:** `IBM Plex Sans Arabic`, fallback `sans-serif`
  * Automatically loaded and applied if the document direction is set to `rtl` (`dir="rtl"`).
* **Secondary Font:** `Roboto`, fallback `sans-serif`
  * Used for tabular outputs, codes, secondary details, or inputs.

---

## 🗂️ Layout & Navigation Architecture

### 1. Admin Dashboard Layout (`AdminLayout.vue`)
* **Dual-Tier Navigation Sidebar:**
  * **Tier 1 (Left Rail):** `72px` width. Deep Slate Blue background (`#2f4f66`). Contains the branding logo at the top, a vertical stack of primary category buttons (Catalog, Account, System Settings), and a collapse sidebar toggle button at the bottom.
  * **Tier 2 (Sub-menu):** `228px` width. Sliding panel. Displays page sub-modules corresponding to the active Tier 1 category. Can be collapsed to `0px` via a toggle, which collapses the main sidebar to just the `72px` rail.
* **Header Bar (`AdminHeader.vue`):** Sticky `64px` height with backdrop blur. Houses the mobile navigation toggle, breadcrumbs, search, notification icon, dark mode switch, and user profile menu dropdown.
* **Responsive Breakpoints:** Mobile views hide the sub-menu, rendering Tier 1 as a slide-out drawer triggered by a hamburger menu in the header.

### 2. Customer Dashboard Layout (`CustomerLayout.vue`)
* Similar clean structured sidebar focused on Customer metrics, invoices, and profile updates.

### 3. Public Layout (`PublicLayout.vue`)
* Standard top navigation bar (Logo, Home, Contact Us, Sign In/Register buttons) with a responsive center grid.

---

## 🖥️ Screen Summaries (Application Map)

Here is a summary of the UI screens built with Vue 3 / Inertia.js inside `resources/js/Pages`:

### 1. Administrative Portal (`Admin/`)
* **Auth Screens:**
  * **Login (`Auth/Login.vue`):** Center-aligned card login utilizing dynamic primary theme colors.
  * **Register (`Auth/Register.vue`):** Setup super admin configuration profile.
* **Dashboard (`Pages/Dashboard.vue`):**
  * Overview panel displaying total invoices count, revenue metrics, top customers, unpaid invoice widgets, and system activity summaries.
* **Invoices Management:**
  * **Invoice List (`Invoices/Index.vue`):** Data table with filters (status, date, customer name), multi-action buttons (view, edit, delete, download, email), and pagination.
  * **Create Invoice (`Invoices/Create.vue`):** Multi-step item selection, tax allocation, discounts, customer selection, and invoice template configurations.
  * **Edit Invoice (`Invoices/Edit.vue`):** Update existing draft invoice values.
  * **Invoice Preview (`Invoices/Show.vue`):** Professional invoice template layout with actions to print/download PDF or send payment link.
* **Catalog Management (`Catalog/`):**
  * **Items List (`Items/Index.vue`):** List of products/services with pricing, SKU, category tags, and edit dialogs.
  * **Categories (`Categories/Index.vue`):** Simple grid list to structure catalog items.
  * **Tax Catalog (`Taxes/Index.vue`):** Manage standard tax items mapped to catalogs.
* **Accounts & User Directory (`Members/` & `Pages/`):**
  * **Employees (`Members/Index.vue`, `Create.vue`, `Edit.vue`):** Directory list, creation, and authorization updates for staff.
  * **Customers (`Pages/Users.vue`):** Profile list of customers with contact metadata, invoices history, and outstanding balances.
  * **Access Roles (`Roles/Index.vue`, `Create.vue`, `Edit.vue`):** Matrix of roles and checkable permissions mapped to system operations.
* **System Settings (`Business/` & `System/`):**
  * **Visual Branding (`Business/Branding.vue`):** Controls dynamic colors (Brand Primary, Secondary, etc.), fonts (LTR & RTL), logo uploads, favicons, and metadata.
  * **Business Logic (`Business/BusinessLogic.vue`):** Manage system config parameters (Default Currency, Timezones, Address, Formatting).
  * **Global Tax Settings (`System/TaxSettings.vue`):** Configuration of tax options, thresholds, and calculations.
  * **Payment Methods (`System/PaymentMethods.vue`):** Enable payment integrations (Stripe, Paypal, Offline Bank transfers, cash).
* **System Logging:**
  * **Activity Logs (`ActivityLog/Index.vue`, `Show.vue`):** System audit trails tracking administrative changes.

### 2. Customer Portal (`Customer/`)
* **Dashboard (`Pages/Dashboard.vue`):** Simple dashboard showing total paid/outstanding balances and quick pay portals.
* **Invoices List (`Invoices/Index.vue`):** Customer-centric list of invoices issued to their profile.
* **Invoice Details (`Invoices/Show.vue`):** Interactive invoice view detailing items, taxes, payments, and standard "Pay Now" gateways.
* **Profile Setup (`Pages/Profile.vue`):** Updates to user info, address, contact, and password settings.

### 3. Public Portal (`Guest/` & `Welcome/`)
* **Landing Page (`Guest/Pages/Home.vue`):** Rich marketing page featuring a primary hero section, mock dashboard previews, animated blobs, product video showcase, feature lists, pricing table, and interactive FAQ accordion.
* **Contact Us (`Guest/Contact/Show.vue`):** Contact submission form with interactive location elements.
* **Guest Auth (`Guest/Pages/Login.vue` & `Register.vue`):** Public-facing customer login and registration portals.
