# St. Francis of Assisi School Management System
## Complete Administrator's Guide & Trainer's Handbook

**Version:** 1.0
**Last Updated:** October 2025
**For:** System Administrators & Training Staff

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Getting Started](#2-getting-started)
3. [User Management](#3-user-management)
4. [Academic Management](#4-academic-management)
5. [Homework Management](#5-homework-management)
6. [Fee Management](#6-fee-management)
7. [Bus Fare Management](#7-bus-fare-management)
8. [Payroll Management](#8-payroll-management)
9. [Library Management](#9-library-management)
10. [Student Clearance](#10-student-clearance)
11. [Reports & Analytics](#11-reports--analytics)
12. [Communication Tools](#12-communication-tools)
13. [Best Practices](#13-best-practices)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. System Overview

### 1.1 What is the SFA School Management System?

The St. Francis of Assisi School Management System is a comprehensive web-based platform designed to manage all aspects of school operations, including:

- **Student & Parent Management**: Enrollment, profiles, attendance
- **Teacher & Staff Management**: Assignments, schedules, payroll
- **Academic Management**: Grades, subjects, classes, homework
- **Financial Management**: Fees, payments, receipts, payroll
- **Library Management**: Books, loans, fines, clearances
- **Bus Management**: Routes, payments, digital passes
- **Communication**: SMS notifications, email alerts

### 1.2 User Roles & Permissions

| Role | Access Level | Primary Functions |
|------|-------------|-------------------|
| **Admin** | Full system access | All management functions |
| **Teacher** | Limited to assigned classes | Homework, grading, student view |
| **Student** | Own data only | View homework, results, fees, bus passes |
| **Parent** | Children's data only | Monitor children's progress, fees |
| **Accountant** | Financial access | Fees, payments, reports |
| **Librarian** | Library access | Books, loans, fines, clearances |
| **Nurse** | Health records | Student health information |
| **Security** | Access logs | Entry/exit monitoring |

### 1.3 System Requirements

- **Browser**: Chrome, Firefox, Safari, Edge (latest versions)
- **Internet**: Stable connection required
- **Device**: Desktop, laptop, tablet, or smartphone
- **Screen Resolution**: Minimum 1024x768 recommended

---

## 2. Getting Started

### 2.1 First-Time Login

**Step-by-Step Instructions:**

1. Open your web browser
2. Navigate to: `http://your-school-domain.com/admin`
3. Enter your username and password
4. Click **"Sign In"**

**Expected Outcome:**
- You will be redirected to the Admin Dashboard
- You'll see widgets showing system statistics
- Navigation menu on the left shows all available modules

**Default Admin Credentials (Change Immediately):**
- **Username**: `admin@sfa.edu.zm`
- **Password**: `password`

### 2.2 Changing Your Password

**Step-by-Step Instructions:**

1. Click on your profile icon (top-right corner)
2. Select **"Profile"** from dropdown
3. Click **"Change Password"**
4. Enter current password
5. Enter new password (minimum 8 characters)
6. Confirm new password
7. Click **"Update Password"**

**Expected Outcome:**
- Success notification appears
- You remain logged in
- Next login requires new password

**Security Best Practice:**
- Use strong passwords (mix of letters, numbers, symbols)
- Change password every 90 days
- Never share credentials

### 2.3 Dashboard Overview

**What You'll See:**

1. **Quick Stats Cards**:
   - Total Students
   - Total Teachers
   - Total Staff
   - Active Academic Year

2. **Recent Activity Widgets**:
   - Recent Enrollments
   - Pending Homework Submissions
   - Payment Transactions
   - Upcoming Events

3. **Financial Summary**:
   - Total Fees Collected
   - Outstanding Balances
   - Monthly Revenue Chart

**Navigation Menu (Left Sidebar):**

```
├── Dashboard
├── User Management
│   ├── Students
│   ├── Teachers
│   ├── Parents/Guardians
│   └── Employees
├── Academic Management
│   ├── Academic Years
│   ├── Terms
│   ├── Grades
│   ├── Class Sections
│   ├── Subjects
│   └── Teacher Assignments
├── Learning & Assessment
│   ├── Homework
│   ├── Homework Submissions
│   └── Results
├── Finance Management
│   ├── Fee Structures
│   ├── Student Fees
│   ├── Payment Transactions
│   ├── Bus Fare Structures
│   └── Bus Payments
├── HR & Payroll
│   ├── Employees
│   └── Payroll
├── Library Management
│   ├── Books
│   ├── Book Loans
│   └── Student Clearance
├── Communication
│   ├── Events
│   └── SMS Logs
└── Settings
```

---

## 3. User Management

### 3.1 Adding a New Student

**Step-by-Step Instructions:**

1. Navigate to **User Management → Students**
2. Click **"New Student"** button (top-right)
3. Fill in the following required fields:

**Personal Information:**
- First Name
- Last Name
- Date of Birth (use date picker)
- Gender (select from dropdown)
- Address
- Phone Number (format: +260XXXXXXXXX)

**Academic Information:**
- Student ID Number (auto-generated or manual)
- Grade (select from dropdown)
- Class Section (select from dropdown)
- Admission Date (use date picker)
- Status: Active/Inactive

**Parent/Guardian Information:**
- Select existing parent OR create new
- If creating new: Fill parent details

**Optional Fields:**
- Email Address
- Medical Conditions
- Emergency Contact
- Previous School

4. Click **"Create"** button

**Expected Outcome:**
- Success notification: "Student created successfully"
- Student appears in students list
- Student ID number generated (e.g., SFA2025001)
- User account created automatically
- Login credentials sent to parent's phone/email

**Auto-Generated Items:**
- Student user account (username: student ID number)
- Temporary password (sent via SMS)
- Fee structure assignment (based on grade)

**Common Issues & Solutions:**

| Issue | Solution |
|-------|----------|
| "Student ID already exists" | System auto-generates unique IDs; check for duplicates if manual entry |
| "Parent not found" | Create parent account first, then link to student |
| "Grade not available" | Ensure grades are created in Academic Management first |
| SMS not sent | Check parent phone number format and SMS service status |

### 3.2 Adding a New Teacher

**Step-by-Step Instructions:**

1. Navigate to **User Management → Employees**
2. Click **"New Employee"**
3. Fill in required fields:

**Personal Information:**
- First Name
- Last Name
- Date of Birth
- Gender
- NRC Number (National Registration Card)
- Phone Number
- Email Address
- Address

**Employment Information:**
- Employee ID (auto-generated)
- Position: Select **"Teacher"**
- Department (e.g., Mathematics, Science)
- Employment Type: Full-time/Part-time/Contract
- Employment Date
- Basic Salary (for payroll)

**Role Assignment:**
- Role: Select **"Teacher"** (role_id = 2)

4. Click **"Create"**

**Expected Outcome:**
- Teacher account created
- Login credentials sent via EMAIL and SMS
- Teacher appears in employees list
- Can now assign subjects and classes

**Next Steps After Creating Teacher:**
1. Assign subjects to teacher (see Section 4.5)
2. Assign class teacher role if applicable
3. Verify login credentials received

### 3.3 Adding a Parent/Guardian

**Step-by-Step Instructions:**

1. Navigate to **User Management → Parents/Guardians**
2. Click **"New Parent Guardian"**
3. Fill in required fields:

**Personal Information:**
- First Name
- Last Name
- Phone Number (required for SMS)
- Email Address (optional)
- Address
- Occupation

**Relationship:**
- Relationship to Student: Father/Mother/Guardian/Other

4. Click **"Create"**

**Expected Outcome:**
- Parent account created
- User login credentials generated
- Credentials sent via SMS and email
- Can now link to student(s)

**Linking Parent to Students:**

**Method 1: During Student Creation**
- When creating student, select parent from dropdown

**Method 2: Edit Existing Student**
1. Go to **Students** list
2. Click **Edit** on student row
3. Select parent from "Parent Guardian" dropdown
4. Click **"Save Changes"**

**Expected Outcome:**
- Parent can view linked children's information
- Parent receives notifications about children
- Parent can pay fees for linked children

### 3.4 Adding Other Staff (Accountant, Librarian, etc.)

**Step-by-Step Instructions:**

1. Navigate to **User Management → Employees**
2. Click **"New Employee"**
3. Fill in personal information (same as teacher)
4. **Important**: Select correct **Role**:
   - **Accountant** (role_id = 5): For finance staff
   - **Librarian** (role_id = 7): For library staff
   - **Nurse** (role_id = 6): For health staff
   - **Security** (role_id = 8): For security personnel
   - **Support** (role_id = 9): For other support staff
5. Click **"Create"**

**Expected Outcome:**
- Employee account created with specific role permissions
- Credentials sent via email and SMS
- Staff member can only access modules relevant to their role

**Role-Based Access Examples:**

| Role | Can Access | Cannot Access |
|------|-----------|---------------|
| Accountant | Fees, Payments, Reports | Homework, Library, Payroll |
| Librarian | Library, Book Loans, Clearances | Fees, Homework, Payroll |
| Nurse | Student health records | Fees, Academic records |

---

## 4. Academic Management

### 4.1 Setting Up Academic Year

**When to Do This:**
- Before new school year starts
- Once per year (typically December/January)

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Academic Years**
2. Click **"New Academic Year"**
3. Fill in fields:
   - **Year**: e.g., "2025" or "2025/2026"
   - **Start Date**: January 1, 2025
   - **End Date**: December 31, 2025
   - **Is Current**: Toggle ON (only one year can be current)
4. Click **"Create"**

**Expected Outcome:**
- New academic year created
- Becomes the active year for all operations
- Previous year automatically set to "not current"
- All new data (homework, results, fees) links to this year

**Important Notes:**
- Only ONE academic year can be "current" at a time
- Students, teachers continue to next year automatically
- Fee structures may need updating for new year

### 4.2 Setting Up Terms

**When to Do This:**
- Immediately after creating academic year
- Setup all 3 terms at once

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Terms**
2. Click **"New Term"**
3. Fill in for **Term 1**:
   - **Academic Year**: Select current year
   - **Term**: Select "Term 1"
   - **Start Date**: January 15, 2025
   - **End Date**: April 30, 2025
   - **Is Current**: Toggle ON
4. Click **"Create"**
5. **Repeat for Term 2 and Term 3**:
   - Term 2: May 1 - August 31
   - Term 3: September 1 - December 15
   - **Important**: Only mark current term as "Is Current"

**Expected Outcome:**
- Three terms created for the year
- Current term highlighted
- Homework and results automatically link to current term
- System shows term info in dashboard

**Best Practice:**
- Update "Is Current" when term changes
- Set term dates before term starts
- Communicate term dates to parents

### 4.3 Setting Up Grades

**When to Do This:**
- Initial system setup
- When adding new grade levels

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Grades**
2. Click **"New Grade"**
3. Fill in fields:
   - **Grade Name**: e.g., "Grade 1", "Grade 8", "Form 1"
   - **Grade Level**: 1, 2, 3, etc. (for sorting)
   - **Section**: Primary/Secondary/High School
4. Click **"Create"**
5. **Repeat for all grades in your school**

**Example Grade Setup:**

| Grade Name | Grade Level | Section |
|-----------|-------------|---------|
| Grade 1 | 1 | Primary |
| Grade 2 | 2 | Primary |
| Grade 3 | 3 | Primary |
| Grade 7 | 7 | Primary |
| Grade 8 | 8 | Junior Secondary |
| Grade 9 | 9 | Junior Secondary |

**Expected Outcome:**
- Grades available for student enrollment
- Grades appear in dropdowns when creating students
- Foundation for class sections and subjects

### 4.4 Setting Up Class Sections

**When to Do This:**
- After creating grades
- Before enrolling students

**What Are Class Sections?**
- Class sections divide grades into smaller groups
- Example: Grade 8 → Grade 8A, Grade 8B, Grade 8C
- Each section has a class teacher

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Class Sections**
2. Click **"New Class Section"**
3. Fill in fields:
   - **Grade**: Select grade (e.g., Grade 8)
   - **Section Name**: Enter letter/number (e.g., "A", "B", "Blue")
   - **Room Number**: Optional (e.g., "Room 204")
   - **Class Teacher**: Select teacher (dropdown)
   - **Capacity**: Maximum students (e.g., 40)
4. Click **"Create"**
5. **Repeat for all sections**

**Example Setup:**

```
Grade 8:
  - Grade 8A (Teacher: Mr. Mwansa, Room: 204, Capacity: 40)
  - Grade 8B (Teacher: Mrs. Banda, Room: 205, Capacity: 40)
  - Grade 8C (Teacher: Mr. Phiri, Room: 206, Capacity: 38)
```

**Expected Outcome:**
- Class sections available for student assignment
- Class teacher can view all students in their section
- Section appears as "Grade 8A" in system

**Best Practice:**
- Balance student numbers across sections
- Assign experienced teachers to larger sections
- Update class teacher if staff changes

### 4.5 Setting Up Subjects

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Subjects**
2. Click **"New Subject"**
3. Fill in fields:
   - **Subject Name**: e.g., "Mathematics", "English", "Science"
   - **Subject Code**: e.g., "MATH", "ENG", "SCI" (optional but recommended)
   - **Description**: Brief description (optional)
4. Click **"Create"**
5. **Repeat for all subjects**

**Common Subjects Setup:**

| Subject Name | Code | For Grades |
|-------------|------|-----------|
| Mathematics | MATH | All grades |
| English | ENG | All grades |
| Science | SCI | Grades 1-9 |
| Physics | PHY | Grades 10-12 |
| Chemistry | CHEM | Grades 10-12 |
| Biology | BIO | Grades 10-12 |
| History | HIST | Grades 5-12 |
| Geography | GEO | Grades 5-12 |
| Religious Education | RE | All grades |
| Physical Education | PE | All grades |

**Expected Outcome:**
- Subjects available for teacher assignments
- Subjects appear in homework creation
- Subjects used in results/report cards

### 4.6 Linking Subjects to Grades

**Why This Matters:**
- Different grades study different subjects
- Grade 1 doesn't study Chemistry, Grade 12 doesn't study "Science"
- Ensures correct subjects appear for each grade

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Grades**
2. Click **Edit** on a grade (e.g., Grade 8)
3. Scroll to **"Subjects"** section
4. Click **"Attach Subject"**
5. Select subjects applicable to this grade:
   - ✓ Mathematics
   - ✓ English
   - ✓ Science
   - ✓ Social Studies
   - ✓ Religious Education
   - ✓ Physical Education
6. Click **"Attach"**
7. **Repeat for all grades**

**Example Grade-Subject Mapping:**

**Grade 1-7 (Primary):**
- Mathematics, English, Science, Social Studies, RE, PE, Art, Music

**Grade 8-9 (Junior Secondary):**
- Mathematics, English, Science, Social Studies, RE, PE, Computer Studies

**Grade 10-12 (Senior Secondary):**
- Mathematics, English, Physics, Chemistry, Biology, History, Geography, RE, PE, Computer Studies

**Expected Outcome:**
- When creating homework for Grade 8, only Grade 8 subjects appear
- When assigning teachers, only relevant subjects shown
- Results entry limited to appropriate subjects

### 4.7 Assigning Teachers to Subjects

**Step-by-Step Instructions:**

1. Navigate to **Academic Management → Teacher Assignments**
2. Click **"New Assignment"** (or use bulk assignment if available)
3. Fill in fields:
   - **Teacher**: Select teacher (e.g., Mr. Banda)
   - **Subject**: Select subject (e.g., Mathematics)
   - **Grade**: Select grade (e.g., Grade 8)
   - **Class Section**: Select section (e.g., Grade 8A) - Optional, leave blank for all sections
   - **Academic Year**: Auto-filled with current year
4. Click **"Create"**

**Example Assignment:**

```
Teacher: Mr. John Banda
  - Mathematics → Grade 8A
  - Mathematics → Grade 8B
  - Mathematics → Grade 9A

Teacher: Mrs. Grace Mwansa
  - English → Grade 8A
  - English → Grade 8B
  - English → Grade 8C
```

**Expected Outcome:**
- Teacher can now create homework for assigned subjects/grades
- Teacher sees only assigned students when grading
- System validates teacher permissions automatically

**Bulk Assignment Feature:**
If you need to assign one teacher to multiple classes:
1. Create first assignment
2. Click **"Duplicate Assignment"**
3. Change only the class section
4. Save

**Common Issues & Solutions:**

| Issue | Solution |
|-------|----------|
| Subject not appearing | Check if subject is linked to selected grade |
| Teacher can't see students | Verify teacher assignment saved correctly |
| Permission denied | Ensure teacher has "Teacher" role (role_id = 2) |

---

## 5. Homework Management

### 5.1 Understanding Homework Workflow

**The Complete Cycle:**

```
Admin/Teacher Creates Homework
           ↓
Students See Homework (their dashboard)
           ↓
Students Submit Homework (upload file + comments)
           ↓
Teacher Reviews Submission
           ↓
Teacher Grades Submission (marks + feedback)
           ↓
Student Views Grade & Feedback
```

### 5.2 Creating Homework (As Admin)

**Step-by-Step Instructions:**

1. Navigate to **Learning & Assessment → Homework**
2. Click **"New Homework"**
3. Fill in fields:

**Basic Information:**
- **Title**: e.g., "Chapter 5 Exercises - Algebra"
- **Description**: Detailed instructions (supports rich text)
  ```
  Complete exercises 5.1 to 5.20 from textbook.
  Show all working.
  Due Monday 9 AM.
  ```

**Assignment Details:**
- **Subject**: Select subject (e.g., Mathematics)
- **Grade**: Select grade (e.g., Grade 8)
- **Class Section**: Select section or leave blank for all sections
- **Assigned By**: Select teacher (usually yourself or subject teacher)

**Dates & Scoring:**
- **Assigned Date**: Today's date (auto-filled)
- **Due Date**: Select deadline (e.g., 3 days from now)
- **Max Score**: Total marks (e.g., 20)

**File Attachment (Optional):**
- Click **"Choose File"** to attach worksheets, PDFs, etc.
- Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
- Max size: 10MB

**Status:**
- **Status**: "Active" (so students can see it)

**SMS Notification (Optional):**
- **Send SMS Notification**: Toggle ON
- System sends SMS to all parents of students in selected grade/section

4. Click **"Create"**

**Expected Outcome:**
- Homework created and visible to students
- Students see it in "Pending Homework" section
- If SMS enabled: Parents receive notification immediately
- SMS format: "Homework assigned: [Title]. Subject: [Subject]. Due: [Date]. - SFA School"

**SMS Cost Tracking:**
- Each SMS logged in system
- Cost tracked per message
- View logs: **Communication → SMS Logs**

### 5.3 Viewing Homework Submissions

**Step-by-Step Instructions:**

1. Navigate to **Learning & Assessment → Homework Submissions**
2. **Use Filters to Find Submissions:**
   - Filter by: Grade, Subject, Homework, Status
   - Example: Show all "Submitted" homework for "Grade 8" "Mathematics"

3. **Review Submission Details:**
   - Click **"View"** icon on submission row
   - You'll see:
     - Student name
     - Submission date & time
     - "Late" indicator (if submitted after due date)
     - Student's comments
     - Attached file (download button)

**Expected Outcome:**
- List of all submissions for selected filters
- Can download student files
- Can see submission timestamps

### 5.4 Grading Homework Submissions

**Step-by-Step Instructions:**

1. Navigate to **Learning & Assessment → Homework Submissions**
2. Filter to find submissions needing grading:
   - **Status**: "Submitted" (not yet graded)
3. Click **"Grade"** action button on submission
4. Fill in grading form:
   - **Marks**: Enter score (e.g., 15 out of 20)
   - **Feedback**: Write personalized feedback
     ```
     Good work! Clear understanding of concepts.
     Remember to show all working in question 12.
     Grade: B+
     ```
   - **Status**: Changes automatically to "Graded"
5. Click **"Save Changes"**

**Expected Outcome:**
- Student can now view marks and feedback
- Status changes from "Submitted" to "Graded"
- Marks appear in student dashboard
- Feedback visible to student

**Grading Best Practices:**

✓ **DO:**
- Grade within 2-3 days of submission
- Provide constructive feedback
- Be consistent with marking scheme
- Mark late submissions (show on report)

✗ **DON'T:**
- Give marks exceeding max score
- Leave feedback blank
- Grade before reviewing attached file

**Bulk Grading:**
For quick grading of similar work:
1. Use table actions to grade multiple submissions
2. Common feedback can be reused
3. Individual marks still required

---

## 6. Fee Management

### 6.1 Understanding Fee Structure

**Fee Hierarchy:**

```
Fee Structure (Template)
  ↓
Student Fee (Individual Assignment)
  ↓
Payment Transaction (Actual Payment)
```

**Example:**
1. **Fee Structure**: "Grade 8 Annual Fees - ZMW 5,000"
2. **Student Fee**: Assigned to John Mwale (Grade 8)
3. **Payment Transaction**: John paid ZMW 2,000 on Jan 15

### 6.2 Creating Fee Structure

**When to Do This:**
- Start of academic year
- When fees change
- For different grades/programs

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Fee Structures**
2. Click **"New Fee Structure"**
3. Fill in fields:

**Basic Information:**
- **Name**: e.g., "Grade 8 Annual Fees 2025"
- **Description**: "Tuition, PTA, Activities, Exams"
- **Fee Type**: Select:
  - Tuition
  - PTA
  - Activities
  - Examination
  - Registration
  - Other

**Amount & Timing:**
- **Amount**: Total annual fee (e.g., ZMW 5000.00)
- **Academic Year**: Select current year
- **Term**: Leave blank for annual, or select specific term

**Applicable To:**
- **Grade**: Select grade (e.g., Grade 8)
  - OR leave blank for "All Grades"
- **Is Active**: Toggle ON

4. Click **"Create"**

**Example Fee Structures:**

| Name | Type | Amount | Grade | Term |
|------|------|--------|-------|------|
| Grade 8 Annual Tuition 2025 | Tuition | 5,000 | Grade 8 | - |
| Examination Fee Term 1 | Examination | 250 | All Grades | Term 1 |
| PTA Levy 2025 | PTA | 150 | All Grades | - |
| Grade 12 STEM Lab Fee | Activities | 800 | Grade 12 | - |

**Expected Outcome:**
- Fee structure created and active
- Available for assignment to students
- Can be auto-assigned or manually assigned

### 6.3 Assigning Fees to Students

**Method 1: Auto-Assignment (Recommended for Annual Fees)**

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Fee Structures**
2. Find the fee structure (e.g., "Grade 8 Annual Tuition 2025")
3. Click **"Bulk Assign"** action (if available)
4. Select criteria:
   - **Grade**: Grade 8
   - **Academic Year**: 2025
   - **Term**: Leave blank or select
5. Click **"Assign to All Students"**

**Expected Outcome:**
- All Grade 8 students automatically assigned this fee
- Student Fees created for each student
- Students/parents can now view and pay

**Method 2: Manual Assignment (For Individual Cases)**

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Student Fees**
2. Click **"New Student Fee"**
3. Fill in fields:
   - **Student**: Select student name
   - **Fee Structure**: Select fee (e.g., "Grade 8 Annual Tuition")
   - **Amount**: Auto-filled from structure (can adjust if needed)
   - **Due Date**: Select deadline (e.g., March 31, 2025)
   - **Status**: "Unpaid" (auto-set)
   - **Academic Year**: Auto-filled
   - **Term**: Auto-filled or select
4. Click **"Create"**

**Expected Outcome:**
- Fee assigned to individual student
- Student sees fee in their dashboard
- Parent sees fee when logging in
- Balance tracked automatically

**When to Use Manual Assignment:**
- New student mid-year
- Special fee adjustments
- Scholarship/discount cases
- Make-up fees

### 6.4 Recording Payments

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Payment Transactions**
2. Click **"New Payment Transaction"**
3. Fill in payment details:

**Student & Fee Information:**
- **Student**: Select student name (searchable)
- **Student Fee**: Select specific fee (dropdown shows unpaid/partial fees)

**Payment Details:**
- **Amount**: Enter amount paid (e.g., ZMW 2000.00)
  - System shows remaining balance automatically
- **Payment Date**: Select date (defaults to today)
- **Payment Method**: Select:
  - Cash
  - Bank Transfer
  - Mobile Money (Airtel Money, MTN MoMo)
  - Cheque
  - Other

**Transaction Information:**
- **Transaction Reference**: Enter reference number
  - For Mobile Money: Transaction ID
  - For Bank: Deposit slip number
  - For Cash: Receipt number
- **Notes**: Optional remarks
  - "Paid via Airtel Money"
  - "Partial payment - balance due Feb 28"

**Receipt Details:**
- **Received By**: Auto-filled with your name
- **Receipt Number**: Auto-generated (e.g., PMT-000123)

4. Click **"Create"**

**Expected Outcome:**
- Payment recorded in system
- Student fee balance automatically updated
- Status changes:
  - Unpaid → Partial (if balance remains)
  - Unpaid → Paid (if fully paid)
  - Partial → Paid (if balance cleared)
- Receipt generated automatically
- Parent/Student can view and print receipt

**Payment Receipt:**
After creating payment, you can:
1. **View Receipt**: Click "View Receipt" button
2. **Print Receipt**: Click print icon
3. **Email Receipt**: Click "Email Receipt" (sent to parent email)

**Receipt Includes:**
- School logo and details
- Student information
- Fee description
- Amount paid
- Previous balance
- New balance
- Payment method
- Receipt number
- Date and time
- Received by (staff name)

### 6.5 Viewing Student Fee Balances

**Quick View Methods:**

**Method 1: Student Fees List**
1. Navigate to **Finance Management → Student Fees**
2. Use filters:
   - **Status**: "Unpaid" or "Partial"
   - **Grade**: Select specific grade
   - **Term**: Select specific term
3. View columns:
   - Student Name
   - Fee Type
   - Total Amount
   - Amount Paid
   - Balance
   - Due Date
   - Status (badge: red=unpaid, yellow=partial, green=paid)

**Method 2: Individual Student**
1. Navigate to **User Management → Students**
2. Click on student name
3. Scroll to **"Fees"** tab
4. View all fees for this student:
   - Fee type
   - Amount
   - Paid
   - Balance
   - Status

**Method 3: Payment Dashboard Widget**
1. Go to **Dashboard**
2. View **"Fee Collection Summary"** widget:
   - Total Fees Billed
   - Total Collected
   - Outstanding Balance
   - Collection Rate %

**Expected Outcome:**
- Clear visibility of all outstanding fees
- Easy identification of defaulters
- Quick access to payment history

**Generating Fee Reports:**

1. Navigate to **Finance Management → Student Fees**
2. Apply filters as needed
3. Click **"Export"** button (top-right)
4. Select format:
   - Excel (.xlsx) - For detailed analysis
   - PDF - For printing
   - CSV - For accounting software import

**Report Includes:**
- Student details
- Fee breakdown
- Payment history
- Current balances
- Payment dates
- Transaction references

### 6.6 Fee Statements & Receipts

**Printing Fee Statement (For Parent/Student):**

**Step-by-Step Instructions:**

1. Navigate to **User Management → Students**
2. Click on student name
3. Click **"Fee Statement"** button
4. Select period:
   - Current Term
   - Full Year
   - Date Range
5. Click **"Generate Statement"**

**Statement Includes:**
- School letterhead
- Student details
- All fees (itemized)
- All payments (with dates)
- Current balance
- Payment history table

**Emailing Statements:**
1. Generate statement (as above)
2. Click **"Email Statement"** button
3. Confirm parent email address
4. Add optional message
5. Click **"Send Email"**

**Expected Outcome:**
- Professional PDF statement
- Can be printed or emailed
- Parents use for payment planning
- Required for clearance

---

## 7. Bus Fare Management

### 7.1 Understanding Bus Fare System

**System Components:**

1. **Bus Fare Structure**: Route configuration (route name, amount, payment plan)
2. **Bus Payment**: Student payment record
3. **Bus Pass**: Digital pass for student (QR code)
4. **Payment Receipt**: Official payment receipt

**Payment Plans:**
- **Monthly**: Student pays per month (e.g., ZMW 200/month)
- **Per Term**: Student pays full term upfront (e.g., ZMW 800/term)

### 7.2 Creating Bus Routes (Fare Structures)

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Bus Fare Structures**
2. Click **"New Bus Fare Structure"**
3. Fill in route information:

**Route Details:**
- **Route Name**: Descriptive name
  - Examples: "Chongwe Route", "Matero Route", "Kabulonga Route"
- **Payment Plan**: Select:
  - **Monthly**: Student pays each month
  - **Per Term**: Student pays once per term

**Pricing (Based on Payment Plan):**

**If Monthly Selected:**
- **Monthly Amount**: e.g., ZMW 200.00
- Term Amount: Hidden/Not required

**If Per Term Selected:**
- Monthly Amount: Hidden/Not required
- **Term Amount**: e.g., ZMW 800.00

**Status:**
- **Is Active**: Toggle ON (makes route available for selection)

4. Click **"Create"**

**Example Routes:**

| Route Name | Payment Plan | Monthly Amount | Term Amount | Active |
|-----------|-------------|----------------|-------------|--------|
| Chongwe Route | Monthly | 200 | - | ✓ |
| Matero Route | Per Term | - | 800 | ✓ |
| Kabulonga Route | Monthly | 250 | - | ✓ |
| Town Center Route | Per Term | - | 900 | ✓ |

**Expected Outcome:**
- Routes available for student assignment
- Active routes appear in dropdowns
- Inactive routes hidden from new assignments

**Managing Routes:**

**Deactivating a Route:**
1. Find route in list
2. Click **Edit**
3. Toggle **Is Active** to OFF
4. Click **"Save Changes"**
- Existing students keep their passes
- New students can't select this route

**Updating Fares:**
1. Best practice: Create new fare structure for new year
2. Don't edit active fare structures mid-term
3. Name clearly: "Chongwe Route 2025", "Chongwe Route 2026"

### 7.3 Enrolling Students for Bus Service

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Bus Payments**
2. Click **"New Bus Payment"**
3. Fill in enrollment form:

**Student & Route:**
- **Student**: Search and select student name
- **Bus Fare Structure**: Select route
  - System shows only active routes
  - Shows payment plan and amount

**Payment Period:**
- **Year**: Select year (e.g., 2025)
- **Month**:
  - Shows only if payment plan is "Monthly"
  - Select month (January, February, etc.)
  - Hidden if payment plan is "Per Term"

**Amount Information:**
- **Amount**: Auto-filled from route structure
  - Can be adjusted if needed (e.g., discounts)
- **Due Date**: Select payment deadline
  - For monthly: Usually 5th of the month
  - For term: Usually end of first month of term

**Payment Details:**
- **Amount Paid**: Enter amount paid (can be partial)
  - Examples:
    - Full payment: Enter full amount
    - Partial: Enter partial amount (e.g., 400 out of 800)
    - Not paid yet: Enter 0
- **Balance**: Auto-calculated (Amount - Amount Paid)
- **Payment Status**: Auto-set based on payment:
  - Unpaid: Amount Paid = 0
  - Partial: 0 < Amount Paid < Amount
  - Paid: Amount Paid >= Amount

4. Click **"Create"**

**Expected Outcome:**
- Bus payment record created
- Student enrolled for bus service
- If paid/partial: Bus pass available immediately
- Student can view pass in their dashboard
- QR code generated for verification

**Auto-Calculation Feature:**

The form automatically calculates:
1. **Amount**: From selected route
2. **Balance**: Amount - Amount Paid
3. **Status**: Based on payment

**Watch the form update in real-time:**
- Select route → Amount fills
- Enter amount paid → Balance calculates
- System updates status automatically

### 7.4 Recording Bus Payments

**Scenario 1: New Payment with Immediate Payment**
- Follow Section 7.3 above
- Enter full amount in "Amount Paid" field

**Scenario 2: Recording Payment for Existing Unpaid Enrollment**

**Step-by-Step Instructions:**

1. Navigate to **Finance Management → Bus Payments**
2. Filter to find the student:
   - **Status**: "Unpaid" or "Partial"
   - **Student**: Search student name
3. Click **"Record Payment"** action button
4. Fill in quick payment form:
   - **Payment Amount**: Enter amount being paid
   - **Payment Date**: Select date (defaults to today)
   - **Payment Method**: Cash/Bank/Mobile Money
   - **Transaction Reference**: Reference number
5. Click **"Submit Payment"**

**Expected Outcome:**
- Payment added to existing record
- Balance automatically reduced
- Status updates:
  - Unpaid (ZMW 0 paid) → Partial (ZMW 400 paid)
  - Partial (ZMW 400 paid) → Paid (ZMW 800 paid)
- Bus pass becomes available if newly paid/partial
- Receipt generated

**Scenario 3: Monthly Payments**

**Example: Student on Chongwe Route (Monthly - ZMW 200/month)**

**January Payment:**
1. Create bus payment for January 2025
2. Route: Chongwe Route
3. Month: January
4. Amount: 200
5. Amount Paid: 200 (paid in full)
6. Status: Paid

**February Payment:**
1. Create NEW bus payment for February 2025
2. Route: Chongwe Route
3. Month: February
4. Amount: 200
5. Amount Paid: 200
6. Status: Paid

**Each month = Separate payment record**

**Expected Outcome:**
- Student has multiple payment records (one per month)
- Each month has own bus pass
- Pass shows valid month/year
- Pass expires at end of month

### 7.5 Student Bus Pass System

**How Bus Passes Work:**

1. **Student pays** bus fare (full or partial)
2. **System generates** digital bus pass with QR code
3. **Student downloads/views** pass on phone
4. **Student shows** pass when boarding bus
5. **Driver/Security scans** QR code or checks verification code

**Student Access to Bus Pass:**

**Student View (Student Dashboard):**
1. Student logs into their account
2. Dashboard shows **"My Bus Passes"** section
3. Student sees:
   - Active bus passes
   - Route name
   - Valid period (month or term)
   - Expiry date
   - Payment status
4. Click **"View Pass"** button
5. Pass opens in new tab
6. Student can:
   - View on phone screen
   - Print pass
   - Download as PDF

**Bus Pass Contains:**
- School logo (from public/logo.png)
- Student photo
- Student name & ID
- Route name
- Valid period
- Expiry date
- QR code (for scanning)
- Verification code (format: BUS-XXXXXX)

**QR Code Data:**
- Format: `BUS-PASS-{payment_id}-{student_id}`
- Example: `BUS-PASS-123-SFA2025045`
- Can be scanned by any QR reader

**Verification Without Scanner:**
- Verification code shown on pass
- Driver can manually check against list
- Code format: BUS-000123

**Pass Expiry:**

**Monthly Passes:**
- Valid until: Last day of the month
- Example: January pass expires Jan 31, 11:59 PM
- Student needs new pass for February

**Term Passes:**
- Valid until: Due date or end of year (Dec 31)
- Example: Term 1 pass valid until April 30
- Single pass for entire term

**Pass Status Indicators:**

| Payment Status | Pass Available? | Badge Color |
|---------------|----------------|-------------|
| Unpaid | ✗ No | Red |
| Partial | ✓ Yes | Yellow |
| Paid | ✓ Yes | Green |

**Admin Management:**

**Viewing All Passes:**
1. Navigate to **Finance Management → Bus Payments**
2. Filter by **Status**: "Paid" or "Partial"
3. Click **"Bus Pass"** action to preview any pass
4. Click **"Receipt"** to view payment receipt

**Printing Passes in Bulk:**
1. Filter to get list of paid students
2. Select multiple rows (checkboxes)
3. Click **"Print Passes"** bulk action
4. System generates PDF with all passes

**Common Issues:**

| Issue | Cause | Solution |
|-------|-------|----------|
| Student can't see pass | Payment status "Unpaid" | Record payment first |
| Pass shows expired | Date calculation issue | Check due_date field |
| Logo not showing | Missing logo file | Add logo.png to public folder |
| QR code not working | Network issue | QR code generated online, check internet |

### 7.6 Bus Payment Receipts

**Generating Receipt:**

**Method 1: From Bus Payments List**
1. Navigate to **Finance Management → Bus Payments**
2. Find student payment record
3. Click **"Receipt"** action button (green icon)
4. Receipt opens in new tab

**Method 2: Student Dashboard**
1. Student logs in
2. Goes to **"My Bus Passes"** section
3. Clicks **"Receipt"** button on their pass
4. Receipt opens in new tab

**Receipt Contains:**
- School logo and letterhead
- Receipt number (format: BUS-000123)
- Issue date
- Student details (name, ID, grade, class)
- Route information
- Payment details table:
  - Date
  - Description
  - Amount
- Financial summary:
  - Total Amount
  - Amount Paid
  - Balance Remaining
- Payment status badge
- Notes section
- Official stamp area

**Receipt Actions:**
- **Print**: Click print button
- **Download PDF**: Browser save as PDF
- **Email**: (if implemented) Email to parent

**Expected Outcome:**
- Professional official receipt
- Can be used for accounting
- Parent keeps for records
- Required for refunds/disputes

---

## 8. Payroll Management

### 8.1 Understanding Zambian Payroll System

**The system automatically calculates Zambian statutory deductions:**

**1. NAPSA (National Pension Scheme Authority)**
- Rate: 5% of basic salary
- Cap: Maximum ZMW 2,301.60 per month
- Calculation: Basic Salary × 5%
- If result > 2,301.60, use 2,301.60

**2. NHIMA (National Health Insurance Management Authority)**
- Rate: 1% of gross salary
- No cap
- Calculation: Gross Salary × 1%

**3. PAYE (Pay As You Earn - Income Tax)**
- Progressive tax rates:
  - ZMW 0 - 4,800: 0% (tax-free)
  - ZMW 4,801 - 6,900: 25%
  - ZMW 6,901 - 11,600: 30%
  - ZMW 11,601+: 37%
- Calculated on: Taxable Income (Gross Salary - NAPSA)

**Salary Components:**

```
BASIC SALARY
  + Allowances (Housing, Transport, etc.)
  = GROSS SALARY

GROSS SALARY
  - NAPSA
  = TAXABLE INCOME

TAXABLE INCOME
  - PAYE
  - NHIMA
  - Other Deductions
  = NET SALARY (Take Home)
```

### 8.2 Creating Employee Payroll

**When to Do This:**
- Monthly (typically end of month)
- After salary changes
- For new employees

**Step-by-Step Instructions:**

1. Navigate to **HR & Payroll → Payroll**
2. Click **"New Payroll"**
3. Fill in payroll form:

**Employee Selection:**
- **Employee**: Select employee from dropdown
  - Shows all employees with salary records
  - When selected, basic salary auto-fills

**Salary Information:**
- **Basic Salary**: Auto-filled from employee record
  - Can be adjusted if needed (e.g., pro-rated for partial month)
- **Pay Period Start**: Select start date (e.g., Jan 1, 2025)
- **Pay Period End**: Select end date (e.g., Jan 31, 2025)

**Allowances (Repeater - Add Multiple):**
- Click **"Add Allowance"**
- **Name**: e.g., "Housing Allowance", "Transport Allowance"
- **Amount**: e.g., ZMW 1,500.00
- Click **"Add Allowance"** again for more
- Examples:
  - Housing: 2,000
  - Transport: 500
  - Lunch: 300

**Gross Salary:**
- Auto-calculated: Basic + Sum of Allowances

**Statutory Deductions:**
- **NAPSA**: Auto-calculated (5% of basic, max 2,301.60)
- **PAYE**: Auto-calculated (progressive tax on taxable income)
- **NHIMA**: Auto-calculated (1% of gross)

**Other Deductions (Repeater - Add Multiple):**
- Click **"Add Deduction"**
- **Name**: e.g., "Loan Repayment", "Union Dues"
- **Amount**: e.g., ZMW 500.00
- Examples:
  - Staff Loan: 1,000
  - Union Dues: 50
  - Salary Advance: 500

**Total Deductions:**
- Auto-calculated: NAPSA + PAYE + NHIMA + Other Deductions

**Net Salary:**
- Auto-calculated: Gross - Total Deductions
- This is take-home pay

**Payment Information:**
- **Payment Date**: Select payment date (e.g., Jan 31, 2025)
- **Payment Method**: Select:
  - Bank Transfer
  - Cash
  - Cheque
  - Mobile Money
- **Status**: Select:
  - Pending: Not yet paid
  - Paid: Already paid
  - Cancelled: Cancelled payroll

4. Click **"Create"**

**Expected Outcome:**
- Payroll record created
- All calculations automatic
- Employee can view payslip
- Ready for payment processing

**Real-Time Calculation:**

Watch the form update automatically:
1. Select employee → Basic salary fills
2. Add allowances → Gross salary updates
3. Gross updates → NAPSA, PAYE, NHIMA recalculate
4. Add deductions → Net salary updates

**Example Calculation:**

```
Employee: Mr. John Banda
Basic Salary: ZMW 8,000.00

Allowances:
  Housing: 2,000.00
  Transport: 500.00
  Total Allowances: 2,500.00

GROSS SALARY: 10,500.00

Statutory Deductions:
  NAPSA (5% of basic): 400.00
  NHIMA (1% of gross): 105.00

Taxable Income: 10,500 - 400 = 10,100.00

PAYE Calculation:
  First 4,800 @ 0%: 0
  Next 2,100 @ 25%: 525.00
  Next 3,200 @ 30%: 960.00
  Total PAYE: 1,485.00

Other Deductions:
  Loan Repayment: 500.00

TOTAL DEDUCTIONS: 400 + 105 + 1,485 + 500 = 2,490.00

NET SALARY: 10,500 - 2,490 = ZMW 8,010.00
```

### 8.3 Bulk Payroll Generation

**When to Use:**
- Monthly payroll for all staff
- Saves time vs creating individual payrolls
- Ensures consistency

**Step-by-Step Instructions:**

1. Navigate to **HR & Payroll → Payroll**
2. Click **"Generate Bulk Payroll"** button
3. Fill in bulk generation form:
   - **Pay Period Start**: Jan 1, 2025
   - **Pay Period End**: Jan 31, 2025
   - **Payment Date**: Jan 31, 2025
   - **Employee Type**:
     - All Employees
     - Teachers only
     - Non-teaching staff only
4. Click **"Generate"**

**Expected Outcome:**
- System creates payroll for all selected employees
- Uses each employee's basic salary
- No allowances/deductions added (add manually after)
- Status: "Pending"
- Success message: "Generated 45 payroll records"

**After Bulk Generation:**
1. Review each payroll record
2. Add individual allowances (if any)
3. Add individual deductions (if any)
4. System recalculates automatically
5. Mark as "Paid" when payment processed

### 8.4 Generating Payslips

**Payslips are automatically available once payroll is created.**

**Viewing Payslip:**

**Method 1: From Payroll List**
1. Navigate to **HR & Payroll → Payroll**
2. Find employee payroll record
3. Click **"View Payslip"** action button
4. Payslip opens in new tab

**Method 2: Employee Dashboard (If Implemented)**
1. Employee logs in
2. Goes to "My Payslips"
3. Selects month
4. Views payslip

**Payslip Contains:**
- School logo and letterhead
- Pay period dates
- Employee details:
  - Name
  - Employee ID
  - Position/Department
  - NRC number
- Earnings table:
  - Basic Salary
  - Housing Allowance
  - Transport Allowance
  - Other allowances
  - **GROSS SALARY**
- Deductions table:
  - NAPSA (highlighted)
  - PAYE (highlighted)
  - NHIMA (highlighted)
  - Other deductions
  - **TOTAL DEDUCTIONS**
- Summary:
  - Gross Salary
  - Total Deductions
  - **NET SALARY** (prominent display)

**Payslip Actions:**
- **Print**: Click print button
- **Download PDF**: Browser save as PDF
- **Email**: (if implemented) Email to employee

**Printing Bulk Payslips:**
1. Navigate to **HR & Payroll → Payroll**
2. Filter by pay period
3. Select multiple employees (checkboxes)
4. Click **"Print Payslips"** bulk action
5. System generates combined PDF

**Expected Outcome:**
- Professional payslip
- Meets Zambian labor law requirements
- Shows all statutory deductions clearly
- Employee can verify calculations

### 8.5 Managing Payroll

**Editing Payroll (Before Payment):**

1. Navigate to **HR & Payroll → Payroll**
2. Find payroll record (Status: Pending)
3. Click **"Edit"**
4. Modify:
   - Allowances
   - Deductions
   - Payment date
5. System recalculates automatically
6. Click **"Save Changes"**

**Marking Payroll as Paid:**

**Method 1: Individual**
1. Find payroll record
2. Click **"Edit"**
3. Change **Status** to "Paid"
4. Confirm **Payment Date**
5. Click **"Save Changes"**

**Method 2: Bulk**
1. Filter to show "Pending" payrolls
2. Select all employees paid (checkboxes)
3. Click **"Mark as Paid"** bulk action
4. Confirm payment date
5. Click **"Confirm"**

**Expected Outcome:**
- Status changes to "Paid"
- Payment date recorded
- Payroll locked (cannot edit easily)
- Appears in payment history

**Cancelling Payroll:**

1. Find payroll record
2. Click **"Edit"**
3. Change **Status** to "Cancelled"
4. Add note explaining reason
5. Click **"Save Changes"**

**When to Cancel:**
- Employee resigned before payment
- Calculation error (create new one)
- Duplicate entry

**Payroll Reports:**

**Monthly Payroll Summary:**
1. Navigate to **HR & Payroll → Payroll**
2. Filter by pay period
3. Click **"Export"** button
4. Select format (Excel/PDF)

**Report Includes:**
- All employees
- Gross salaries
- Total deductions (breakdown)
- Net salaries
- Total cost to school
- Statutory contributions summary

**Year-to-Date Reports:**
1. Export payroll for full year
2. Use Excel to summarize:
   - Annual salary per employee
   - Total NAPSA contributions
   - Total PAYE contributions
   - Total NHIMA contributions

**For Tax Compliance:**
- Keep all payslips for 6 years
- Submit monthly PAYE to ZRA
- Submit NAPSA contributions monthly
- Submit NHIMA contributions monthly

---

## 9. Library Management

### 9.1 Understanding Library System

**System Components:**

1. **Books**: Library catalog (physical books inventory)
2. **Book Loans**: Track lending and receiving books
3. **Student Clearance**: Verify students have returned all books

**Book Categories:**
- Fiction, Non-Fiction, Science, Mathematics, History, Geography, Literature, Reference, Biography, Children, Other

**Loan Status:**
- Active: Book currently borrowed
- Returned: Book returned on time
- Overdue: Book not returned by due date
- Lost: Book reported lost

### 9.2 Adding Books to Library

**Step-by-Step Instructions:**

1. Navigate to **Library Management → Books**
2. Click **"New Book"**
3. Fill in book information:

**Book Details:**
- **ISBN**: International Standard Book Number
  - Format: 978-XXXXXXXXXX
  - Optional but recommended
- **Title**: Full book title
  - Example: "Things Fall Apart"
- **Author**: Author name
  - Example: "Chinua Achebe"
- **Publisher**: Publishing company
  - Example: "Heinemann"
- **Publication Year**: Year published
  - Example: 1958

**Categorization:**
- **Category**: Select from dropdown
  - Fiction, Science, Mathematics, etc.
- **Subject**: Optional
  - Link to academic subject if applicable

**Physical Details:**
- **Total Copies**: Number of copies in library
  - Example: 25
- **Available Copies**: Currently available
  - Initially same as total copies
  - Auto-updates when books loaned/returned
- **Shelf Location**: Where book is stored
  - Example: "A3-Fiction-1", "Science-Shelf-2"

**Additional Information:**
- **Cover Image**: Upload book cover (optional)
  - Supported: JPG, PNG
  - Max size: 2MB
- **Price**: Purchase price (optional)
  - For inventory value tracking
- **Description**: Brief description (optional)

4. Click **"Create"**

**Expected Outcome:**
- Book added to catalog
- Available for loan
- Appears in book search
- Inventory updated

**Example Books:**

| Title | Author | Category | Total Copies | Shelf |
|-------|--------|----------|-------------|-------|
| Things Fall Apart | Chinua Achebe | Literature | 25 | L-01 |
| Biology Grade 12 | Longman | Science | 30 | S-12 |
| Mathematics Grade 9 | Oxford | Mathematics | 40 | M-09 |
| Zambian History | ZNP | History | 20 | H-01 |

**Bulk Import (If Available):**
1. Prepare Excel file with columns:
   - ISBN, Title, Author, Publisher, Year, Category, Copies
2. Click **"Import Books"**
3. Upload Excel file
4. Review preview
5. Click **"Import"**

### 9.3 Lending Books to Students

**Step-by-Step Instructions:**

1. Navigate to **Library Management → Book Loans**
2. Click **"New Book Loan"** (or "Lend Book")
3. Fill in loan form:

**Borrower Information:**
- **Student**: Search and select student
  - Searchable by name or student ID
  - Shows grade and class

**Book Information:**
- **Book**: Search and select book
  - Shows title, author
  - Shows available copies
  - If available copies = 0, cannot lend

**Loan Details:**
- **Lent Date**: Date book borrowed (defaults to today)
- **Due Date**: Return deadline
  - Default: 14 days from lent date
  - Adjust based on book type:
    - Reference books: 7 days
    - Regular books: 14 days
    - Long assignments: 30 days

**Book Condition:**
- **Condition on Loan**: Select
  - Excellent: Brand new condition
  - Good: Minor wear
  - Fair: Visible wear but usable
  - Poor: Significant wear
  - Damaged: Already damaged
- Take note for comparison when returned

**Optional:**
- **Notes**: Any special notes
  - "Student requested for assignment"
  - "Extended loan for project"

4. Click **"Create"**

**Expected Outcome:**
- Loan record created
- Book status: "Active"
- Available copies decreased by 1
  - Example: 25 total, 20 available → 19 available
- Student can see borrowed book in their dashboard
- Due date tracked automatically
- Overdue status triggers if not returned

**Multiple Books for One Student:**
- Create separate loan record for each book
- Student can borrow multiple books
- Each tracked independently

**Quick Lend Feature:**
If available in your system:
1. From **Books** list
2. Click **"Lend"** action on book row
3. Select student
4. Set due date
5. Click **"Lend Book"**

### 9.4 Receiving Returned Books

**Step-by-Step Instructions:**

1. Navigate to **Library Management → Book Loans**
2. Filter to find loan:
   - **Status**: "Active" or "Overdue"
   - **Student**: Search by name
   - **Book**: Search by title
3. Click **"Return Book"** action button
4. Fill in return form:

**Return Information:**
- **Return Date**: Date book returned
  - Defaults to today
  - Can be backdated if needed

**Book Condition:**
- **Condition on Return**: Select
  - Excellent
  - Good
  - Fair
  - Poor
  - Damaged
  - Lost
- Compare with condition on loan

**Fine Calculation:**
System automatically calculates fines based on:

**1. Overdue Fine:**
- If returned after due date
- Rate: ZMW 5 per day (configurable)
- Example: 3 days late = ZMW 15

**2. Damage Fine:**
- If condition on return worse than condition on loan
- Amount based on damage severity:
  - Minor damage: ZMW 20
  - Moderate damage: ZMW 50
  - Severe damage: ZMW 100
  - Lost: Full book price

**Fine Details:**
- **Fine Amount**: Auto-calculated, can adjust
- **Fine Reason**: Auto-filled (Overdue/Damage)
- **Fine Status**:
  - Unpaid: Default
  - Paid: If student pays immediately
- **Fine Paid Date**: If paid now, enter date

5. Click **"Return Book"**

**Expected Outcome:**
- Loan status changes to "Returned" or "Overdue"
- Available copies increased by 1
  - Example: 19 available → 20 available
- Fine recorded if applicable
- Student notified (if SMS enabled)
- Loan record archived

**Handling Lost Books:**

1. Click **"Return Book"** on loan
2. **Condition on Return**: Select "Lost"
3. **Fine Amount**: Enter full book replacement cost
4. **Fine Reason**: "Lost Book"
5. Click **"Return Book"**

**Expected Outcome:**
- Loan status: "Lost"
- Fine equal to book price
- Available copies NOT increased
- Total copies decreased by 1 (optional, depends on policy)
- Student cannot borrow until fine paid

**Waiving Fines:**

If admin decides to waive fine:
1. Find loan record
2. Click **"Edit"**
3. Set **Fine Amount** to 0
4. Add **Note**: "Fine waived - first offense"
5. Click **"Save Changes"**

### 9.5 Managing Library Fines

**Viewing Outstanding Fines:**

1. Navigate to **Library Management → Book Loans**
2. Filter:
   - **Fine Status**: "Unpaid"
3. View columns:
   - Student name
   - Book title
   - Fine amount
   - Fine reason (Overdue/Damage/Lost)
   - Days overdue

**Sorting by Fine Amount:**
- Click column header to sort
- Identify highest fines first

**Recording Fine Payment:**

**Method 1: Quick Payment**
1. Find loan with unpaid fine
2. Click **"Pay Fine"** action button
3. Confirm payment:
   - **Payment Amount**: Shows fine amount
   - **Payment Date**: Today
   - **Payment Method**: Cash/Mobile Money/Bank
4. Click **"Record Payment"**

**Expected Outcome:**
- Fine status: "Paid"
- Payment date recorded
- Student account cleared
- Receipt generated (if applicable)

**Method 2: Edit Loan**
1. Find loan record
2. Click **"Edit"**
3. Change **Fine Status** to "Paid"
4. Enter **Fine Paid Date**
5. Click **"Save Changes"**

**Fine Reports:**

**Unpaid Fines Report:**
1. Navigate to **Book Loans**
2. Filter: Fine Status = "Unpaid"
3. Click **"Export"**
4. Select format (Excel/PDF)

**Report shows:**
- Student details
- Book details
- Fine amount
- Fine reason
- Days outstanding

**Fine Collection Summary:**
1. Export all loans with fines
2. Use Excel to calculate:
   - Total fines issued
   - Total fines collected
   - Total fines outstanding
   - Collection rate

### 9.6 Student Library Clearance

**Purpose:**
- Verify student has returned all books
- Verify student has paid all fines
- Required for:
  - End of term
  - End of year
  - Graduation
  - Transfer to another school

**Checking Student Clearance:**

1. Navigate to **Library Management → Student Clearance**
2. View clearance list showing all students:

**Columns Displayed:**
- **Student Name**
- **Grade & Class**
- **Active Loans**: Number of books not returned
- **Total Fines**: Amount owed
- **Clearance Status**:
  - ✓ Cleared: No active loans, no unpaid fines
  - ✗ Not Cleared: Has active loans OR unpaid fines
  - ! Pending: Some issues

**Filter Options:**
- **Grade**: View specific grade
- **Clearance Status**: View only "Not Cleared"
- **Search**: Search by student name

**Viewing Individual Student Details:**

1. Click on student name or **"View"** button
2. See detailed clearance information:

**Active Loans:**
- Book title
- Lent date
- Due date
- Days overdue
- Status

**Fines:**
- Book title
- Fine amount
- Fine reason
- Payment status

**Actions Available:**
- **View All Loans**: See complete loan history
- **View Unpaid Fines**: See only unpaid fines
- **Mark as Cleared**: Override and mark cleared (admin only)

**Clearing Process:**

**For Students with Active Loans:**
1. Student returns all books (see Section 9.4)
2. System automatically updates clearance status
3. If fines issued, student must pay first

**For Students with Unpaid Fines:**
1. Student pays all fines (see Section 9.5)
2. Librarian records payment
3. System automatically updates clearance status

**Manual Override (Use Carefully):**

If student lost book but paid replacement:
1. Click on student in clearance list
2. Click **"Mark as Cleared"** button
3. Add note explaining reason
4. Click **"Confirm"**

**Expected Outcome:**
- Clearance status changes to "Cleared"
- Student can proceed with other processes
- Override logged in system

**Clearance Reports:**

**End of Term Clearance Report:**
1. Navigate to **Student Clearance**
2. Filter by **Grade** (if needed)
3. Click **"Export Clearance Report"**
4. Select format (Excel/PDF)

**Report includes:**
- All students
- Clearance status
- Outstanding books
- Outstanding fines
- Ready for next term

**Graduation Clearance List:**
1. Filter **Grade**: Grade 12 (or final grade)
2. Filter **Status**: "Not Cleared"
3. Export list
4. Follow up with students individually

**Best Practices:**

✓ **DO:**
- Check clearance at end of each term
- Send reminders for overdue books
- Issue fines consistently
- Keep accurate records
- Update clearance status promptly

✗ **DON'T:**
- Override clearance without valid reason
- Waive fines inconsistently
- Delay recording returns
- Forget to update book conditions

---

## 10. Student Clearance

**[This section can be expanded if there's a separate general clearance system beyond library clearance]**

The current system focuses on Library Clearance (covered in Section 9.6).

**For comprehensive clearance system:**
- Library clearance (books returned, fines paid)
- Finance clearance (fees paid)
- Disciplinary clearance (no pending cases)

Check individual modules for specific clearance requirements.

---

## 11. Reports & Analytics

### 11.1 Financial Reports

**Fee Collection Reports:**

**Daily Collection Report:**
1. Navigate to **Finance Management → Payment Transactions**
2. Filter by **Payment Date**: Today
3. View total collections
4. Click **"Export"** for detailed report

**Report shows:**
- All payments received today
- Student names
- Fee types
- Amounts
- Payment methods
- Total: ZMW X,XXX.XX

**Monthly Collection Report:**
1. Filter **Payment Date**: Current month
2. Export report
3. Shows:
   - Total collected this month
   - Breakdown by grade
   - Breakdown by fee type
   - Payment methods used

**Outstanding Fees Report:**
1. Navigate to **Finance Management → Student Fees**
2. Filter **Status**: "Unpaid" or "Partial"
3. Export report
4. Shows:
   - All students with balances
   - Amount owed
   - Due dates
   - Contact information

**Use for:**
- Follow-up calls/letters
- Identifying defaulters
- Financial planning

**Term Collection Summary:**
1. Filter by **Term**: Term 1
2. Export student fees
3. Calculate:
   - Total fees billed
   - Total collected
   - Collection rate %
   - Outstanding balance

### 11.2 Academic Reports

**Homework Completion Report:**
1. Navigate to **Homework Submissions**
2. Filter by **Grade** and **Subject**
3. Export report
4. Shows:
   - Total homework assigned
   - Submissions per student
   - Late submissions
   - Completion rates

**Student Performance Report:**
1. Navigate to **Results**
2. Filter by **Grade**, **Term**, **Subject**
3. Export report
4. Analyze:
   - Average marks per subject
   - Top performers
   - Students needing support
   - Pass rates

**Teacher Performance Report:**
1. Export homework data by teacher
2. Analyze:
   - Number of assignments created
   - Grading turnaround time
   - Student performance in their subject

### 11.3 Library Reports

**Book Inventory Report:**
1. Navigate to **Books**
2. Click **"Export"**
3. Shows:
   - All books in catalog
   - Total copies
   - Available copies
   - Current loans
   - Book value

**Loan Statistics:**
1. Navigate to **Book Loans**
2. Export data
3. Analyze:
   - Most borrowed books
   - Least borrowed books
   - Average loan duration
   - Most active borrowers

**Overdue Books Report:**
1. Filter **Status**: "Overdue"
2. Export report
3. Shows:
   - Student names
   - Books overdue
   - Days overdue
   - Contact information

**Fine Collection Report:**
1. Export loans with fines
2. Calculate:
   - Total fines issued
   - Total fines collected
   - Collection rate
   - Outstanding fines

### 11.4 HR & Payroll Reports

**Monthly Payroll Summary:**
1. Navigate to **Payroll**
2. Filter by **Pay Period**
3. Export report
4. Shows:
   - All employees
   - Gross salaries
   - Deductions breakdown
   - Net salaries
   - Total payroll cost

**Statutory Contributions Report:**
1. Export payroll data
2. Calculate totals:
   - Total NAPSA: ZMW X,XXX
   - Total PAYE: ZMW X,XXX
   - Total NHIMA: ZMW X,XXX

**Use for:**
- Monthly remittances to ZRA, NAPSA, NHIMA
- Compliance verification
- Budgeting

**Annual Salary Report:**
1. Export full year payroll
2. Group by employee
3. Shows:
   - Annual salary per employee
   - Total allowances
   - Total deductions
   - Year-to-date totals

**Use for:**
- Tax filing
- Employee queries
- Budget planning

### 11.5 Student Reports

**Student List by Grade:**
1. Navigate to **Students**
2. Filter by **Grade**
3. Export list
4. Shows:
   - Student names
   - Student IDs
   - Parent contacts
   - Admission dates

**Student Master List:**
1. Export all students
2. Shows complete student database
3. Use for:
   - Enrollment statistics
   - Demographic analysis
   - Planning

**Fee Status by Student:**
1. Navigate to **Student Fees**
2. Group by student
3. Export report
4. Shows:
   - Each student's total fees
   - Amount paid
   - Balance
   - Payment status

### 11.6 Custom Reports

**Creating Custom Reports:**

Most reports can be customized by:
1. Using filters
2. Selecting columns (if available)
3. Exporting data
4. Using Excel/Google Sheets for analysis

**Excel Tips:**
- Use Pivot Tables for summaries
- Use SUMIF for conditional totals
- Use COUNTIF for statistics
- Create charts for visualization

**Common Custom Reports:**

**1. Student Performance Trend:**
- Export results for multiple terms
- Compare term-by-term performance
- Identify improvement/decline

**2. Fee Collection Trend:**
- Export payments monthly
- Compare month-to-month
- Identify peak collection periods

**3. Library Usage Trend:**
- Export loans monthly
- Track borrowing patterns
- Plan book purchases

---

## 12. Communication Tools

### 12.1 SMS Notifications

**SMS System Overview:**

The system integrates with SMS API to send notifications to parents and staff.

**When SMS Are Sent:**

**Automatic SMS:**
- New homework assigned (if enabled)
- Student credentials created
- Staff credentials created
- Bus pass issued (if enabled)

**Manual SMS:**
(If implemented in your system)
- Custom messages to parents
- Fee reminders
- Event notifications

**Viewing SMS Logs:**

1. Navigate to **Communication → SMS Logs**
2. View all sent messages:
   - **Recipient**: Phone number
   - **Message**: Content
   - **Status**: Sent/Failed/Pending
   - **Cost**: Per message cost
   - **Sent At**: Timestamp
   - **Purpose**: Why sent (Homework/Credentials/etc.)

**Filtering SMS Logs:**
- **Status**: View only failed messages
- **Purpose**: View homework notifications only
- **Date Range**: View messages in date range
- **Recipient**: Search specific number

**SMS Statistics:**

View dashboard widget or export data:
- Total SMS sent
- Total SMS cost
- Success rate
- Failed messages count

**Common Issues:**

| Issue | Cause | Solution |
|-------|-------|----------|
| SMS not delivered | Wrong number format | Use +260XXXXXXXXX format |
| SMS failed | API down | Check SMS service status |
| Duplicate SMS | Multiple triggers | Check notification settings |
| No SMS sent | SMS disabled | Enable in settings |

**SMS Best Practices:**

✓ **DO:**
- Verify phone numbers before sending
- Keep messages concise (160 characters)
- Send during reasonable hours (8 AM - 8 PM)
- Track costs regularly
- Monitor delivery rates

✗ **DON'T:**
- Send spam messages
- Send late at night
- Send duplicate notifications
- Ignore failed messages

### 12.2 Email Notifications

**When Emails Are Sent:**

**Automatic Emails:**
- Staff credentials (username/password)
- Parent credentials (username/password)
- Payment receipts (if requested)
- Fee statements (if requested)
- Payslips (if enabled)

**Manual Emails:**
- Fee statements (on-demand)
- Bus payment receipts (on-demand)
- Custom communications

**Email Templates:**

The system uses professional HTML email templates for:
- Staff credentials
- Payment confirmations
- Statements

**Checking Email Delivery:**

Currently, emails are sent directly. Check:
- Recipient's inbox (including spam folder)
- System logs (if available)

**Email Best Practices:**

✓ **DO:**
- Verify email addresses
- Use professional templates
- Include school branding
- Test emails before bulk send

✗ **DON'T:**
- Send large attachments (>5MB)
- Use all caps subject lines
- Send without testing
- Forget to include contact info

### 12.3 Events & Announcements

**Creating School Events:**

1. Navigate to **Communication → Events**
2. Click **"New Event"**
3. Fill in event details:
   - **Title**: "Mid-Term Break", "Sports Day"
   - **Description**: Full details
   - **Event Type**: Academic/Sports/Cultural/Other
   - **Start Date & Time**
   - **End Date & Time**
   - **Location**: "School Grounds", "Assembly Hall"
   - **Applicable To**:
     - All
     - Students Only
     - Specific Grade
   - **Notify**: Send SMS/Email (if enabled)
4. Click **"Create"**

**Expected Outcome:**
- Event created
- Appears on student/parent dashboards
- Shows in "Upcoming Events" section
- Notifications sent if enabled

**Managing Events:**

**Editing Events:**
1. Find event in list
2. Click **"Edit"**
3. Update details
4. Click **"Save Changes"**

**Cancelling Events:**
1. Click **"Edit"**
2. Add "CANCELLED" to title
3. Update description with cancellation notice
4. Send notification to affected users

**Past Events:**
- Automatically move to "Past Events"
- Can be archived
- Useful for annual planning

---

## 13. Best Practices

### 13.1 Data Security

**Password Management:**

✓ **DO:**
- Change default admin password immediately
- Use strong passwords (8+ characters, mixed case, numbers, symbols)
- Change passwords every 90 days
- Never share credentials

✗ **DON'T:**
- Use common passwords (password123, admin, etc.)
- Share login credentials
- Write passwords on paper
- Use same password for multiple systems

**User Account Management:**

✓ **DO:**
- Deactivate accounts for departed staff immediately
- Review user permissions regularly
- Create accounts only for legitimate users
- Keep contact information updated

✗ **DON'T:**
- Leave inactive accounts enabled
- Give excessive permissions
- Create test accounts and forget them
- Share admin accounts

**Data Backup:**

✓ **DO:**
- Backup database weekly
- Store backups off-site
- Test restore procedures
- Keep backups for 1 year

✗ **DON'T:**
- Rely on single backup
- Store backups only on server
- Never test restores
- Delete recent backups

### 13.2 Financial Management

**Fee Collection:**

✓ **DO:**
- Issue receipts for all payments
- Record payments immediately
- Reconcile daily collections
- Follow up on overdue fees
- Keep payment records for 7 years

✗ **DON'T:**
- Accept payments without recording
- Delay recording payments
- Skip reconciliation
- Delete payment records

**Payroll Management:**

✓ **DO:**
- Review payroll before payment
- Keep payslips for 6 years
- Submit statutory deductions on time
- Reconcile payroll monthly
- Maintain payroll confidentiality

✗ **DON'T:**
- Pay without review
- Miss submission deadlines
- Share salary information
- Skip reconciliation

### 13.3 Academic Management

**Homework Management:**

✓ **DO:**
- Set reasonable due dates
- Grade submissions within 3 days
- Provide meaningful feedback
- Track completion rates
- Communicate with parents

✗ **DON'T:**
- Assign homework without due dates
- Leave submissions ungraded
- Give feedback without substance
- Ignore low completion rates

**Results Management:**

✓ **DO:**
- Enter results promptly
- Verify accuracy before publishing
- Maintain result confidentiality
- Archive results properly
- Provide result analysis

✗ **DON'T:**
- Delay results entry
- Publish without verification
- Share results publicly
- Delete historical results

### 13.4 Communication

**Parent Communication:**

✓ **DO:**
- Communicate regularly
- Use professional tone
- Respond to queries promptly
- Keep parents informed
- Document all communication

✗ **DON'T:**
- Send unclear messages
- Ignore parent concerns
- Use informal language
- Over-communicate

**SMS Usage:**

✓ **DO:**
- Keep messages concise
- Send during reasonable hours
- Track SMS costs
- Monitor delivery rates
- Use for important notices

✗ **DON'T:**
- Send lengthy messages
- Send late at night
- Ignore failed deliveries
- Overuse SMS

### 13.5 System Maintenance

**Regular Tasks:**

**Daily:**
- Record all transactions
- Respond to user queries
- Check for failed SMS
- Monitor system performance

**Weekly:**
- Review pending tasks
- Check outstanding fees
- Follow up on overdue homework
- Review overdue library books

**Monthly:**
- Generate financial reports
- Process payroll
- Review user accounts
- Submit statutory contributions
- Backup database

**Termly:**
- Update term settings
- Review fee structures
- Check student clearances
- Archive old data
- Plan for next term

**Annually:**
- Create new academic year
- Update fee structures
- Review system permissions
- Audit all data
- Plan improvements

---

## 14. Troubleshooting

### 14.1 Common Login Issues

**Problem: Forgot Password**

**Solution:**
1. Click "Forgot Password" on login page
2. Enter email address
3. Check email for reset link
4. Click link and set new password
5. Login with new password

**Alternative (Admin Help):**
1. Contact system administrator
2. Admin resets your password
3. New password sent via SMS/email
4. Login and change password

**Problem: Account Locked**

**Cause:** Multiple failed login attempts

**Solution:**
1. Wait 30 minutes (auto-unlock)
2. OR contact administrator to unlock immediately
3. Admin unlocks account manually
4. Login with correct password

**Problem: "Access Denied" After Login**

**Cause:** Incorrect role or permissions

**Solution:**
1. Contact administrator
2. Admin verifies your role
3. Admin updates permissions if needed
4. Logout and login again

### 14.2 Financial Issues

**Problem: Payment Not Showing**

**Troubleshooting Steps:**
1. Verify payment was saved (check for success message)
2. Refresh the page
3. Clear browser cache
4. Check filters (ensure not filtering out the payment)
5. Search by receipt number
6. Contact administrator if still missing

**Problem: Incorrect Balance**

**Troubleshooting Steps:**
1. View all payments for the student
2. Manually calculate: Total Fee - Sum of Payments
3. If mismatch:
   - Check for duplicate payments
   - Check for deleted payments
   - Contact administrator to recalculate

**Problem: Receipt Not Generating**

**Troubleshooting Steps:**
1. Check if payment has receipt number
2. Try different browser
3. Disable pop-up blocker
4. Clear browser cache
5. Contact administrator if persistent

**Problem: SMS Not Sent for Credentials**

**Troubleshooting Steps:**
1. Check phone number format (+260XXXXXXXXX)
2. Check SMS Logs for delivery status
3. If failed, resend manually:
   - Edit user account
   - Click "Resend Credentials"
4. Verify SMS service is active

### 14.3 Academic Issues

**Problem: Teacher Can't Create Homework**

**Troubleshooting Steps:**
1. Verify teacher role (must be role_id = 2)
2. Check teacher assignments:
   - Must be assigned to subject & grade
3. Verify subject is linked to grade
4. Check current academic year is set
5. Contact administrator to verify permissions

**Problem: Student Can't See Homework**

**Troubleshooting Steps:**
1. Verify homework status is "Active"
2. Check homework grade matches student grade
3. Verify student is in correct class section
4. Check if student already submitted (won't show in pending)
5. Refresh student dashboard

**Problem: Can't Grade Homework**

**Troubleshooting Steps:**
1. Verify submission status is "Submitted"
2. Check if you're assigned to this subject/grade
3. Verify homework belongs to your subject
4. Contact administrator if permission denied

### 14.4 Library Issues

**Problem: Can't Lend Book - "No Copies Available"**

**Troubleshooting Steps:**
1. Check book record:
   - Total Copies vs Available Copies
2. Check active loans for this book
3. If mismatch:
   - Some books may not be returned properly
   - Admin recalculates available copies
4. If all copies genuinely loaned, wait for returns

**Problem: Available Copies Not Updating After Return**

**Troubleshooting Steps:**
1. Verify return was saved successfully
2. Check loan status changed to "Returned"
3. Refresh book list
4. If still wrong:
   - Admin manually adjusts available copies
   - Admin recalculates from active loans

**Problem: Fine Not Calculated**

**Troubleshooting Steps:**
1. Check due date vs return date
2. Verify fine calculation settings
3. Manually enter fine if needed
4. Contact administrator to check fine rules

**Problem: Student Shows "Not Cleared" But No Active Loans**

**Troubleshooting Steps:**
1. Check for unpaid fines
2. Verify all loans are marked "Returned"
3. Filter loans by student to see all records
4. Pay outstanding fines
5. If error, admin can manually mark "Cleared"

### 14.5 Payroll Issues

**Problem: Statutory Deductions Wrong**

**Troubleshooting Steps:**
1. Verify basic salary entered correctly
2. Check allowances added properly
3. Recalculate:
   - NAPSA = Basic × 5% (max 2,301.60)
   - NHIMA = Gross × 1%
   - PAYE = Progressive tax on (Gross - NAPSA)
4. If still wrong, contact administrator
5. Review PayrollCalculationService settings

**Problem: Net Salary Incorrect**

**Formula to Verify:**
```
Basic Salary + Allowances = Gross Salary
Gross Salary - (NAPSA + PAYE + NHIMA + Other Deductions) = Net Salary
```

**Troubleshooting Steps:**
1. Check all allowances added
2. Check all deductions added
3. Verify calculations manually
4. Edit payroll to correct
5. If persistent, delete and recreate

**Problem: Payslip Not Showing**

**Troubleshooting Steps:**
1. Verify payroll record exists
2. Check pay period selected
3. Try different browser
4. Clear cache
5. Contact administrator

### 14.6 Bus Management Issues

**Problem: Student Can't See Bus Pass**

**Troubleshooting Steps:**
1. Verify payment status is "Paid" or "Partial"
2. Check if student logged into correct account
3. Verify bus payment exists for this student
4. Refresh student dashboard
5. Check "My Bus Passes" section

**Problem: Bus Pass Shows Expired**

**Cause:** Past the validity period

**Solution:**
- Monthly pass: Create new payment for current month
- Term pass: Verify due date, extend if needed
- Student must pay for new period

**Problem: QR Code Not Showing**

**Troubleshooting Steps:**
1. Check internet connection (QR generated online)
2. Try different browser
3. Refresh page
4. Use verification code instead
5. Contact administrator if persistent

**Problem: Logo Not Showing on Pass**

**Troubleshooting Steps:**
1. Verify logo.png exists in public folder
2. Check file name exactly: "logo.png" (lowercase)
3. Clear browser cache
4. Upload logo if missing
5. Refresh pass page

### 14.7 General System Issues

**Problem: Page Loading Slowly**

**Solutions:**
1. Check internet connection
2. Clear browser cache:
   - Chrome: Ctrl+Shift+Delete
   - Firefox: Ctrl+Shift+Delete
3. Close unnecessary tabs
4. Try different browser
5. Contact administrator if slow for all users

**Problem: Changes Not Saving**

**Troubleshooting Steps:**
1. Check for error messages (red notifications)
2. Verify all required fields filled
3. Check internet connection
4. Try saving again
5. Copy data, logout, login, try again
6. Contact administrator

**Problem: Export Not Working**

**Troubleshooting Steps:**
1. Check pop-up blocker (disable for this site)
2. Try different browser
3. Check download folder
4. Verify you have data to export (not empty list)
5. Contact administrator

**Problem: SMS/Email Not Sending**

**Troubleshooting Steps:**
1. Verify recipient contact information correct
2. Check SMS Logs for delivery status
3. For email: Check spam folder
4. Verify SMS/email service is active
5. Contact administrator to check service status

### 14.8 When to Contact Technical Support

**Contact Support If:**

- Database errors appear
- System completely down
- Calculations consistently wrong
- Data lost or corrupted
- Security breach suspected
- Multiple users affected
- Can't access admin panel
- Payment gateway not working
- SMS service not working

**Information to Provide:**

1. **Your Details:**
   - Name
   - Role
   - Username (not password!)

2. **Problem Details:**
   - What were you trying to do?
   - What happened instead?
   - Error message (screenshot)
   - Time problem occurred
   - Browser used
   - Device used

3. **Steps to Reproduce:**
   - Step 1: I clicked...
   - Step 2: Then I...
   - Step 3: Error appeared...

**Response Times:**

- Critical (system down): Within 2 hours
- High (feature broken): Within 24 hours
- Medium (minor issue): Within 3 days
- Low (question/request): Within 1 week

---

## 15. Quick Reference Guide

### 15.1 Common Tasks Quick Guide

**Daily Tasks:**

| Task | Navigate To | Action |
|------|------------|---------|
| Record payment | Finance → Payment Transactions | New Payment |
| Lend book | Library → Book Loans | New Loan |
| Return book | Library → Book Loans | Return Book |
| Check SMS delivery | Communication → SMS Logs | View logs |

**Weekly Tasks:**

| Task | Navigate To | Action |
|------|------------|---------|
| Follow up overdue fees | Finance → Student Fees | Filter: Status=Unpaid |
| Check overdue books | Library → Book Loans | Filter: Status=Overdue |
| Review homework submissions | Learning → Homework Submissions | Filter: Status=Submitted |
| Check failed SMS | Communication → SMS Logs | Filter: Status=Failed |

**Monthly Tasks:**

| Task | Navigate To | Action |
|------|------------|---------|
| Generate payroll | HR → Payroll | Generate Bulk Payroll |
| Financial report | Finance → Payment Transactions | Export monthly |
| Pay statutory contributions | HR → Payroll | Export payroll summary |

**Termly Tasks:**

| Task | Navigate To | Action |
|------|------------|---------|
| Update current term | Academic → Terms | Edit term, set "Is Current" |
| Check library clearance | Library → Student Clearance | Export clearance report |
| Review fee structures | Finance → Fee Structures | Update for next term |

### 15.2 Keyboard Shortcuts

**General Navigation:**

| Shortcut | Action |
|----------|--------|
| Ctrl + K | Quick search (if available) |
| Ctrl + / | Open command palette |
| Esc | Close modal/dialog |

**Data Entry:**

| Shortcut | Action |
|----------|--------|
| Tab | Next field |
| Shift + Tab | Previous field |
| Enter | Submit form (if focused on button) |
| Ctrl + S | Save (some forms) |

**Browser:**

| Shortcut | Action |
|----------|--------|
| Ctrl + R | Refresh page |
| F5 | Refresh page |
| Ctrl + P | Print |
| Ctrl + F | Find on page |

### 15.3 Important Phone Numbers

**System Support:**
- IT Support: [Your IT support number]
- Technical Issues: [Your technical support email]

**School Administration:**
- Main Office: [School main number]
- Accountant: [Accountant number]
- Librarian: [Librarian number]

**External Services:**
- SMS Provider: [SMS service support]
- Payment Gateway: [Payment service support]

---

## 16. Training Checklist

### 16.1 Administrator Training Checklist

Use this checklist to verify administrator is fully trained:

**Week 1: System Basics**
- [ ] Can login and logout
- [ ] Can navigate main menu
- [ ] Can change own password
- [ ] Understands dashboard widgets
- [ ] Can use filters and search
- [ ] Can export data to Excel

**Week 2: User Management**
- [ ] Can create student account
- [ ] Can create parent account
- [ ] Can create teacher account
- [ ] Can link parent to student
- [ ] Can update user information
- [ ] Can deactivate user account
- [ ] Understands role permissions

**Week 3: Academic Setup**
- [ ] Can create academic year
- [ ] Can set up terms
- [ ] Can create grades
- [ ] Can create class sections
- [ ] Can create subjects
- [ ] Can link subjects to grades
- [ ] Can assign teachers to subjects

**Week 4: Financial Management**
- [ ] Can create fee structure
- [ ] Can assign fees to students
- [ ] Can record payments
- [ ] Can generate receipts
- [ ] Can print fee statements
- [ ] Can run financial reports
- [ ] Understands payment reconciliation

**Week 5: Advanced Features**
- [ ] Can create bus routes
- [ ] Can manage bus payments
- [ ] Can view bus passes
- [ ] Can generate payroll
- [ ] Can create payslips
- [ ] Can manage library books
- [ ] Can track book loans
- [ ] Can process returns and fines
- [ ] Can check student clearance

**Week 6: Communication & Reporting**
- [ ] Can create events
- [ ] Can view SMS logs
- [ ] Can generate all reports
- [ ] Can export data for analysis
- [ ] Can troubleshoot common issues

**Final Assessment:**
- [ ] Can perform all daily tasks independently
- [ ] Can train other users
- [ ] Understands when to escalate issues
- [ ] Knows where to find help

### 16.2 Training Resources

**Documentation:**
- This Administrator Guide
- Online Help (if available)
- Video Tutorials (if available)
- FAQ Document

**Practice Environment:**
- Use test accounts for training
- Create sample data
- Practice all tasks before live use

**Ongoing Learning:**
- Attend system updates training
- Review new features announcements
- Share best practices with team
- Document your own procedures

---

## 17. Appendices

### Appendix A: Glossary of Terms

**Academic Year**: The 12-month period for school operations (e.g., 2025)

**Term**: A subdivision of academic year (Term 1, 2, 3)

**Grade**: Year level of student (Grade 1-12)

**Class Section**: Subdivision of grade (Grade 8A, 8B)

**NAPSA**: National Pension Scheme Authority (5% pension contribution)

**NHIMA**: National Health Insurance Management Authority (1% health insurance)

**PAYE**: Pay As You Earn (income tax)

**QR Code**: Quick Response code for scanning

**Clearance**: Verification student has met all requirements

**Fee Structure**: Template for fees (amounts and categories)

**Student Fee**: Individual fee assigned to specific student

**Payment Transaction**: Actual payment recorded

**Statutory Deduction**: Legally required deduction (NAPSA, PAYE, NHIMA)

### Appendix B: System Roles & Permissions Summary

| Role | Role ID | Can Access |
|------|---------|-----------|
| Admin | 1 | Everything |
| Teacher | 2 | Homework, Results, Assigned Students |
| Student | 3 | Own Data (homework, results, fees, bus passes) |
| Parent | 4 | Children's Data |
| Accountant | 5 | Fees, Payments, Financial Reports |
| Nurse | 6 | Health Records |
| Librarian | 7 | Library, Books, Loans, Clearance |
| Security | 8 | Access Logs |
| Support | 9 | Limited Access |

### Appendix C: Important Calculations

**NAPSA Calculation:**
```
NAPSA = Basic Salary × 5%
If NAPSA > 2,301.60, then NAPSA = 2,301.60
```

**NHIMA Calculation:**
```
NHIMA = Gross Salary × 1%
```

**PAYE Calculation:**
```
Taxable Income = Gross Salary - NAPSA

Tax Bands:
0 - 4,800: 0%
4,801 - 6,900: 25%
6,901 - 11,600: 30%
11,601+: 37%
```

**Net Salary Calculation:**
```
Net = Gross - (NAPSA + PAYE + NHIMA + Other Deductions)
```

**Overdue Fine:**
```
Fine = Days Overdue × Daily Rate
Daily Rate = ZMW 5 (configurable)
```

### Appendix D: Support Contacts

**System Support:**
- Email: support@sfaschool.edu.zm
- Phone: [Your support number]
- Hours: Monday-Friday, 8 AM - 5 PM

**Technical Issues:**
- Report bugs: [GitHub/Issue tracker URL]
- Feature requests: [Request form URL]

**Documentation:**
- User Guide: [This document]
- Video Tutorials: [YouTube/Training URL]
- FAQ: [FAQ URL]

---

## Document Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | October 2025 | Initial comprehensive guide | Admin Team |

---

## Feedback & Improvements

This guide is continuously updated based on user feedback. If you have suggestions for improvements:

1. Email: admin@sfaschool.edu.zm
2. Subject: "Admin Guide Feedback"
3. Describe:
   - Section that needs clarification
   - Suggested improvements
   - Missing information

Thank you for using the St. Francis of Assisi School Management System!

---

**END OF ADMINISTRATOR GUIDE**
