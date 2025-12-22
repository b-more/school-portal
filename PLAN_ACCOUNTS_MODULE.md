# Accounts Module Implementation Plan

## Overview
A comprehensive accounts module for St. Francis of Assisi Private School with simple daily-use interface backed by proper double-entry bookkeeping, full integration with existing fee and payroll systems, and basic financial reporting.

---

## Module Components

### 1. Database Schema (11 new tables)

#### Core Accounting Tables:
```
account_categories
├── id, name, type (asset/liability/equity/revenue/expense)
├── code, description, is_system, sort_order
└── created_at, updated_at

chart_of_accounts
├── id, account_category_id, parent_id (self-referencing)
├── code, name, description
├── account_type (debit/credit), is_active, is_system
├── opening_balance, current_balance
├── created_at, updated_at
└── Hierarchical structure for sub-accounts

journal_entries
├── id, entry_number (auto-generated), entry_date
├── reference_type, reference_id (polymorphic - links to fees, payroll, etc.)
├── description, notes
├── total_debit, total_credit
├── status (draft/posted/void), posted_by, posted_at
├── academic_year_id, created_by
└── created_at, updated_at

journal_entry_lines
├── id, journal_entry_id, account_id
├── debit_amount, credit_amount
├── description, sort_order
└── created_at, updated_at

bank_accounts
├── id, account_id (links to chart_of_accounts)
├── bank_name, account_name, account_number
├── branch, swift_code, currency (default ZMW)
├── opening_balance, current_balance
├── is_active, is_default
└── created_at, updated_at

bank_transactions
├── id, bank_account_id, transaction_date
├── type (deposit/withdrawal/transfer/fee/charge)
├── amount, reference, description
├── reconciled, reconciled_at, reconciled_by
├── journal_entry_id
└── created_at, updated_at
```

#### Expense Management Tables:
```
expense_categories
├── id, name, code, description
├── parent_id (hierarchical), account_id
├── budget_amount, is_active
└── created_at, updated_at

vendors
├── id, name, contact_person, email, phone
├── address, tax_pin, payment_terms
├── account_id (payable account), is_active
└── created_at, updated_at

expenses
├── id, expense_number, expense_date
├── vendor_id, expense_category_id
├── description, amount, tax_amount, total_amount
├── payment_status (unpaid/partial/paid)
├── payment_method, payment_reference
├── bank_account_id, approved_by, approved_at
├── journal_entry_id, academic_year_id
├── attachments (JSON)
└── created_at, updated_at

payment_vouchers
├── id, voucher_number, voucher_date
├── payee_type (vendor/employee/other), payee_id
├── payee_name, description
├── amount, payment_method
├── bank_account_id, cheque_number
├── status (pending/approved/paid/cancelled)
├── prepared_by, approved_by, paid_by
├── approved_at, paid_at
├── journal_entry_id
└── created_at, updated_at

income_records
├── id, income_number, income_date
├── income_category_id, description
├── amount, payment_method, reference
├── bank_account_id, payer_name, payer_contact
├── source_type, source_id (polymorphic - fees, donations, etc.)
├── journal_entry_id, academic_year_id
└── created_at, updated_at
```

---

### 2. Models (11 new models)

```
app/Models/Accounting/
├── AccountCategory.php
├── ChartOfAccount.php
├── JournalEntry.php
├── JournalEntryLine.php
├── BankAccount.php
├── BankTransaction.php
├── ExpenseCategory.php
├── Vendor.php
├── Expense.php
├── PaymentVoucher.php
└── IncomeRecord.php
```

**Key Model Features:**
- Auto-generation of entry/voucher numbers
- Balance calculations and updates
- Polymorphic relationships for integration
- Scopes for filtering by date, status, category
- Events for automatic journal posting

---

### 3. Services (4 new services)

```
app/Services/Accounting/
├── JournalEntryService.php    - Create, post, void journal entries
├── AccountingIntegrationService.php - Auto-post fees & payroll
├── BankReconciliationService.php - Bank statement matching
└── FinancialReportService.php - Generate financial reports
```

**JournalEntryService:**
- createEntry(data, lines[])
- postEntry(entryId)
- voidEntry(entryId, reason)
- getAccountBalance(accountId, asOfDate)

**AccountingIntegrationService:**
- postFeePayment(PaymentTransaction) → JournalEntry
- postPayroll(Payroll) → JournalEntry
- postExpense(Expense) → JournalEntry
- reverseEntry(originalEntryId)

