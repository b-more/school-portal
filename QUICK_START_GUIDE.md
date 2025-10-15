# SFA School Management System
## Quick Start Guide for Administrators

**Version:** 1.0 | **Pages:** 10 | **Reading Time:** 20 minutes

---

## 🚀 Getting Started (5 Minutes)

### First Login
1. Open browser: `http://your-school-domain.com/admin`
2. Login: `admin@sfa.edu.zm` / `password`
3. **IMMEDIATELY change password**: Profile Icon → Change Password

### Dashboard Overview
- **Left Sidebar**: All modules (Users, Academic, Finance, etc.)
- **Main Area**: Statistics and widgets
- **Top Right**: Your profile and notifications

---

## 👥 User Management (15 Minutes)

### Add New Student
**User Management → Students → New Student**

| Field | Example | Required |
|-------|---------|----------|
| First Name | John | ✓ |
| Last Name | Mwale | ✓ |
| Date of Birth | 01/15/2010 | ✓ |
| Gender | Male | ✓ |
| Grade | Grade 8 | ✓ |
| Class Section | Grade 8A | ✓ |
| Parent | Select or create new | ✓ |

**Outcome**: Student account created, credentials sent via SMS

### Add New Teacher
**User Management → Employees → New Employee**

| Field | Example | Required |
|-------|---------|----------|
| First Name | Grace | ✓ |
| Last Name | Banda | ✓ |
| Position | Teacher | ✓ |
| Role | Teacher (role_id=2) | ✓ |
| Email | grace@sfa.edu.zm | ✓ |
| Phone | +260977123456 | ✓ |
| Basic Salary | 8000.00 | ✓ |

**Outcome**: Teacher account created, credentials sent via EMAIL + SMS

### Add Parent/Guardian
**User Management → Parents/Guardians → New Parent Guardian**

| Field | Example | Required |
|-------|---------|----------|
| First Name | Mary | ✓ |
| Last Name | Mwale | ✓ |
| Phone | +260977654321 | ✓ |
| Relationship | Mother | ✓ |

**Link to Student**: Edit student → Select parent from dropdown

---

## 📚 Academic Setup (20 Minutes)

### 1. Create Academic Year
**Academic Management → Academic Years → New Academic Year**
- Year: 2025
- Start Date: 01/01/2025
- End Date: 12/31/2025
- Is Current: ✓ ON

### 2. Create Terms
**Academic Management → Terms → New Term**

| Term | Start Date | End Date | Is Current |
|------|-----------|----------|------------|
| Term 1 | 01/15/2025 | 04/30/2025 | ✓ ON |
| Term 2 | 05/01/2025 | 08/31/2025 | OFF |
| Term 3 | 09/01/2025 | 12/15/2025 | OFF |

### 3. Create Grades & Class Sections
**Academic Management → Grades → New Grade**
- Example: Grade 8, Level 8, Section: Junior Secondary

**Academic Management → Class Sections → New Class Section**
- Grade: Grade 8
- Section Name: A
- Class Teacher: Select teacher
- Result: "Grade 8A"

### 4. Create Subjects
**Academic Management → Subjects → New Subject**

Common subjects: Mathematics, English, Science, Social Studies, RE, PE

### 5. Link Subjects to Grades
**Academic Management → Grades → Edit Grade 8 → Subjects tab**
- Attach all applicable subjects

### 6. Assign Teachers
**Academic Management → Teacher Assignments → New Assignment**
- Teacher: Mr. Banda
- Subject: Mathematics
- Grade: Grade 8
- Class Section: Grade 8A

---

## ✍️ Homework Management (10 Minutes)

### Create Homework
**Learning & Assessment → Homework → New Homework**

| Field | Example |
|-------|---------|
| Title | Chapter 5 Algebra Exercises |
| Subject | Mathematics |
| Grade | Grade 8 |
| Class Section | Grade 8A (or blank for all) |
| Due Date | 3 days from now |
| Max Score | 20 |
| Status | Active |
| Send SMS | ✓ ON (notifies parents) |

**Outcome**: Homework visible to students, SMS sent to parents

