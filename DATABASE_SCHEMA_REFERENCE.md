# Database Schema Reference - Electrical System

## as_electric Table Structure

### Core Fields
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **id** | INT | Auto-increment primary key | Numeric PK (MAX + 1) |
| **electric_id** | VARCHAR(128) | Unique electrical item identifier | Format: `ELC-{PREFIX}-{SEQ}` (e.g., ELC-LAM-001) |
| **nama** | VARCHAR | Common name of electrical device | e.g., "LAMPU", "RELAY", "KONTROL" |
| **type** | VARCHAR | Type/Model number | e.g., "EZ9F34106", "G2R-1-SND", "TK45-14SN" |
| **brand** | VARCHAR | Manufacturer/Brand | e.g., "OMRON", "SCHNEIDER", "SIEMENS", "ABB". Defaults to "Unknown" if not provided. |
| **type_id** | INT | Foreign key to electric_type category | References as_electric_type table |

### Specification Fields
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **voltage** | VARCHAR | Operating voltage (single value or range) | e.g., "220", "4-30", optional |
| **voltage_unit** | VARCHAR | Voltage unit | Values: "V", "VAC", "VDC" (default: "V") |
| **ampere** | DECIMAL | Current rating in amperes | e.g., "1.5", "10", optional |
| **daya** | DECIMAL | Power rating | e.g., "330", "1500", optional |
| **daya_unit** | VARCHAR | Power unit | Values: "W", "VA" (default: "W") |

### Location & Stock Fields
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **location** | VARCHAR | Storage location name | Alternative to location_id (legacy support) |
| **location_id** | INT | Foreign key to as_location master table | Preferred method; prefer over location field |
| **location_name** | VARCHAR | Location name (denormalized) | Alternative location storage |
| **stock** | INT | Current stock quantity | Optional; authoritative source is as_history.qty_sisa |

### Timestamp Fields
| Field | Type | Description |
|-------|------|-------------|
| **created_at** | DATETIME | Record creation timestamp |
| **updated_at** | DATETIME | Last modification timestamp |

---

## as_history Table Structure

### Primary Transaction Fields
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **id** | INT | Primary key | Manual generation (MAX + 1) |
| **electric_id** | VARCHAR(128) | Reference to as_electric | Foreign key |
| **type** | ENUM('Masuk','Keluar') | Transaction type | "Masuk" = Incoming, "Keluar" = Outgoing |
| **qty** | INT | Transaction quantity | Quantity in/out |
| **qty_sisa** | INT | Remaining quantity after transaction | **Authoritative stock amount** |
| **user_nik** | VARCHAR(20) | User/staff identifier | Who performed the transaction |
| **date** | DATETIME | Transaction timestamp | When the transaction occurred |
| **keterangan** | TEXT | Notes/description | Optional notes about transaction |

### Procurement Fields (Masuk/Incoming Only)
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **po_number** | VARCHAR | Purchase Order number | e.g., "PO-2026-..." |
| **distributor** | VARCHAR | Supplier/Distributor name | Vendor information |
| **tanggal_pesan** | DATE | Order/Request date | When PO was placed |
| **tanggal_terima** | DATE | Receipt date | When goods arrived |
| **harga_satuan** | DECIMAL | Unit price (Rp) | **PRICING FIELD** - Price per unit in Rupiah |
| **location_id** | INT | Storage location for received items | References as_location table |

### Batch Tracking Fields
| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| **from_batch_id** | INT | References original Masuk transaction ID | For Keluar: tracks which batch this quantity came from (FIFO) |

---

## Summary: Fields by Category

### Brand Information
- **Field:** `as_electric.brand`
- **Source:** as_electric table
- **Display:** Shown in electric list view, edit form
- **Default:** "Unknown" if not provided

### Specification/Type Information
- **Primary:** `as_electric.type` (e.g., "EZ9F34106", model numbers)
- **Secondary:** `as_electric.nama` (common name, e.g., "RELAY")
- **Category:** `as_electric.type_id` (references as_electric_type)
- **Specs:** voltage, voltage_unit, ampere, daya, daya_unit

### Pricing Information
- **Field:** `as_history.harga_satuan`
- **Unit:** Rupiah (Rp)
- **Entry Point:** During "Catat Barang Masuk" (Procurement form)
- **Visibility:** Stored with incoming (Masuk) transactions only
- **Display:** Shown in history/procurement reports

### Stock/Inventory Tracking
- **Authoritative Source:** `as_history.qty_sisa`
  - Sum of qty_sisa for Masuk - sum for Keluar = current available stock
- **Fallback Sources:** `as_electric.stock`, `as_storage.amount`
- **Batch Tracking:** `as_history.from_batch_id` enables FIFO tracking

---

## Key Relationships

```
as_electric
├── type_id → as_electric_type (category)
└── location_id → as_location (master locations)

as_history
├── electric_id → as_electric (which item)
└── from_batch_id → as_history (for Keluar records, references the Masuk batch)
```

---

## View Usage

### Electric List View (electric/index.php)
Displays: electric_id, nama, type (as badge), brand, voltage+ampere+daya specs, location, total_stock

### Add/Edit Forms (electric/add.php, electric/edit.php)
Fields collected:
- type_id, nama, type, brand (optional)
- voltage, voltage_unit, ampere, daya, daya_unit
- location selection

### Procurement/History (history/in.php)
Pricing field:
- harga_satuan (unit price in Rp)
- Also collects: po_number, distributor, tanggal_pesan, tanggal_terima, location_id