---

### 4. Filament Resources (8 new resources)

```
app/Filament/Resources/Accounting/
├── ChartOfAccountResource.php     - Manage accounts hierarchy
├── JournalEntryResource.php       - View/create journal entries
├── BankAccountResource.php        - Manage bank accounts
├── BankTransactionResource.php    - Track bank transactions
├── ExpenseCategoryResource.php    - Expense categories
├── VendorResource.php             - Vendor management
├── ExpenseResource.php            - Record expenses
└── PaymentVoucherResource.php     - Payment vouchers
```

---

### 5. Filament Pages (3 new pages)

```
app/Filament/Pages/Accounting/
├── AccountsDashboard.php          - Financial overview
├── FinancialReports.php           - Income/Expense reports
└── BankReconciliation.php         - Reconcile bank statements
```

**AccountsDashboard Features:**
- Total Income (current month/year)
- Total Expenses (current month/year)
- Net Income/Loss
- Bank Balances summary
- Recent Transactions
- Quick Actions (Record Expense, Payment Voucher)

**FinancialReports Features:**
- Income Summary by category
- Expense Summary by category
- Cash Flow Statement
- Bank Account Balances
- Date range filtering
- PDF export

---

### 6. Integration Points

**Fee Payment Integration:**
```php
// When PaymentTransaction is created:
// Debit: Bank Account (Asset)
// Credit: School Fees Income (Revenue)
```

**Payroll Integration:**
```php
// When Payroll is marked as paid:
// Debit: Salary Expense (Expense)
// Debit: NAPSA Expense (Expense)
// Credit: Bank Account (Asset)
// Credit: NAPSA Payable (Liability)
// Credit: PAYE Payable (Liability)
```

**Expense Integration:**
```php
// When Expense is recorded:
// Debit: Expense Category Account (Expense)
// Credit: Bank/Cash Account (Asset) or Payable (Liability)
```

---

### 7. Default Chart of Accounts

```
ASSETS (1000-1999)
├── 1000 - Cash and Bank
│   ├── 1001 - Petty Cash
│   ├── 1010 - Main Bank Account
│   └── 1011 - School Fees Collection Account
├── 1100 - Accounts Receivable
│   ├── 1101 - Student Fees Receivable
│   └── 1102 - Other Receivables
└── 1200 - Prepaid Expenses

LIABILITIES (2000-2999)
├── 2000 - Accounts Payable
│   ├── 2001 - Vendor Payables
│   └── 2002 - Accrued Expenses
├── 2100 - Statutory Payables
│   ├── 2101 - PAYE Payable
│   ├── 2102 - NAPSA Payable
│   └── 2103 - NHIMA Payable
└── 2200 - Other Payables

EQUITY (3000-3999)
├── 3000 - School Capital
├── 3100 - Retained Earnings
└── 3200 - Current Year Surplus/Deficit

REVENUE (4000-4999)
├── 4000 - Tuition Fees
│   ├── 4001 - Pre-School Fees
│   ├── 4002 - Primary School Fees
│   └── 4003 - Secondary School Fees
├── 4100 - Other Income
│   ├── 4101 - Bus Fees
│   ├── 4102 - Registration Fees
│   ├── 4103 - Examination Fees
│   └── 4104 - Library Fees
└── 4200 - Miscellaneous Income

EXPENSES (5000-5999)
├── 5000 - Staff Costs
│   ├── 5001 - Teaching Staff Salaries
│   ├── 5002 - Non-Teaching Staff Salaries
│   ├── 5003 - NAPSA Contribution
│   ├── 5004 - NHIMA Contribution
│   └── 5005 - Staff Training
├── 5100 - Utilities
│   ├── 5101 - Electricity
│   ├── 5102 - Water
│   ├── 5103 - Internet
│   └── 5104 - Telephone
├── 5200 - Maintenance
│   ├── 5201 - Building Maintenance
│   ├── 5202 - Equipment Maintenance
│   └── 5203 - Vehicle Maintenance
├── 5300 - Academic Expenses
│   ├── 5301 - Books and Stationery
│   ├── 5302 - Laboratory Supplies
│   ├── 5303 - Sports Equipment
│   └── 5304 - Examination Costs
├── 5400 - Administrative
│   ├── 5401 - Office Supplies
│   ├── 5402 - Printing and Photocopying
│   ├── 5403 - Bank Charges
│   └── 5404 - Professional Fees
└── 5500 - Other Expenses
    ├── 5501 - Security Services
    ├── 5502 - Cleaning Services
    ├── 5503 - Transport Costs
    └── 5504 - Miscellaneous
```