### Grade Homework
**Learning & Assessment → Homework Submissions**
- Filter: Status = "Submitted"
- Click **Grade** → Enter marks & feedback → Save

---

## 💰 Fee Management (20 Minutes)

### 1. Create Fee Structure
**Finance Management → Fee Structures → New Fee Structure**

| Field | Example |
|-------|---------|
| Name | Grade 8 Annual Tuition 2025 |
| Fee Type | Tuition |
| Amount | 5000.00 |
| Grade | Grade 8 |
| Is Active | ✓ ON |

### 2. Assign Fees to Students
**Option A - Bulk Assignment**:
- Fee Structures → Find fee → Click "Bulk Assign"
- Select Grade → Assign to All Students

**Option B - Individual**:
- Finance → Student Fees → New Student Fee
- Select student, fee structure, due date → Create

### 3. Record Payment
**Finance Management → Payment Transactions → New Payment Transaction**

| Field | Example |
|-------|---------|
| Student | John Mwale |
| Student Fee | Grade 8 Annual Tuition |
| Amount | 2000.00 |
| Payment Method | Mobile Money |
| Transaction Reference | AIRTEL-1234567890 |

**Outcome**: Balance auto-updated, receipt generated

### 4. View/Print Receipt
- Payment Transactions → Click "View Receipt"
- Click Print button
- Or Email Receipt to parent

---

## 🚌 Bus Management (15 Minutes)

### 1. Create Bus Route
**Finance Management → Bus Fare Structures → New Bus Fare Structure**

| Field | Example |
|-------|---------|
| Route Name | Chongwe Route |
| Payment Plan | Monthly |
| Monthly Amount | 200.00 |
| Is Active | ✓ ON |

**For Per-Term**: Select "Per Term", enter Term Amount

### 2. Enroll Student
**Finance Management → Bus Payments → New Bus Payment**

| Field | Example |
|-------|---------|
| Student | John Mwale |
| Bus Fare Structure | Chongwe Route |
| Year | 2025 |
| Month | January (if monthly) |
| Amount | 200.00 (auto-filled) |
| Amount Paid | 200.00 |
| Due Date | 01/31/2025 |

**Outcome**:
- Payment Status: Paid
- Bus pass available immediately
- Student can download pass with QR code

### 3. Student Access Bus Pass
**Student Dashboard → "My Bus Passes" section**
- Click "View Pass" → Opens pass with QR code
- Click "Receipt" → Opens payment receipt

---

## 💼 Payroll Management (15 Minutes)

### Create Monthly Payroll
**HR & Payroll → Payroll → New Payroll**

| Field | Example |
|-------|---------|
| Employee | Mr. John Banda |
| Basic Salary | 8000.00 (auto-filled) |
| Pay Period | 01/01/2025 - 01/31/2025 |
| Allowances | Housing: 2000, Transport: 500 |
| NAPSA | 400.00 (auto-calculated) |
| PAYE | 1485.00 (auto-calculated) |
| NHIMA | 105.00 (auto-calculated) |
| Other Deductions | Loan: 500 |
| Net Salary | 8010.00 (auto-calculated) |
| Payment Date | 01/31/2025 |
| Status | Pending |

**Outcome**: Payroll created, payslip ready

### View Payslip
- Payroll → Click "View Payslip"
- Print or download PDF

### Bulk Payroll
- Click "Generate Bulk Payroll"
- Select pay period → Generate
- Review each payroll, add allowances/deductions
- Mark as "Paid" when processed

---

## 📖 Library Management (15 Minutes)

### 1. Add Book
**Library Management → Books → New Book**

| Field | Example |
|-------|---------|
| Title | Things Fall Apart |
| Author | Chinua Achebe |
| Category | Literature |
| Total Copies | 25 |
| Available Copies | 25 |
| Shelf Location | L-01 |

### 2. Lend Book
**Library Management → Book Loans → New Book Loan**

| Field | Example |
|-------|---------|
| Student | John Mwale |
| Book | Things Fall Apart |
| Lent Date | Today |
| Due Date | 14 days from today |
| Condition on Loan | Good |

**Outcome**: Available copies decreased by 1

