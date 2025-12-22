<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounts Module Practical Guide</title>
    <style>
        @page {
            margin: 2.5cm 2cm 2.5cm 3cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }

        /* Cover Page */
        .cover-page {
            text-align: center;
            padding-top: 150px;
            page-break-after: always;
        }

        .cover-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 20px;
        }

        .cover-subtitle {
            font-size: 16pt;
            color: #2d3748;
            margin-bottom: 40px;
        }

        .cover-school {
            font-size: 14pt;
            color: #4a5568;
            margin-top: 100px;
        }

        .cover-date {
            font-size: 12pt;
            color: #718096;
            margin-top: 20px;
        }

        /* Headers */
        h1 {
            font-size: 20pt;
            color: #1a365d;
            border-bottom: 3px solid #3182ce;
            padding-bottom: 10px;
            margin-bottom: 20px;
            margin-top: 30px;
        }

        h2 {
            font-size: 16pt;
            color: #2c5282;
            margin-top: 25px;
            margin-bottom: 15px;
            border-left: 4px solid #3182ce;
            padding-left: 10px;
        }

        h3 {
            font-size: 13pt;
            color: #2d3748;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        h4 {
            font-size: 11pt;
            color: #4a5568;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        /* Content */
        p {
            margin-bottom: 12px;
            text-align: justify;
        }

        ul, ol {
            margin-left: 25px;
            margin-bottom: 15px;
        }

        li {
            margin-bottom: 6px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        th {
            background-color: #2c5282;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border: 1px solid #cbd5e0;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f7fafc;
        }

        /* Boxes */
        .info-box {
            background-color: #ebf8ff;
            border: 1px solid #90cdf4;
            border-left: 4px solid #3182ce;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .example-box {
            background-color: #f0fff4;
            border: 1px solid #9ae6b4;
            border-left: 4px solid #38a169;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .example-box-title {
            font-weight: bold;
            color: #276749;
            margin-bottom: 10px;
            font-size: 11pt;
        }

        .warning-box {
            background-color: #fffaf0;
            border: 1px solid #fbd38d;
            border-left: 4px solid #dd6b20;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .tip-box {
            background-color: #faf5ff;
            border: 1px solid #d6bcfa;
            border-left: 4px solid #805ad5;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        /* Workflow Diagrams */
        .workflow {
            background-color: #f7fafc;
            border: 2px solid #e2e8f0;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .workflow-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 12pt;
            text-align: center;
        }

        .workflow-step {
            background-color: #fff;
            border: 1px solid #cbd5e0;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 4px;
        }

        .workflow-arrow {
            text-align: center;
            color: #3182ce;
            font-size: 16pt;
            margin: 5px 0;
        }

        /* Module Cards */
        .module-card {
            border: 2px solid #e2e8f0;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background-color: #fff;
        }

        .module-header {
            background-color: #2c5282;
            color: white;
            padding: 12px 15px;
            margin: -20px -20px 15px -20px;
            border-radius: 6px 6px 0 0;
            font-size: 14pt;
            font-weight: bold;
        }

        /* Code/Reference */
        .code {
            background-color: #edf2f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 10pt;
        }

        /* Page breaks */
        .page-break {
            page-break-after: always;
        }

        /* TOC */
        .toc {
            page-break-after: always;
        }

        .toc-title {
            font-size: 20pt;
            color: #1a365d;
            margin-bottom: 30px;
            text-align: center;
        }

        .toc-item {
            padding: 8px 0;
            border-bottom: 1px dotted #cbd5e0;
        }

        .toc-section {
            font-weight: bold;
            color: #2c5282;
        }

        /* Interaction Diagram */
        .interaction-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .interaction-row {
            display: table-row;
        }

        .interaction-cell {
            display: table-cell;
            border: 1px solid #cbd5e0;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .interaction-header {
            background-color: #2c5282;
            color: white;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #718096;
            padding: 10px 0;
        }

        /* Scenario styling */
        .scenario {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .scenario-header {
            background-color: #667eea;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }

        .scenario-content {
            padding: 15px;
        }

        .scenario-step {
            display: flex;
            margin: 10px 0;
        }

        .step-number {
            background-color: #667eea;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-title">Accounts Module</div>
        <div class="cover-title" style="font-size: 24pt;">Practical Guide</div>
        <div class="cover-subtitle">Understanding Each Module with Real School Examples</div>
        <div class="cover-subtitle" style="font-size: 14pt; color: #667eea;">
            When to Use Each Module & How They Work Together
        </div>
        <div class="cover-school">
            {{ $settings->school_name ?? 'St. Francis of Assisi Primary School' }}
        </div>
        <div class="cover-date">
            Generated: {{ $generatedAt->format('F d, Y') }}
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
        <div class="toc-title">Table of Contents</div>

        <div class="toc-item toc-section">1. Introduction & Module Overview</div>
        <div class="toc-item toc-section">2. Accounts Dashboard</div>
        <div class="toc-item toc-section">3. Chart of Accounts</div>
        <div class="toc-item toc-section">4. Bank Accounts</div>
        <div class="toc-item toc-section">5. Bank Transactions</div>
        <div class="toc-item toc-section">6. Expense Categories</div>
        <div class="toc-item toc-section">7. Vendors/Suppliers</div>
        <div class="toc-item toc-section">8. Expenses</div>
        <div class="toc-item toc-section">9. Payment Vouchers</div>
        <div class="toc-item toc-section">10. Financial Reports</div>
        <div class="toc-item toc-section">11. Module Interactions Map</div>
        <div class="toc-item toc-section">12. Complete Workflow Examples</div>
        <div class="toc-item toc-section">13. Quick Reference Guide</div>
    </div>

    <!-- Section 1: Introduction -->
    <h1>1. Introduction & Module Overview</h1>

    <p>This guide provides a practical, hands-on understanding of the Accounts Module. Each section explains what the module does, when you should use it, and includes real examples from school operations.</p>

    <h2>How the Modules Connect</h2>

    <div class="workflow">
        <div class="workflow-title">The Flow of Money in Your School</div>

        <table>
            <tr>
                <td style="width: 33%; text-align: center; background-color: #c6f6d5;">
                    <strong>MONEY COMES IN</strong><br>
                    <small>Student Fees, Donations, Grants</small>
                </td>
                <td style="width: 33%; text-align: center; background-color: #bee3f8;">
                    <strong>STORED IN</strong><br>
                    <small>Bank Accounts</small>
                </td>
                <td style="width: 33%; text-align: center; background-color: #fed7d7;">
                    <strong>MONEY GOES OUT</strong><br>
                    <small>Expenses, Salaries, Supplies</small>
                </td>
            </tr>
        </table>

        <p style="text-align: center; margin-top: 15px;"><strong>All transactions are recorded in the Chart of Accounts for accurate financial reporting.</strong></p>
    </div>

    <h2>Module Summary</h2>

    <table>
        <tr>
            <th>Module</th>
            <th>Purpose</th>
            <th>When to Use</th>
        </tr>
        <tr>
            <td><strong>Dashboard</strong></td>
            <td>Overview of financial status</td>
            <td>Daily - to check balances and pending items</td>
        </tr>
        <tr>
            <td><strong>Chart of Accounts</strong></td>
            <td>Categories for all transactions</td>
            <td>Setup once, rarely modified</td>
        </tr>
        <tr>
            <td><strong>Bank Accounts</strong></td>
            <td>Track school bank accounts</td>
            <td>When opening/managing bank accounts</td>
        </tr>
        <tr>
            <td><strong>Bank Transactions</strong></td>
            <td>Record deposits & withdrawals</td>
            <td>Daily - when money moves in/out</td>
        </tr>
        <tr>
            <td><strong>Expense Categories</strong></td>
            <td>Organize spending types</td>
            <td>Setup initially, update as needed</td>
        </tr>
        <tr>
            <td><strong>Vendors/Suppliers</strong></td>
            <td>Manage who you pay</td>
            <td>When adding new suppliers</td>
        </tr>
        <tr>
            <td><strong>Expenses</strong></td>
            <td>Record all spending</td>
            <td>Whenever money is spent</td>
        </tr>
        <tr>
            <td><strong>Payment Vouchers</strong></td>
            <td>Authorize payments</td>
            <td>Before making any payment</td>
        </tr>
        <tr>
            <td><strong>Financial Reports</strong></td>
            <td>Generate reports</td>
            <td>Weekly/Monthly for management</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- Section 2: Accounts Dashboard -->
    <h1>2. Accounts Dashboard</h1>

    <div class="module-card">
        <div class="module-header">What is the Accounts Dashboard?</div>

        <p>The Accounts Dashboard is your <strong>financial control center</strong>. It provides a real-time snapshot of your school's financial health, showing key metrics, recent transactions, and alerts that need your attention.</p>

        <h3>Key Features</h3>
        <ul>
            <li><strong>Total Bank Balance:</strong> Combined balance across all school bank accounts</li>
            <li><strong>Income This Month:</strong> All money received (fees, donations, etc.)</li>
            <li><strong>Expenses This Month:</strong> All money spent</li>
            <li><strong>Pending Vouchers:</strong> Payment requests awaiting approval</li>
            <li><strong>Recent Transactions:</strong> Last 10 financial activities</li>
            <li><strong>Fee Collection Status:</strong> How much of expected fees has been collected</li>
        </ul>
    </div>

    <h2>When to Use the Dashboard</h2>

    <table>
        <tr>
            <th>Situation</th>
            <th>What to Look For</th>
            <th>Action to Take</th>
        </tr>
        <tr>
            <td>Start of each day</td>
            <td>Bank balances, pending vouchers</td>
            <td>Plan payments for the day</td>
        </tr>
        <tr>
            <td>Before making large payment</td>
            <td>Available bank balance</td>
            <td>Ensure sufficient funds</td>
        </tr>
        <tr>
            <td>Weekly review</td>
            <td>Income vs Expenses</td>
            <td>Identify cash flow issues</td>
        </tr>
        <tr>
            <td>Board meeting prep</td>
            <td>All key metrics</td>
            <td>Prepare financial summary</td>
        </tr>
    </table>

    <div class="example-box">
        <div class="example-box-title">EXAMPLE: Morning Check</div>
        <p><strong>Scenario:</strong> Mrs. Nakamya, the accountant, logs in at 8:00 AM on Monday.</p>
        <p><strong>Dashboard Shows:</strong></p>
        <ul>
            <li>Main Account Balance: UGX 45,000,000</li>
            <li>Pending Vouchers: 3 (totaling UGX 2,500,000)</li>
            <li>Fee Collection This Term: 78% collected</li>
        </ul>
        <p><strong>Action:</strong> She sees sufficient funds to approve the 3 pending vouchers for supplier payments, then follows up on the 22% uncollected fees.</p>
    </div>

    <div class="page-break"></div>

    <!-- Section 3: Chart of Accounts -->
    <h1>3. Chart of Accounts</h1>

    <div class="module-card">
        <div class="module-header">What is the Chart of Accounts?</div>

        <p>The Chart of Accounts is the <strong>backbone of your accounting system</strong>. It's a complete list of every category used to classify financial transactions. Think of it as a filing system for money.</p>

        <h3>Account Types Explained</h3>

        <table>
            <tr>
                <th>Type</th>
                <th>What It Tracks</th>
                <th>School Examples</th>
            </tr>
            <tr>
                <td style="background-color: #c6f6d5;"><strong>Assets</strong></td>
                <td>What the school OWNS</td>
                <td>Bank accounts, furniture, computers, buildings</td>
            </tr>
            <tr>
                <td style="background-color: #fed7d7;"><strong>Liabilities</strong></td>
                <td>What the school OWES</td>
                <td>Loans, unpaid supplier bills, staff advances</td>
            </tr>
            <tr>
                <td style="background-color: #bee3f8;"><strong>Equity</strong></td>
                <td>Owner's stake/reserves</td>
                <td>School development fund, retained surplus</td>
            </tr>
            <tr>
                <td style="background-color: #c6f6d5;"><strong>Income/Revenue</strong></td>
                <td>Money coming IN</td>
                <td>School fees, donations, rental income</td>
            </tr>
            <tr>
                <td style="background-color: #fed7d7;"><strong>Expenses</strong></td>
                <td>Money going OUT</td>
                <td>Salaries, utilities, supplies, maintenance</td>
            </tr>
        </table>
    </div>

    <h2>Standard School Chart of Accounts</h2>

    <table>
        <tr>
            <th>Code</th>
            <th>Account Name</th>
            <th>Type</th>
            <th>Used For</th>
        </tr>
        <tr>
            <td>1000</td>
            <td>Stanbic Bank - Main</td>
            <td>Asset</td>
            <td>Primary operating account</td>
        </tr>
        <tr>
            <td>1001</td>
            <td>Centenary Bank - Fees</td>
            <td>Asset</td>
            <td>Fee collection account</td>
        </tr>
        <tr>
            <td>1100</td>
            <td>Petty Cash</td>
            <td>Asset</td>
            <td>Small daily expenses</td>
        </tr>
        <tr>
            <td>1200</td>
            <td>Accounts Receivable</td>
            <td>Asset</td>
            <td>Outstanding student fees</td>
        </tr>
        <tr>
            <td>2000</td>
            <td>Accounts Payable</td>
            <td>Liability</td>
            <td>Unpaid supplier bills</td>
        </tr>
        <tr>
            <td>3000</td>
            <td>School Development Fund</td>
            <td>Equity</td>
            <td>Reserves for development</td>
        </tr>
        <tr>
            <td>4000</td>
            <td>Tuition Fees</td>
            <td>Income</td>
            <td>Student tuition payments</td>
        </tr>
        <tr>
            <td>4100</td>
            <td>Boarding Fees</td>
            <td>Income</td>
            <td>Boarding student payments</td>
        </tr>
        <tr>
            <td>4200</td>
            <td>Transport Fees</td>
            <td>Income</td>
            <td>Bus service payments</td>
        </tr>
        <tr>
            <td>5000</td>
            <td>Salaries & Wages</td>
            <td>Expense</td>
            <td>Staff payments</td>
        </tr>
        <tr>
            <td>5100</td>
            <td>Utilities</td>
            <td>Expense</td>
            <td>Electricity, water, internet</td>
        </tr>
        <tr>
            <td>5200</td>
            <td>Supplies</td>
            <td>Expense</td>
            <td>Office and teaching materials</td>
        </tr>
    </table>

    <h2>When to Use Chart of Accounts</h2>

    <div class="info-box">
        <strong>Setup Phase (Once):</strong> Create all accounts when first setting up the system. This is typically done by the accountant with guidance from management.
    </div>

    <div class="info-box">
        <strong>Ongoing:</strong> You don't interact with this directly often. The accounts are selected automatically when recording expenses, income, or bank transactions.
    </div>

    <div class="warning-box">
        <strong>When to Add New Accounts:</strong>
        <ul>
            <li>New income source (e.g., school starts offering swimming lessons)</li>
            <li>New expense type (e.g., school buys a bus - need "Vehicle Expenses")</li>
            <li>New bank account opened</li>
        </ul>
    </div>

    <div class="page-break"></div>

    <!-- Section 4: Bank Accounts -->
    <h1>4. Bank Accounts</h1>

    <div class="module-card">
        <div class="module-header">What is the Bank Accounts Module?</div>

        <p>This module is where you <strong>register and manage all school bank accounts</strong>. Every bank account the school holds should be set up here. This enables tracking of balances and transactions per account.</p>

        <h3>Information Stored</h3>
        <ul>
            <li><strong>Bank Name:</strong> e.g., Stanbic Bank, Centenary Bank</li>
            <li><strong>Account Name:</strong> As it appears on the account</li>
            <li><strong>Account Number:</strong> Full account number</li>
            <li><strong>Account Type:</strong> Current, Savings, Fixed Deposit</li>
            <li><strong>Opening Balance:</strong> Balance when you start using the system</li>
            <li><strong>Current Balance:</strong> Automatically calculated</li>
            <li><strong>Status:</strong> Active or Inactive</li>
        </ul>
    </div>

    <h2>When to Use This Module</h2>

    <table>
        <tr>
            <th>Situation</th>
            <th>Action</th>
            <th>Example</th>
        </tr>
        <tr>
            <td>Initial system setup</td>
            <td>Add all existing bank accounts</td>
            <td>Add Stanbic Main Account, Centenary Fees Account</td>
        </tr>
        <tr>
            <td>Opening new bank account</td>
            <td>Create new bank account record</td>
            <td>School opens savings account for building fund</td>
        </tr>
        <tr>
            <td>Closing bank account</td>
            <td>Mark account as inactive</td>
            <td>Consolidating accounts, close old account</td>
        </tr>
        <tr>
            <td>Monthly reconciliation</td>
            <td>Compare system balance vs bank statement</td>
            <td>End of month bank reconciliation</td>
        </tr>
    </table>

    <div class="example-box">
        <div class="example-box-title">EXAMPLE: Setting Up Bank Accounts</div>
        <p><strong>Scenario:</strong> St. Francis Primary School has 3 bank accounts:</p>

        <table>
            <tr>
                <th>Bank</th>
                <th>Account Name</th>
                <th>Purpose</th>
                <th>Opening Balance</th>
            </tr>
            <tr>
                <td>Stanbic Bank</td>
                <td>St. Francis Operating Account</td>
                <td>Day-to-day expenses, salaries</td>
                <td>UGX 25,000,000</td>
            </tr>
            <tr>
                <td>Centenary Bank</td>
                <td>St. Francis Fees Collection</td>
                <td>Receive student fee payments</td>
                <td>UGX 15,000,000</td>
            </tr>
            <tr>
                <td>DFCU Bank</td>
                <td>St. Francis Development Fund</td>
                <td>Savings for capital projects</td>
                <td>UGX 50,000,000</td>
            </tr>
        </table>

        <p><strong>Action:</strong> Create 3 bank account records with the above details. The system will now track each account separately.</p>
    </div>

    <h2>How Bank Accounts Connect to Other Modules</h2>

    <div class="workflow">
        <div class="workflow-title">Bank Accounts Integration</div>

        <div class="workflow-step">
            <strong>Student Fees Module</strong> → When students pay fees, money goes into a Bank Account
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Bank Transactions</strong> → Records the deposit in the selected bank account
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Expenses/Payment Vouchers</strong> → When paying, money comes out of a Bank Account
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Financial Reports</strong> → Shows balance and movement per Bank Account
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 5: Bank Transactions -->
    <h1>5. Bank Transactions</h1>

    <div class="module-card">
        <div class="module-header">What are Bank Transactions?</div>

        <p>Bank Transactions record <strong>every movement of money</strong> in and out of your bank accounts. This is the most frequently used module - you'll create records here almost daily.</p>

        <h3>Transaction Types</h3>
        <table>
            <tr>
                <th>Type</th>
                <th>Direction</th>
                <th>Examples</th>
            </tr>
            <tr>
                <td style="background-color: #c6f6d5;"><strong>Deposit</strong></td>
                <td>Money IN</td>
                <td>Fee payment, donation, grant received</td>
            </tr>
            <tr>
                <td style="background-color: #fed7d7;"><strong>Withdrawal</strong></td>
                <td>Money OUT</td>
                <td>Cash withdrawal, payment made</td>
            </tr>
            <tr>
                <td style="background-color: #bee3f8;"><strong>Transfer</strong></td>
                <td>Between accounts</td>
                <td>Move money from fees account to operating account</td>
            </tr>
        </table>
    </div>

    <h2>When to Use Bank Transactions</h2>

    <div class="scenario">
        <div class="scenario-header">Scenario 1: Recording a Fee Payment Deposit</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> Parent pays UGX 1,500,000 school fees via bank deposit.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div>Go to Bank Transactions → Create New</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div>Select Transaction Type: <strong>Deposit</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div>Select Bank Account: <strong>Centenary Fees Collection</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div>Enter Amount: <strong>UGX 1,500,000</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div>Select Category: <strong>Tuition Fees (Income)</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div>Reference: <strong>Bank slip number or student name</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">7</div>
                <div>Save → Balance automatically updates</div>
            </div>
        </div>
    </div>

    <div class="scenario">
        <div class="scenario-header">Scenario 2: Recording a Cash Withdrawal</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> Withdraw UGX 500,000 for petty cash.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div>Create new Bank Transaction</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div>Type: <strong>Withdrawal</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div>Bank Account: <strong>Stanbic Operating</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div>Amount: <strong>UGX 500,000</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div>Description: <strong>Petty cash replenishment</strong></div>
            </div>
        </div>
    </div>

    <div class="scenario">
        <div class="scenario-header">Scenario 3: Transferring Between Accounts</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> Transfer UGX 10,000,000 from Fees account to Operating account for salary payment.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div>Create new Bank Transaction</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div>Type: <strong>Transfer</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div>From Account: <strong>Centenary Fees Collection</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div>To Account: <strong>Stanbic Operating</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div>Amount: <strong>UGX 10,000,000</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div>Description: <strong>Transfer for January salaries</strong></div>
            </div>
        </div>
    </div>

    <div class="tip-box">
        <strong>TIP:</strong> Always enter transactions the same day they occur. This keeps your records accurate and makes monthly reconciliation easier.
    </div>

    <div class="page-break"></div>

    <!-- Section 6: Expense Categories -->
    <h1>6. Expense Categories</h1>

    <div class="module-card">
        <div class="module-header">What are Expense Categories?</div>

        <p>Expense Categories help you <strong>organize and classify your spending</strong>. They group similar expenses together, making it easier to analyze where your money goes and prepare budgets.</p>

        <h3>Why Categories Matter</h3>
        <ul>
            <li>See spending patterns (e.g., "We spent 40% on salaries")</li>
            <li>Compare to budget ("Utilities 20% over budget")</li>
            <li>Generate reports by category</li>
            <li>Identify cost-saving opportunities</li>
        </ul>
    </div>

    <h2>Standard School Expense Categories</h2>

    <table>
        <tr>
            <th>Category</th>
            <th>Description</th>
            <th>Example Expenses</th>
        </tr>
        <tr>
            <td><strong>Salaries & Wages</strong></td>
            <td>Staff compensation</td>
            <td>Teacher salaries, support staff wages, allowances</td>
        </tr>
        <tr>
            <td><strong>Utilities</strong></td>
            <td>Essential services</td>
            <td>Electricity, water, internet, telephone</td>
        </tr>
        <tr>
            <td><strong>Teaching Materials</strong></td>
            <td>Educational supplies</td>
            <td>Textbooks, chalk, charts, lab equipment</td>
        </tr>
        <tr>
            <td><strong>Office Supplies</strong></td>
            <td>Administrative materials</td>
            <td>Paper, pens, files, printer cartridges</td>
        </tr>
        <tr>
            <td><strong>Maintenance</strong></td>
            <td>Repairs and upkeep</td>
            <td>Building repairs, furniture repair, painting</td>
        </tr>
        <tr>
            <td><strong>Transport</strong></td>
            <td>Vehicle-related costs</td>
            <td>Fuel, vehicle maintenance, driver allowances</td>
        </tr>
        <tr>
            <td><strong>Food & Catering</strong></td>
            <td>Meals and refreshments</td>
            <td>Staff lunch, meeting refreshments, boarding food</td>
        </tr>
        <tr>
            <td><strong>Cleaning & Sanitation</strong></td>
            <td>Hygiene supplies</td>
            <td>Detergents, brooms, toilet supplies</td>
        </tr>
        <tr>
            <td><strong>Security</strong></td>
            <td>Safety-related costs</td>
            <td>Security guards, alarm systems, CCTV</td>
        </tr>
        <tr>
            <td><strong>Events & Functions</strong></td>
            <td>School activities</td>
            <td>Sports day, graduation, prize giving</td>
        </tr>
        <tr>
            <td><strong>Professional Fees</strong></td>
            <td>External services</td>
            <td>Audit fees, legal fees, consulting</td>
        </tr>
        <tr>
            <td><strong>Bank Charges</strong></td>
            <td>Banking costs</td>
            <td>Transaction fees, account maintenance</td>
        </tr>
    </table>

    <h2>When to Use Expense Categories</h2>

    <div class="info-box">
        <strong>Setup Phase:</strong> Create all categories when first setting up the system. Review existing spending to ensure all types are covered.
    </div>

    <div class="info-box">
        <strong>Adding New Categories:</strong> When a new type of expense arises that doesn't fit existing categories (e.g., school starts ICT program → add "ICT Expenses" category).
    </div>

    <div class="example-box">
        <div class="example-box-title">EXAMPLE: Using Categories for Budget Analysis</div>
        <p>At the end of Term 1, the Head Teacher asks: "What are our biggest expenses?"</p>
        <p>The system generates a report showing:</p>
        <table>
            <tr>
                <th>Category</th>
                <th>Amount (UGX)</th>
                <th>% of Total</th>
            </tr>
            <tr>
                <td>Salaries & Wages</td>
                <td>35,000,000</td>
                <td>58%</td>
            </tr>
            <tr>
                <td>Utilities</td>
                <td>5,500,000</td>
                <td>9%</td>
            </tr>
            <tr>
                <td>Teaching Materials</td>
                <td>4,200,000</td>
                <td>7%</td>
            </tr>
            <tr>
                <td>Food & Catering</td>
                <td>6,000,000</td>
                <td>10%</td>
            </tr>
            <tr>
                <td>Other</td>
                <td>9,300,000</td>
                <td>16%</td>
            </tr>
        </table>
        <p><strong>Insight:</strong> This shows salaries are the main cost. If the school needs to reduce costs, focus should be on non-salary areas.</p>
    </div>

    <div class="page-break"></div>

    <!-- Section 7: Vendors/Suppliers -->
    <h1>7. Vendors/Suppliers</h1>

    <div class="module-card">
        <div class="module-header">What is the Vendors/Suppliers Module?</div>

        <p>The Vendors module stores information about <strong>all companies and individuals you make payments to</strong>. This creates a database of your suppliers for easy reference and reporting.</p>

        <h3>Information Stored</h3>
        <ul>
            <li><strong>Vendor Name:</strong> Company or person name</li>
            <li><strong>Contact Person:</strong> Who to call</li>
            <li><strong>Phone Number:</strong> Contact details</li>
            <li><strong>Email:</strong> For sending purchase orders</li>
            <li><strong>Address:</strong> Physical location</li>
            <li><strong>TIN Number:</strong> Tax Identification Number (for tax compliance)</li>
            <li><strong>Bank Details:</strong> For electronic payments</li>
            <li><strong>Payment Terms:</strong> e.g., "Net 30 days"</li>
        </ul>
    </div>

    <h2>Types of School Vendors</h2>

    <table>
        <tr>
            <th>Vendor Type</th>
            <th>Examples</th>
            <th>Common Purchases</th>
        </tr>
        <tr>
            <td><strong>Stationery Suppliers</strong></td>
            <td>Kampala Stationers, Office Mart</td>
            <td>Paper, pens, files, exercise books</td>
        </tr>
        <tr>
            <td><strong>Book Suppliers</strong></td>
            <td>Fountain Publishers, MK Publishers</td>
            <td>Textbooks, reference books</td>
        </tr>
        <tr>
            <td><strong>Food Suppliers</strong></td>
            <td>Fresh Foods Ltd, Grain Traders</td>
            <td>Posho, beans, rice, cooking oil</td>
        </tr>
        <tr>
            <td><strong>Utility Companies</strong></td>
            <td>Umeme, NWSC</td>
            <td>Electricity, water</td>
        </tr>
        <tr>
            <td><strong>Maintenance</strong></td>
            <td>ABC Builders, Plumber John</td>
            <td>Repairs, construction</td>
        </tr>
        <tr>
            <td><strong>Fuel Suppliers</strong></td>
            <td>Shell Station, Total</td>
            <td>Vehicle fuel, generator fuel</td>
        </tr>
        <tr>
            <td><strong>Service Providers</strong></td>
            <td>Security Co., Cleaning Services</td>
            <td>Ongoing services</td>
        </tr>
        <tr>
            <td><strong>ICT Suppliers</strong></td>
            <td>Computer Point, Tech Solutions</td>
            <td>Computers, printers, repairs</td>
        </tr>
    </table>

    <h2>When to Use the Vendors Module</h2>

    <div class="scenario">
        <div class="scenario-header">Scenario: Adding a New Supplier</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> School finds a new stationery supplier with better prices.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div>Go to Vendors → Create New</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div>Enter: <strong>Quality Stationers Ltd</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div>Contact: <strong>Mr. James Okello, 0772-123456</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div>TIN: <strong>1001234567</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div>Bank: <strong>Stanbic Bank, A/C 9030012345678</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div>Terms: <strong>Cash on delivery</strong></div>
            </div>
        </div>
    </div>

    <h2>How Vendors Connect to Other Modules</h2>

    <div class="workflow">
        <div class="workflow-step">
            <strong>Create Vendor</strong> → One-time setup for each supplier
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Create Expense</strong> → Select vendor from dropdown list
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Create Payment Voucher</strong> → Vendor details auto-populate
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step">
            <strong>Reports</strong> → See all payments made to each vendor
        </div>
    </div>

    <div class="tip-box">
        <strong>TIP:</strong> Always verify TIN numbers with URA before adding vendors. This ensures proper tax compliance and valid receipts.
    </div>

    <div class="page-break"></div>

    <!-- Section 8: Expenses -->
    <h1>8. Expenses</h1>

    <div class="module-card">
        <div class="module-header">What is the Expenses Module?</div>

        <p>The Expenses module is where you <strong>record every purchase and payment</strong> the school makes. This is one of the most frequently used modules - you'll create expense records almost daily.</p>

        <h3>Expense Record Contains</h3>
        <ul>
            <li><strong>Date:</strong> When the expense occurred</li>
            <li><strong>Vendor:</strong> Who was paid (from vendors list)</li>
            <li><strong>Category:</strong> Type of expense (from categories)</li>
            <li><strong>Description:</strong> What was purchased</li>
            <li><strong>Amount:</strong> How much was paid</li>
            <li><strong>Payment Method:</strong> Cash, Cheque, Bank Transfer, Mobile Money</li>
            <li><strong>Bank Account:</strong> Which account was used (if not cash)</li>
            <li><strong>Receipt/Invoice Number:</strong> For reference</li>
            <li><strong>Attachments:</strong> Scanned receipts/invoices</li>
        </ul>
    </div>

    <h2>Common School Expenses</h2>

    <div class="example-box">
        <div class="example-box-title">Daily/Weekly Expenses</div>
        <table>
            <tr>
                <th>Expense</th>
                <th>Category</th>
                <th>Typical Amount</th>
            </tr>
            <tr>
                <td>Chalk and markers</td>
                <td>Teaching Materials</td>
                <td>UGX 50,000</td>
            </tr>
            <tr>
                <td>Cleaning supplies</td>
                <td>Cleaning & Sanitation</td>
                <td>UGX 100,000</td>
            </tr>
            <tr>
                <td>Printing paper</td>
                <td>Office Supplies</td>
                <td>UGX 150,000</td>
            </tr>
            <tr>
                <td>Vehicle fuel</td>
                <td>Transport</td>
                <td>UGX 200,000</td>
            </tr>
        </table>
    </div>

    <div class="example-box">
        <div class="example-box-title">Monthly Expenses</div>
        <table>
            <tr>
                <th>Expense</th>
                <th>Category</th>
                <th>Typical Amount</th>
            </tr>
            <tr>
                <td>Staff salaries</td>
                <td>Salaries & Wages</td>
                <td>UGX 15,000,000</td>
            </tr>
            <tr>
                <td>Electricity bill</td>
                <td>Utilities</td>
                <td>UGX 800,000</td>
            </tr>
            <tr>
                <td>Water bill</td>
                <td>Utilities</td>
                <td>UGX 200,000</td>
            </tr>
            <tr>
                <td>Internet</td>
                <td>Utilities</td>
                <td>UGX 150,000</td>
            </tr>
            <tr>
                <td>Security services</td>
                <td>Security</td>
                <td>UGX 500,000</td>
            </tr>
        </table>
    </div>

    <h2>When to Record Expenses</h2>

    <table>
        <tr>
            <th>Payment Type</th>
            <th>When to Record</th>
            <th>Additional Steps</th>
        </tr>
        <tr>
            <td>Cash payment (petty cash)</td>
            <td>Same day as payment</td>
            <td>Keep receipt for filing</td>
        </tr>
        <tr>
            <td>Cheque payment</td>
            <td>When cheque is issued</td>
            <td>Create payment voucher first</td>
        </tr>
        <tr>
            <td>Bank transfer</td>
            <td>When transfer is made</td>
            <td>Create payment voucher, confirm transfer</td>
        </tr>
        <tr>
            <td>Mobile money</td>
            <td>When payment sent</td>
            <td>Keep transaction confirmation</td>
        </tr>
    </table>

    <div class="scenario">
        <div class="scenario-header">Complete Example: Recording a Stationery Purchase</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> School buys exercise books worth UGX 500,000 from Quality Stationers.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div>Go to Expenses → Create New</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div>Date: <strong>Today's date</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div>Vendor: <strong>Quality Stationers Ltd</strong> (select from list)</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div>Category: <strong>Teaching Materials</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div>Description: <strong>500 counter books (96 pages) for P5-P7</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div>Amount: <strong>UGX 500,000</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">7</div>
                <div>Payment Method: <strong>Cheque</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">8</div>
                <div>Bank Account: <strong>Stanbic Operating Account</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">9</div>
                <div>Invoice #: <strong>INV-2024-0892</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">10</div>
                <div>Attach: <strong>Scanned invoice</strong></div>
            </div>
            <div class="scenario-step">
                <div class="step-number">11</div>
                <div>Save → Expense recorded, bank balance updated</div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 9: Payment Vouchers -->
    <h1>9. Payment Vouchers</h1>

    <div class="module-card">
        <div class="module-header">What are Payment Vouchers?</div>

        <p>A Payment Voucher is an <strong>official authorization document</strong> that approves a payment before it's made. It creates a paper trail and ensures proper approval for all expenditures.</p>

        <h3>Why Payment Vouchers Matter</h3>
        <ul>
            <li><strong>Control:</strong> Prevents unauthorized spending</li>
            <li><strong>Approval Trail:</strong> Shows who authorized each payment</li>
            <li><strong>Documentation:</strong> Links payment to supporting documents</li>
            <li><strong>Audit Compliance:</strong> Required for financial audits</li>
            <li><strong>Budget Control:</strong> Ensures spending stays within limits</li>
        </ul>
    </div>

    <h2>Payment Voucher Workflow</h2>

    <div class="workflow">
        <div class="workflow-title">The Payment Voucher Process</div>

        <div class="workflow-step" style="background-color: #fff5f5;">
            <strong>Step 1: REQUEST</strong><br>
            Staff member requests payment → Creates voucher with details
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step" style="background-color: #fffff0;">
            <strong>Step 2: REVIEW</strong><br>
            Accountant reviews → Checks budget, supporting documents
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step" style="background-color: #f0fff4;">
            <strong>Step 3: APPROVE</strong><br>
            Head Teacher/Director approves → Signs voucher
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step" style="background-color: #ebf8ff;">
            <strong>Step 4: PAY</strong><br>
            Accountant makes payment → Cheque or bank transfer
        </div>
        <div class="workflow-arrow">↓</div>
        <div class="workflow-step" style="background-color: #faf5ff;">
            <strong>Step 5: RECORD</strong><br>
            Expense recorded → Bank transaction created
        </div>
    </div>

    <h2>Voucher Statuses</h2>

    <table>
        <tr>
            <th>Status</th>
            <th>Meaning</th>
            <th>Next Action</th>
        </tr>
        <tr>
            <td style="background-color: #fefcbf;"><strong>Pending</strong></td>
            <td>Awaiting review</td>
            <td>Accountant to review</td>
        </tr>
        <tr>
            <td style="background-color: #c6f6d5;"><strong>Approved</strong></td>
            <td>Authorized for payment</td>
            <td>Accountant to make payment</td>
        </tr>
        <tr>
            <td style="background-color: #fed7d7;"><strong>Rejected</strong></td>
            <td>Not approved</td>
            <td>Requester informed, no payment</td>
        </tr>
        <tr>
            <td style="background-color: #bee3f8;"><strong>Paid</strong></td>
            <td>Payment completed</td>
            <td>File voucher with receipt</td>
        </tr>
    </table>

    <div class="scenario">
        <div class="scenario-header">Complete Example: Creating a Payment Voucher</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> The school needs to pay Umeme UGX 850,000 for electricity.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div><strong>Accountant creates voucher:</strong>
                    <ul style="margin-left: 30px; margin-top: 5px;">
                        <li>Payee: Umeme Uganda Limited</li>
                        <li>Amount: UGX 850,000</li>
                        <li>Purpose: Electricity bill for November 2024</li>
                        <li>Category: Utilities</li>
                        <li>Attach: Electricity bill scan</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div><strong>Head Teacher reviews:</strong>
                    <ul style="margin-left: 30px; margin-top: 5px;">
                        <li>Checks budget for utilities</li>
                        <li>Verifies bill is genuine</li>
                        <li>Approves voucher in system</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div><strong>Accountant makes payment:</strong>
                    <ul style="margin-left: 30px; margin-top: 5px;">
                        <li>Writes cheque or initiates bank transfer</li>
                        <li>Updates voucher status to "Paid"</li>
                        <li>Enters cheque number/transaction reference</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div><strong>System automatically:</strong>
                    <ul style="margin-left: 30px; margin-top: 5px;">
                        <li>Creates expense record</li>
                        <li>Updates bank balance</li>
                        <li>Updates expense reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h2>When to Use Payment Vouchers</h2>

    <table>
        <tr>
            <th>Payment Type</th>
            <th>Voucher Required?</th>
            <th>Reason</th>
        </tr>
        <tr>
            <td>Supplier payments</td>
            <td style="color: green;"><strong>YES</strong></td>
            <td>All supplier payments need approval</td>
        </tr>
        <tr>
            <td>Utility bills</td>
            <td style="color: green;"><strong>YES</strong></td>
            <td>Formal approval needed</td>
        </tr>
        <tr>
            <td>Staff salaries</td>
            <td style="color: green;"><strong>YES</strong></td>
            <td>Payroll approved monthly</td>
        </tr>
        <tr>
            <td>Petty cash (small)</td>
            <td style="color: orange;"><strong>Optional</strong></td>
            <td>Can use petty cash vouchers instead</td>
        </tr>
        <tr>
            <td>Large capital purchases</td>
            <td style="color: green;"><strong>YES</strong></td>
            <td>May need Board approval too</td>
        </tr>
    </table>

    <div class="warning-box">
        <strong>IMPORTANT:</strong> Never make payments without proper voucher approval. This protects both the school and the accountant from fraud allegations.
    </div>

    <div class="page-break"></div>

    <!-- Section 10: Financial Reports -->
    <h1>10. Financial Reports</h1>

    <div class="module-card">
        <div class="module-header">What are Financial Reports?</div>

        <p>Financial Reports transform your daily transactions into <strong>meaningful summaries</strong> that help management make informed decisions. The system generates several types of reports.</p>
    </div>

    <h2>Available Reports</h2>

    <table>
        <tr>
            <th>Report</th>
            <th>What It Shows</th>
            <th>When to Use</th>
        </tr>
        <tr>
            <td><strong>Income Statement</strong></td>
            <td>Revenue minus Expenses = Profit/Loss</td>
            <td>Monthly, termly, annually</td>
        </tr>
        <tr>
            <td><strong>Balance Sheet</strong></td>
            <td>Assets, Liabilities, Equity</td>
            <td>End of term, year end, audits</td>
        </tr>
        <tr>
            <td><strong>Cash Flow Statement</strong></td>
            <td>Where money came from and went</td>
            <td>Monthly cash planning</td>
        </tr>
        <tr>
            <td><strong>Bank Reconciliation</strong></td>
            <td>System balance vs Bank statement</td>
            <td>Monthly</td>
        </tr>
        <tr>
            <td><strong>Expense Report</strong></td>
            <td>All expenses by category</td>
            <td>Budget review meetings</td>
        </tr>
        <tr>
            <td><strong>Vendor Payment Report</strong></td>
            <td>Payments made to each supplier</td>
            <td>Supplier negotiations, audits</td>
        </tr>
        <tr>
            <td><strong>Trial Balance</strong></td>
            <td>All account balances</td>
            <td>Month end, before closing</td>
        </tr>
    </table>

    <h2>Understanding Key Reports</h2>

    <h3>Income Statement (Profit & Loss)</h3>

    <div class="example-box">
        <div class="example-box-title">Sample Income Statement - Term 1, 2024</div>
        <table>
            <tr>
                <th colspan="2" style="background-color: #c6f6d5;">INCOME</th>
            </tr>
            <tr>
                <td>Tuition Fees</td>
                <td style="text-align: right;">45,000,000</td>
            </tr>
            <tr>
                <td>Boarding Fees</td>
                <td style="text-align: right;">12,000,000</td>
            </tr>
            <tr>
                <td>Transport Fees</td>
                <td style="text-align: right;">3,500,000</td>
            </tr>
            <tr>
                <td>Other Income</td>
                <td style="text-align: right;">500,000</td>
            </tr>
            <tr>
                <td><strong>Total Income</strong></td>
                <td style="text-align: right;"><strong>61,000,000</strong></td>
            </tr>
            <tr>
                <th colspan="2" style="background-color: #fed7d7;">EXPENSES</th>
            </tr>
            <tr>
                <td>Salaries & Wages</td>
                <td style="text-align: right;">35,000,000</td>
            </tr>
            <tr>
                <td>Utilities</td>
                <td style="text-align: right;">2,500,000</td>
            </tr>
            <tr>
                <td>Teaching Materials</td>
                <td style="text-align: right;">4,000,000</td>
            </tr>
            <tr>
                <td>Food (Boarding)</td>
                <td style="text-align: right;">6,000,000</td>
            </tr>
            <tr>
                <td>Maintenance</td>
                <td style="text-align: right;">2,000,000</td>
            </tr>
            <tr>
                <td>Other Expenses</td>
                <td style="text-align: right;">3,500,000</td>
            </tr>
            <tr>
                <td><strong>Total Expenses</strong></td>
                <td style="text-align: right;"><strong>53,000,000</strong></td>
            </tr>
            <tr style="background-color: #bee3f8;">
                <td><strong>NET SURPLUS</strong></td>
                <td style="text-align: right;"><strong>8,000,000</strong></td>
            </tr>
        </table>
        <p style="margin-top: 10px;"><strong>Interpretation:</strong> The school made a surplus of UGX 8 million this term. This can be saved for development or emergencies.</p>
    </div>

    <h3>When to Generate Each Report</h3>

    <table>
        <tr>
            <th>Frequency</th>
            <th>Reports to Generate</th>
            <th>Who Receives</th>
        </tr>
        <tr>
            <td><strong>Weekly</strong></td>
            <td>Cash Position, Outstanding Payments</td>
            <td>Accountant, Head Teacher</td>
        </tr>
        <tr>
            <td><strong>Monthly</strong></td>
            <td>Income Statement, Bank Reconciliation, Expense Report</td>
            <td>Head Teacher, Director</td>
        </tr>
        <tr>
            <td><strong>Termly</strong></td>
            <td>All financial statements, Fee Collection Report</td>
            <td>Board of Directors</td>
        </tr>
        <tr>
            <td><strong>Annually</strong></td>
            <td>Complete Financial Statements, Audit Report</td>
            <td>Board, Auditors, Regulators</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- Section 11: Module Interactions Map -->
    <h1>11. Module Interactions Map</h1>

    <p>Understanding how modules connect helps you use the system effectively. Here's how data flows between modules:</p>

    <h2>The Complete Picture</h2>

    <div class="workflow">
        <div class="workflow-title">How All Modules Work Together</div>

        <table style="margin: 0;">
            <tr>
                <td style="background-color: #c6f6d5; text-align: center; font-weight: bold;" colspan="3">
                    MONEY IN (Student Fees Module)
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #bee3f8; text-align: center; font-weight: bold;" colspan="3">
                    BANK ACCOUNTS (Where money is stored)
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #e9d8fd; text-align: center; font-weight: bold;" colspan="3">
                    BANK TRANSACTIONS (Records all movements)
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #fefcbf; text-align: center; width: 33%;">
                    <strong>VENDORS</strong><br>
                    <small>Who we pay</small>
                </td>
                <td style="background-color: #fefcbf; text-align: center; width: 33%;">
                    <strong>EXPENSE CATEGORIES</strong><br>
                    <small>What we pay for</small>
                </td>
                <td style="background-color: #fefcbf; text-align: center; width: 33%;">
                    <strong>CHART OF ACCOUNTS</strong><br>
                    <small>How we classify</small>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #fed7d7; text-align: center; font-weight: bold;" colspan="3">
                    PAYMENT VOUCHERS (Approval before payment)
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #fed7d7; text-align: center; font-weight: bold;" colspan="3">
                    EXPENSES (Record of all spending)
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">↓</td>
            </tr>
            <tr>
                <td style="background-color: #c6f6d5; text-align: center; font-weight: bold;" colspan="3">
                    FINANCIAL REPORTS (Summary for management)
                </td>
            </tr>
        </table>
    </div>

    <h2>Specific Module Connections</h2>

    <table>
        <tr>
            <th>From Module</th>
            <th>To Module</th>
            <th>What Flows</th>
        </tr>
        <tr>
            <td>Student Fees</td>
            <td>Bank Transactions</td>
            <td>Fee payments become deposits</td>
        </tr>
        <tr>
            <td>Bank Transactions</td>
            <td>Bank Accounts</td>
            <td>Updates account balances</td>
        </tr>
        <tr>
            <td>Bank Transactions</td>
            <td>Chart of Accounts</td>
            <td>Creates journal entries</td>
        </tr>
        <tr>
            <td>Vendors</td>
            <td>Expenses</td>
            <td>Supplier info for expenses</td>
        </tr>
        <tr>
            <td>Expense Categories</td>
            <td>Expenses</td>
            <td>Classification for reporting</td>
        </tr>
        <tr>
            <td>Payment Vouchers</td>
            <td>Expenses</td>
            <td>Approved vouchers become expenses</td>
        </tr>
        <tr>
            <td>Expenses</td>
            <td>Bank Transactions</td>
            <td>Payments reduce bank balance</td>
        </tr>
        <tr>
            <td>All Modules</td>
            <td>Financial Reports</td>
            <td>Data aggregated for reports</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- Section 12: Complete Workflow Examples -->
    <h1>12. Complete Workflow Examples</h1>

    <h2>Example 1: Complete Fee Collection to Expense Cycle</h2>

    <div class="scenario">
        <div class="scenario-header">The Story: A Day in the School's Financial Life</div>
        <div class="scenario-content">
            <h4>Morning: Fee Collection</h4>
            <p>Parent John Mukasa pays UGX 1,200,000 for his daughter's Term 2 fees via bank deposit to Centenary Bank.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div><strong>Fee Collection:</strong> Bursar records payment in Student Fees module</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div><strong>Auto-Created:</strong> Bank Transaction (Deposit) → Centenary Fees Account +1,200,000</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div><strong>Auto-Created:</strong> Journal Entry → DR: Bank, CR: Tuition Fees Income</div>
            </div>

            <h4>Afternoon: Need to Pay Supplier</h4>
            <p>Accountant receives invoice from Quality Stationers for UGX 350,000 for exercise books.</p>

            <div class="scenario-step">
                <div class="step-number">4</div>
                <div><strong>Create Payment Voucher:</strong>
                    <ul style="margin-left: 30px;">
                        <li>Payee: Quality Stationers Ltd</li>
                        <li>Amount: UGX 350,000</li>
                        <li>Category: Teaching Materials</li>
                        <li>Attach invoice</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div><strong>Head Teacher Approves:</strong> Reviews and approves in system</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div><strong>Accountant Pays:</strong> Transfers from Stanbic Operating to supplier bank account</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">7</div>
                <div><strong>Record Expense:</strong> Creates expense record with voucher reference</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">8</div>
                <div><strong>Auto-Created:</strong> Bank Transaction (Withdrawal) → Stanbic Account -350,000</div>
            </div>

            <h4>End of Day: Dashboard Shows</h4>
            <ul>
                <li>Centenary Fees Account: +1,200,000</li>
                <li>Stanbic Operating: -350,000</li>
                <li>Net Cash Position: +850,000</li>
                <li>Teaching Materials Expense: +350,000</li>
            </ul>
        </div>
    </div>

    <h2>Example 2: Monthly Salary Payment Process</h2>

    <div class="scenario">
        <div class="scenario-header">End of Month Salary Processing</div>
        <div class="scenario-content">
            <div class="scenario-step">
                <div class="step-number">1</div>
                <div><strong>HR/Admin prepares payroll:</strong> List of all staff with their salaries</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div><strong>Check Bank Balance:</strong> Dashboard → Ensure sufficient funds in Operating Account</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div><strong>Transfer if needed:</strong> Move money from Fees Account to Operating Account</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div><strong>Create Salary Voucher:</strong>
                    <ul style="margin-left: 30px;">
                        <li>Description: Staff Salaries - January 2024</li>
                        <li>Amount: Total payroll amount</li>
                        <li>Attach: Approved payroll sheet</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">5</div>
                <div><strong>Get Approval:</strong> Head Teacher/Director approves</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">6</div>
                <div><strong>Process Payments:</strong> Pay each staff member (bank transfer or cash)</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">7</div>
                <div><strong>Record Expense:</strong> Salaries & Wages category</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">8</div>
                <div><strong>Update Voucher:</strong> Mark as Paid</div>
            </div>
        </div>
    </div>

    <h2>Example 3: Adding a New Vendor and First Purchase</h2>

    <div class="scenario">
        <div class="scenario-header">New Supplier Onboarding</div>
        <div class="scenario-content">
            <p><strong>Situation:</strong> School finds new supplier "Tech Solutions Ltd" for computer repairs.</p>

            <div class="scenario-step">
                <div class="step-number">1</div>
                <div><strong>Create Vendor:</strong>
                    <ul style="margin-left: 30px;">
                        <li>Name: Tech Solutions Ltd</li>
                        <li>Contact: Peter Ochieng, 0755-987654</li>
                        <li>TIN: 1009876543</li>
                        <li>Bank: Equity Bank, A/C 0123456789</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">2</div>
                <div><strong>Check Expense Categories:</strong> Verify "ICT Expenses" category exists (create if not)</div>
            </div>
            <div class="scenario-step">
                <div class="step-number">3</div>
                <div><strong>First Purchase - Computer Repair UGX 200,000:</strong>
                    <ul style="margin-left: 30px;">
                        <li>Create Payment Voucher</li>
                        <li>Get Approval</li>
                        <li>Make Payment</li>
                        <li>Record Expense (Vendor auto-fills from database)</li>
                    </ul>
                </div>
            </div>
            <div class="scenario-step">
                <div class="step-number">4</div>
                <div><strong>Future Purchases:</strong> Select "Tech Solutions Ltd" from dropdown - all details ready</div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 13: Quick Reference Guide -->
    <h1>13. Quick Reference Guide</h1>

    <h2>Module Quick Reference</h2>

    <table>
        <tr>
            <th style="width: 25%;">I Want To...</th>
            <th style="width: 25%;">Go To...</th>
            <th style="width: 50%;">Steps</th>
        </tr>
        <tr>
            <td>Check school's financial position</td>
            <td>Dashboard</td>
            <td>View summary cards and charts</td>
        </tr>
        <tr>
            <td>Add a new bank account</td>
            <td>Bank Accounts</td>
            <td>Create → Enter bank details → Save</td>
        </tr>
        <tr>
            <td>Record fee payment deposit</td>
            <td>Bank Transactions</td>
            <td>Create → Deposit → Select bank → Enter amount</td>
        </tr>
        <tr>
            <td>Transfer between accounts</td>
            <td>Bank Transactions</td>
            <td>Create → Transfer → Select From/To accounts</td>
        </tr>
        <tr>
            <td>Add a new supplier</td>
            <td>Vendors</td>
            <td>Create → Enter supplier details → Save</td>
        </tr>
        <tr>
            <td>Request a payment</td>
            <td>Payment Vouchers</td>
            <td>Create → Enter details → Submit for approval</td>
        </tr>
        <tr>
            <td>Record an expense</td>
            <td>Expenses</td>
            <td>Create → Select vendor/category → Enter amount</td>
        </tr>
        <tr>
            <td>See monthly income vs expenses</td>
            <td>Financial Reports</td>
            <td>Income Statement → Select month → Generate</td>
        </tr>
        <tr>
            <td>Reconcile bank account</td>
            <td>Financial Reports</td>
            <td>Bank Reconciliation → Select account → Compare</td>
        </tr>
        <tr>
            <td>Add expense category</td>
            <td>Expense Categories</td>
            <td>Create → Enter name and description → Save</td>
        </tr>
    </table>

    <h2>Best Practices Summary</h2>

    <div class="tip-box">
        <strong>Daily Tasks:</strong>
        <ul>
            <li>Check Dashboard first thing in the morning</li>
            <li>Record all bank transactions the same day they occur</li>
            <li>Process pending payment vouchers</li>
            <li>Enter all expenses before end of day</li>
        </ul>
    </div>

    <div class="tip-box">
        <strong>Weekly Tasks:</strong>
        <ul>
            <li>Review cash position across all accounts</li>
            <li>Follow up on outstanding fee balances</li>
            <li>Verify all receipts are attached to expenses</li>
        </ul>
    </div>

    <div class="tip-box">
        <strong>Monthly Tasks:</strong>
        <ul>
            <li>Perform bank reconciliation for all accounts</li>
            <li>Generate and review Income Statement</li>
            <li>Compare actual expenses vs budget</li>
            <li>Report to Head Teacher/Board</li>
        </ul>
    </div>

    <h2>Common Mistakes to Avoid</h2>

    <div class="warning-box">
        <ul>
            <li><strong>Don't:</strong> Make payments without approved vouchers</li>
            <li><strong>Don't:</strong> Delay entering transactions (enter same day)</li>
            <li><strong>Don't:</strong> Forget to attach supporting documents</li>
            <li><strong>Don't:</strong> Use wrong expense categories</li>
            <li><strong>Don't:</strong> Skip monthly bank reconciliation</li>
            <li><strong>Don't:</strong> Share login credentials with others</li>
        </ul>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #718096;">
        <p><strong>End of Accounts Module Practical Guide</strong></p>
        <p>{{ $settings->school_name ?? 'St. Francis of Assisi Primary School' }}</p>
        <p>Generated: {{ $generatedAt->format('F d, Y') }}</p>
    </div>

</body>
</html>