---

### 8. Expense Categories (Pre-seeded)

```
School Operations
├── Utilities (Electricity, Water, Internet, Phone)
├── Maintenance (Building, Equipment, Grounds)
├── Security Services
├── Cleaning Services
└── Insurance

Staff Related
├── Salaries & Wages
├── Statutory Contributions (NAPSA, NHIMA, PAYE)
├── Staff Welfare
├── Training & Development
└── Uniforms

Academic
├── Teaching Materials
├── Laboratory Supplies
├── Library Resources
├── Sports & Recreation
├── Examinations
└── Field Trips

Administrative
├── Office Supplies
├── Communication
├── Professional Services
├── Bank Charges
└── Licenses & Permits

Transport
├── Fuel
├── Vehicle Maintenance
├── Driver Allowances
└── Route Expenses
```

---

### 9. Navigation Structure

```
Accounts & Finance (Navigation Group)
├── Dashboard           → AccountsDashboard
├── Chart of Accounts   → ChartOfAccountResource
├── Journal Entries     → JournalEntryResource
├── Bank Accounts       → BankAccountResource
├── Bank Transactions   → BankTransactionResource
├── Vendors             → VendorResource
├── Expenses            → ExpenseResource
├── Payment Vouchers    → PaymentVoucherResource
├── Financial Reports   → FinancialReports Page
└── Bank Reconciliation → BankReconciliation Page
```

---

### 10. Files to Create

**Migrations (11 files):**
1. create_account_categories_table.php
2. create_chart_of_accounts_table.php
3. create_journal_entries_table.php
4. create_journal_entry_lines_table.php
5. create_bank_accounts_table.php
6. create_bank_transactions_table.php
7. create_expense_categories_table.php
8. create_vendors_table.php
9. create_expenses_table.php
10. create_payment_vouchers_table.php
11. create_income_records_table.php

**Models (11 files):**
- AccountCategory, ChartOfAccount, JournalEntry, JournalEntryLine
- BankAccount, BankTransaction, ExpenseCategory, Vendor
- Expense, PaymentVoucher, IncomeRecord

**Services (4 files):**
- JournalEntryService, AccountingIntegrationService
- BankReconciliationService, FinancialReportService

**Filament Resources (8 files):**
- ChartOfAccountResource, JournalEntryResource
- BankAccountResource, BankTransactionResource
- ExpenseCategoryResource, VendorResource
- ExpenseResource, PaymentVoucherResource

**Filament Pages (3 files):**
- AccountsDashboard, FinancialReports, BankReconciliation

**Seeders (2 files):**
- ChartOfAccountsSeeder (default COA)
- ExpenseCategoriesSeeder (default categories)

**Observers (1 file):**
- PaymentTransactionObserver (auto-post fee payments)

**PDF Views (3 files):**
- income-expense-report.blade.php
- cash-flow-report.blade.php
- payment-voucher.blade.php

---

## Implementation Order

1. **Phase 1 - Core Structure**
   - Migrations for all tables
   - Models with relationships
   - Seeders for default data

2. **Phase 2 - Services**
   - JournalEntryService
   - AccountingIntegrationService

3. **Phase 3 - Filament Resources**
   - ChartOfAccountResource
   - BankAccountResource
   - VendorResource
   - ExpenseCategoryResource

4. **Phase 4 - Transaction Management**
   - ExpenseResource
   - PaymentVoucherResource
   - JournalEntryResource
   - BankTransactionResource

5. **Phase 5 - Integration & Reports**
   - PaymentTransactionObserver
   - AccountsDashboard
   - FinancialReports
   - BankReconciliation

6. **Phase 6 - Testing & Migration**
   - Run migrations
   - Seed default data
   - Test integration

---

## Summary

| Component | Count |
|-----------|-------|
| New Tables | 11 |
| New Models | 11 |
| New Services | 4 |
| New Filament Resources | 8 |
| New Filament Pages | 3 |
| New Seeders | 2 |
| New Observers | 1 |
| New PDF Views | 3 |
| **Total New Files** | **~50 files** |

This module will provide complete financial management with:
- Simple expense recording interface
- Proper double-entry accounting behind the scenes
- Full integration with existing fee and payroll systems
- Basic financial reporting with PDF export
- Bank account management and reconciliation