### 3. Return Book
**Library Management → Book Loans → Click "Return Book"**

| Field | Example |
|-------|---------|
| Return Date | Today |
| Condition on Return | Good |
| Fine Amount | 15.00 (if 3 days late) |

**Outcome**:
- Available copies increased by 1
- Fine recorded if overdue/damaged

### 4. Check Student Clearance
**Library Management → Student Clearance**
- View all students
- Filter "Not Cleared" to see who has issues
- Follow up on unreturned books and unpaid fines

---

## 📊 Reports (10 Minutes)

### Financial Reports

**Daily Collections**:
- Payment Transactions → Filter: Today → Export

**Outstanding Fees**:
- Student Fees → Filter: Status = Unpaid → Export

**Monthly Summary**:
- Payment Transactions → Filter: This Month → Export

### Academic Reports

**Homework Completion**:
- Homework Submissions → Filter by Grade/Subject → Export

**Student Performance**:
- Results → Filter by Term/Grade → Export

### Library Reports

**Overdue Books**:
- Book Loans → Filter: Status = Overdue → Export

**Unpaid Fines**:
- Book Loans → Filter: Fine Status = Unpaid → Export

---

## 🔧 Quick Troubleshooting

### Payment Not Showing
1. Refresh page
2. Clear browser cache (Ctrl+Shift+Delete)
3. Check filters
4. Verify payment was saved (look for success message)

### Student Can't Login
1. Verify credentials sent (check SMS logs)
2. Resend credentials: Edit student → Resend Credentials
3. Check phone number format: +260XXXXXXXXX
4. Reset password manually

### Teacher Can't Create Homework
1. Verify role is "Teacher" (role_id = 2)
2. Check teacher assignments: Must be assigned to subject/grade
3. Verify subject linked to grade
4. Check current academic year is set

### Bus Pass Not Showing
1. Verify payment status is "Paid" or "Partial"
2. Check student logged into correct account
3. Refresh student dashboard
4. Verify bus payment exists for student

### Logo Not Showing on Pass/Receipt
1. Verify logo.png exists in `public/` folder
2. File name must be exactly "logo.png" (lowercase)
3. Clear browser cache
4. Refresh page

---

## ✅ Daily Checklist

**Every Morning:**
- [ ] Check dashboard for pending tasks
- [ ] Review failed SMS (Communication → SMS Logs)
- [ ] Check overdue homework submissions
- [ ] Review overdue library books

**Every Afternoon:**
- [ ] Record all payments received
- [ ] Issue receipts to parents
- [ ] Grade submitted homework
- [ ] Process library returns

**Before Leaving:**
- [ ] Reconcile daily collections
- [ ] Backup important data
- [ ] Respond to pending parent queries
- [ ] Update task list for tomorrow

---

## 🆘 Need Help?

### Common Tasks Quick Access

| Task | Navigate To |
|------|------------|
| Add Student | User Management → Students → New |
| Record Payment | Finance → Payment Transactions → New |
| Create Homework | Learning → Homework → New |
| Lend Book | Library → Book Loans → New |
| Generate Payroll | HR → Payroll → New |

### Support Contacts
- **IT Support**: support@sfaschool.edu.zm
- **Full Guide**: ADMINISTRATOR_GUIDE.md (600+ pages)
- **Training**: 6-week structured program available

---

## 🎯 Next Steps

1. **Complete Initial Setup** (1-2 days):
   - Academic year, terms, grades
   - Class sections, subjects
   - Initial users (teachers, students)

2. **Start Operations** (Week 1):
   - Assign fees to students
   - Create first homework
   - Add library books
   - Generate first payroll

3. **Monitor & Improve** (Ongoing):
   - Track fee collection rates
   - Monitor homework completion
   - Review library usage
   - Generate monthly reports

4. **Advanced Training**:
   - Read full ADMINISTRATOR_GUIDE.md
   - Complete assessment tests
   - Practice on test data
   - Shadow experienced admin

---

**Remember**:
- Save often
- Verify before bulk actions
- Keep regular backups
- Ask for help when uncertain

**Good luck! You've got this! 🎉**
