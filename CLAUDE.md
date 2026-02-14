# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Point of Sale (POS) system built with Laravel 9 backend and Vue 3 frontend. It has two main interfaces:
- **POS Interface** (`/`): For waitstaff to manage tables, orders, and invoices with Epson thermal printer integration
- **Backoffice** (`/backoffice/`): Admin dashboard for inventory, reports, warehouse management, and system configuration

## Commands

### Development
```bash
yarn dev          # Start Vite dev server for frontend
php artisan serve # Start Laravel dev server
```

### Build
```bash
yarn build        # Build frontend assets for production
```

### Testing
```bash
./vendor/bin/phpunit                          # Run all tests
./vendor/bin/phpunit tests/Unit               # Run unit tests only
./vendor/bin/phpunit tests/Feature            # Run feature tests only
./vendor/bin/phpunit --filter=TestClassName   # Run specific test
```

### Database
```bash
php artisan migrate                    # Run migrations
php artisan db:seed                    # Seed database
php artisan database:backup            # Manual backup to S3 (scheduled daily at 4am)
```

### Artisan Commands
```bash
php artisan sales:clear                        # Clear storno/refunded sales
php artisan command:process-unprocessed-imports # Retry failed warehouse imports
```

## Architecture

### Backend (Laravel 9)

**Services** (`services/`): Custom service classes autoloaded via PSR-4
- `WorkingDay`: Business "working day" calculations (4am to 4am)
- `SalesService`: Order processing, warehouse stock updates
- `ReportsService`: Revenue statistics and report generation
- `Pusher`: Real-time event broadcasting wrapper

**Key Models** (`app/Models/`):
- `Table` → `Order` → `Invoice`: Core POS transaction flow
- `Inventory` ↔ `Category`: Menu items and their categories
- `Warehouse` → `WarehouseInventory` → `WarehouseStatus`: Stock management
- `Sales` / `SalesImportDetail`: Sales records and batch imports

**Model Constants** (important status/type values):
```php
// Invoice statuses
Invoice::STATUS_REFUNDED = 0
Invoice::STATUS_PAYED = 1
Invoice::STATUS_ON_THE_HOUSE = 2

// Warehouse status types
WarehouseStatus::TYPE_IN = 0    // Stock received
WarehouseStatus::TYPE_OUT = 1   // Stock consumed (sales)
WarehouseStatus::TYPE_RESET = 2 // Inventory count reset

// Inventory sold_by types
Inventory::SOLD_BY_PIECE = 0
Inventory::SOLD_BY_HALF_PORTION = 1
Inventory::SOLD_BY_GRAMS = 2
```

**Filtering Traits** (`app/Models/Traits/`):
- `Revenue`: Invoice filtering with date, waiter, status scopes
- `SalesRevenue`: Sales filtering with date, search, category scopes
- `InventoryFilters`: Inventory search and category filtering

### Frontend (Vue 3)

**Two separate Vue apps** with independent entry points:

| App | Entry | Router | Store | Purpose |
|-----|-------|--------|-------|---------|
| POS | `app.js` | `router.js` | `store.js` | Waitstaff interface |
| Backoffice | `backoffice.js` | `backofficeRouter.js` | `backofficeStore.js` | Admin interface |

**Vuex Store Modules** (`resources/js/store/modules/`):
- `general`: Tables, tasks, areas, orders
- `epos`: Epson printer device connection
- `printing`: Print job formatting and management
- `backoffice`: Admin data (reports, stats, inventory)

**Global Utilities** (available in all components):
```javascript
window.axios     // HTTP client
window._         // Lodash
window.dayjs     // Date manipulation
this.$filters.formatPrice(value)  // Price formatting
this.$filters.formatDate(value)   // Date formatting
```

**UI Stack**: Tailwind CSS, Headless UI, Heroicons

### Real-time Updates (Pusher)

Channels and events:
- `broadcasting` → `tables-update`: Table state changes
- `broadcasting` → `notifications`: Task notifications

### API Structure

```
/api/                    # Public API endpoints
/api/backoffice/         # Admin-only endpoints
/public/sales/import     # CORS-enabled external import
```

**Key Endpoints**:
- `GET /api/tables` - List tables
- `POST /api/orders` - Create order
- `POST /api/invoices` - Create invoice (cash out)
- `GET /api/backoffice/warehouse-status` - Stock levels
- `GET /api/backoffice/reports/{type}` - Reports

## Key Patterns

### Caching Pattern
```php
// Used in InventoryController, CategoryController
return Cache::remember('inventory-all', 60, function() {
    return new InventoryCollection(Inventory::all());
});
// Always invalidate with Cache::forget() on mutations
```

### Filter Scope Pattern
```php
// Models with Revenue/SalesRevenue/InventoryFilters traits
$invoices = Invoice::filter($request)->paginate(15);
```

### Working Day Usage
```php
use Services\WorkingDay;

// Get today's working day range
$range = WorkingDay::getWorkingDay();
// Returns: ['2024-01-15 04:00:00', '2024-01-16 03:59:59']

// Get correct date for storing (handles early AM)
$date = WorkingDay::setCorrectDateForWorkingDay();
```

### Pusher Broadcasting
```php
use Services\Pusher;

try {
    Pusher::trigger('tables-update', []);
} catch (\Exception $e) {
    \Log::error($e->getMessage());
}
```

## Working Day Concept

The system uses a "working day" running **4:00 AM to 3:59 AM next day**. A sale at 1:00 AM on January 16th counts as January 15th's revenue. Always use `WorkingDay` service for date filtering in reports and queries.

## Warehouse System

Stock consumption flow:
1. Invoice created → `SalesService::parseAndSaveOrder()` called
2. Each order item → lookup `WarehouseInventory` for consumption rate (`norm`)
3. Create `WarehouseStatus` TYPE_OUT entries: `quantity = qty * norm`
4. Failed operations logged to `ExceptionLog` for retry

## Skills Available

This project includes custom Claude Code skills in `.claude/skills/`:

**Task Skills** (invoke with `/skill-name`):
- `/api-controller` - Create Laravel API controllers
- `/vue-component` - Create Vue 3 components
- `/model` - Create Eloquent models
- `/migration` - Create database migrations
- `/feature-test` - Create PHPUnit feature tests
- `/commit` - Git commit helper

**Reference Skills** (auto-loaded when relevant):
- `warehouse-guide` - Warehouse system documentation
- `working-day` - Business day concept reference
- `printing-guide` - Epson printer integration

## Naming Conventions / Terminology

The UI labels differ from internal code names. This mapping is important for understanding what users see vs what the code calls things:

| Code / Internal Name | UI Label | Description |
|---|---|---|
| `Order`, `activeTableOrders`, `OverviewActiveOrders` | **Tablet porudžbine** | POS tablet orders (from waitstaff interface) |
| `ThirdPartyOrder`, `thirdPartyOrders`, `OverviewThirdPartyOrders` | **Aktivni stolovi** | Third-party / ebar orders |
| `Invoice`, route `invoices` | **Tablet računi** | POS tablet invoices |
| `ThirdPartyInvoice`, route `third-party-invoices` | **Računi** | Third-party / ebar invoices |
