-- Invoice Printer / Billing System
-- MySQL 8.0+, InnoDB, utf8mb4

CREATE DATABASE IF NOT EXISTS invoice_printer
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE invoice_printer;

SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS v_customer_balances;
DROP VIEW IF EXISTS v_invoice_balances;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    permissions JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_roles_name (name),
    KEY idx_roles_deleted_at (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_active (role_id, is_active),
    KEY idx_users_deleted_at (deleted_at),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE company_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    legal_name VARCHAR(200) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(190) NULL,
    website VARCHAR(255) NULL,
    tin_number VARCHAR(80) NULL,
    bin_number VARCHAR(80) NULL,
    logo_path VARCHAR(500) NULL,
    invoice_prefix VARCHAR(30) NOT NULL DEFAULT 'INV',
    invoice_next_number BIGINT UNSIGNED NOT NULL DEFAULT 1,
    currency_code CHAR(3) NOT NULL DEFAULT 'BDT',
    currency_symbol VARCHAR(10) NOT NULL DEFAULT '৳',
    bank_details JSON NULL,
    invoice_footer TEXT NULL,
    authorized_person VARCHAR(150) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_company_settings_deleted_at (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    company_name VARCHAR(200) NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    tin_number VARCHAR(80) NULL,
    bin_number VARCHAR(80) NULL,
    nid_number VARCHAR(80) NULL,
    opening_balance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_customers_code (customer_code),
    KEY idx_customers_name (name),
    KEY idx_customers_phone (phone),
    KEY idx_customers_email (email),
    KEY idx_customers_deleted_name (deleted_at, name),
    CONSTRAINT fk_customers_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_code VARCHAR(50) NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    default_rate DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    default_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_services_code (service_code),
    KEY idx_services_active_name (is_active, name),
    KEY idx_services_deleted_at (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_setting_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    referral_source VARCHAR(200) NULL,
    created_by BIGINT UNSIGNED NULL,
    invoice_number VARCHAR(100) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    currency_code CHAR(3) NOT NULL DEFAULT 'BDT',
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_type ENUM('fixed','percentage') NOT NULL DEFAULT 'fixed',
    discount_value DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(7,4) NOT NULL DEFAULT 0.0000,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_profit DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft','issued','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
    payment_terms VARCHAR(255) NULL,
    notes TEXT NULL,
    printed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancelled_by BIGINT UNSIGNED NULL,
    cancellation_reason VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_invoices_number (invoice_number),
    KEY idx_invoices_customer_date (customer_id, invoice_date),
    KEY idx_invoices_status_date (status, invoice_date),
    KEY idx_invoices_due_date (due_date, status),
    KEY idx_invoices_created_by (created_by),
    KEY idx_invoices_deleted_at (deleted_at),
    CONSTRAINT fk_invoices_company FOREIGN KEY (company_setting_id) REFERENCES company_settings(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(12,3) NOT NULL DEFAULT 1.000,
    unit VARCHAR(30) NOT NULL DEFAULT 'item',
    rate DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    unit_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    profit DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_invoice_items_invoice_sort (invoice_id, sort_order),
    KEY idx_invoice_items_service (service_id),
    KEY idx_invoice_items_deleted_at (deleted_at),
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    received_by BIGINT UNSIGNED NULL,
    payment_date DATE NOT NULL,
    receipt_number VARCHAR(100) NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_method ENUM('cash','bank','bkash','nagad','rocket','cheque','card','other') NOT NULL DEFAULT 'cash',
    transaction_number VARCHAR(150) NULL,
    account_name VARCHAR(150) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_payments_receipt (receipt_number),
    KEY idx_payments_invoice_date (invoice_id, payment_date),
    KEY idx_payments_customer_date (customer_id, payment_date),
    KEY idx_payments_method_date (payment_method, payment_date),
    KEY idx_payments_deleted_at (deleted_at),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_payments_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_payments_received_by FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    description VARCHAR(500) NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_method ENUM('cash','bank','bkash','nagad','rocket','card','other') NOT NULL DEFAULT 'cash',
    invoice_id BIGINT UNSIGNED NULL,
    referral_source VARCHAR(200) NULL,
    created_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_expenses_date_category (expense_date, category),
    KEY idx_expenses_invoice (invoice_id),
    KEY idx_expenses_deleted_at (deleted_at),
    CONSTRAINT fk_expenses_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    event VARCHAR(50) NOT NULL,
    auditable_type VARCHAR(100) NOT NULL,
    auditable_id BIGINT UNSIGNED NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_entity (auditable_type, auditable_id, created_at),
    KEY idx_audit_user_date (user_id, created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Live invoice balance. Payments marked deleted are excluded.
CREATE OR REPLACE VIEW v_invoice_balances AS
SELECT
    i.id,
    i.invoice_number,
    i.customer_id,
    i.invoice_date,
    i.due_date,
    i.total_amount,
    COALESCE(SUM(CASE WHEN p.deleted_at IS NULL THEN p.amount ELSE 0 END), 0.00) AS received_amount,
    i.total_amount - COALESCE(SUM(CASE WHEN p.deleted_at IS NULL THEN p.amount ELSE 0 END), 0.00) AS balance_amount,
    i.total_cost,
    i.total_profit,
    i.status
FROM invoices i
LEFT JOIN payments p ON p.invoice_id = i.id
WHERE i.deleted_at IS NULL
GROUP BY i.id;

CREATE OR REPLACE VIEW v_customer_balances AS
SELECT
    c.id AS customer_id,
    c.customer_code,
    c.name,
    c.opening_balance,
    COALESCE(SUM(v.total_amount), 0.00) AS total_invoiced,
    COALESCE(SUM(v.received_amount), 0.00) AS total_received,
    c.opening_balance + COALESCE(SUM(v.balance_amount), 0.00) AS outstanding_balance
FROM customers c
LEFT JOIN v_invoice_balances v ON v.customer_id = c.id
WHERE c.deleted_at IS NULL
GROUP BY c.id;

-- Seed roles. Password must be replaced by a Laravel Hash::make() value.
INSERT INTO roles (name, display_name, permissions) VALUES
('admin', 'Administrator', JSON_OBJECT('all', true)),
('manager', 'Manager', JSON_OBJECT('invoices', true, 'payments', true, 'reports', true)),
('staff', 'Staff', JSON_OBJECT('invoices', true, 'payments', true));

-- Laravel migrations should be used in the application after this base schema.
