# SFA School Management System
## Training Assessment Tests

**Purpose**: Evaluate administrator training progress and competency
**Format**: Practical exercises + Multiple choice questions
**Passing Score**: 80% or higher
**Time Allowed**: As indicated per section

---

## Table of Contents

1. [Week 1 Assessment: System Basics](#week-1-assessment-system-basics)
2. [Week 2 Assessment: User Management](#week-2-assessment-user-management)
3. [Week 3 Assessment: Academic Setup](#week-3-assessment-academic-setup)
4. [Week 4 Assessment: Financial Management](#week-4-assessment-financial-management)
5. [Week 5 Assessment: Advanced Features](#week-5-assessment-advanced-features)
6. [Week 6 Assessment: Operations & Troubleshooting](#week-6-assessment-operations--troubleshooting)
7. [Final Comprehensive Assessment](#final-comprehensive-assessment)
8. [Answer Keys](#answer-keys)

---

## Week 1 Assessment: System Basics

**Time Allowed**: 30 minutes
**Total Points**: 20 points
**Passing Score**: 16 points (80%)

### Part A: Multiple Choice (10 points, 1 point each)

1. What is the default admin login URL?
   - a) http://school.com/login
   - b) http://school.com/admin
   - c) http://school.com/admin/login
   - d) http://school.com/dashboard

2. How should you handle the default admin password?
   - a) Keep it for convenience
   - b) Share it with all staff
   - c) Change it immediately
   - d) Write it down

3. Where do you find the main navigation menu?
   - a) Top of the page
   - b) Right sidebar
   - c) Left sidebar
   - d) Bottom footer

4. What does the dashboard show?
   - a) Only student information
   - b) System statistics and widgets
   - c) Only financial data
   - d) Teacher schedules

5. How do you change your password?
   - a) Settings → Security
   - b) Profile Icon → Change Password
   - c) Dashboard → Account
   - d) Cannot change password

6. What does role_id = 1 represent?
   - a) Teacher
   - b) Student
   - c) Admin
   - d) Parent

7. How often should you backup the database?
   - a) Yearly
   - b) Monthly
   - c) Weekly
   - d) Daily

8. What is the recommended browser cache clearing shortcut?
   - a) Ctrl + C
   - b) Ctrl + Shift + Delete
   - c) Alt + F4
   - d) F5

9. Where can you export data to Excel?
   - a) Dashboard only
   - b) Most list views have Export button
   - c) Settings page
   - d) Cannot export

10. What should you do if you forget your password?
    - a) Create new account
    - b) Click "Forgot Password" on login
    - c) Call police
    - d) Reinstall system

### Part B: Practical Exercise (10 points)

**Instructions**: Perform the following tasks in the system:

1. **Login & Password Change** (3 points)
   - Login with your credentials
   - Navigate to profile settings
   - Change your password
   - Logout and login with new password

2. **Dashboard Navigation** (3 points)
   - Identify and write down 5 widgets visible on dashboard
   - Navigate to 3 different modules from sidebar
   - Return to dashboard

3. **Data Export** (4 points)
   - Navigate to Students list
   - Apply a filter (e.g., Grade 8)
   - Export data to Excel
   - Open file and verify data

---

## Week 2 Assessment: User Management

**Time Allowed**: 45 minutes
**Total Points**: 30 points
**Passing Score**: 24 points (80%)

### Part A: Multiple Choice (10 points, 1 point each)

1. What is auto-generated when creating a student?
   - a) Student photo
   - b) Student ID number
   - c) Grade assignment
   - d) Parent account

2. How are student credentials sent to parents?
   - a) Email only
   - b) SMS only
   - c) SMS (and email if available)
   - d) Printed letter

3. What is the correct phone number format?
   - a) 0977123456
   - b) +260977123456
   - c) 260977123456
   - d) 977123456

4. What role_id should a teacher have?
   - a) 1
   - b) 2
   - c) 3
   - d) 5

5. How are teacher credentials sent?
   - a) SMS only
   - b) Email only
   - c) Both EMAIL and SMS
   - d) Not sent automatically

6. What must you do before linking a parent to a student?
   - a) Nothing, can do directly
   - b) Create parent account first
   - c) Create student first
   - d) Get permission from principal

7. What happens when you create an Accountant account?
   - a) Full system access like Admin
   - b) Only financial module access
   - c) Only student data access
   - d) No access at all

8. What is role_id = 7?
   - a) Teacher
   - b) Accountant
   - c) Librarian
   - d) Parent

9. Can one parent be linked to multiple students?
   - a) Yes
   - b) No
   - c) Only if they're twins
   - d) Only same grade

10. What should you do with accounts of departed staff?
    - a) Leave them active
    - b) Deactivate immediately
    - c) Delete after 1 year
    - d) Share with other staff

### Part B: Practical Exercise (20 points)

**Instructions**: Complete the following user creation tasks:

1. **Create a Student** (6 points)
   - First Name: Test
   - Last Name: Student
   - Grade: Grade 8
   - Class Section: Grade 8A
   - Parent: Create new parent
   - Verify student ID generated
   - Check SMS logs for credential delivery

2. **Create a Parent** (4 points)
   - First Name: Test
   - Last Name: Parent
   - Phone: +260977000001
   - Link to the student created above

3. **Create a Teacher** (6 points)
   - First Name: Test
   - Last Name: Teacher
   - Role: Teacher (role_id = 2)
   - Email: test.teacher@sfa.edu.zm
   - Phone: +260977000002
   - Basic Salary: 5000
   - Verify credentials sent via EMAIL + SMS

4. **Create a Librarian** (4 points)
   - First Name: Test
   - Last Name: Librarian
   - Role: Librarian (role_id = 7)
   - Verify they only see library modules

---

## Week 3 Assessment: Academic Setup

**Time Allowed**: 60 minutes
**Total Points**: 40 points
**Passing Score**: 32 points (80%)

### Part A: Multiple Choice (15 points, 1 point each)

1. Can you have multiple "current" academic years?
   - a) Yes, as many as needed
   - b) No, only ONE at a time
   - c) Yes, but maximum 2
   - d) No academic year needed

2. How many terms are typically in a year?
   - a) 1
   - b) 2
   - c) 3
   - d) 4

3. What happens when you mark a term as "Is Current"?
   - a) Previous current term auto-updates to not current
   - b) All data links to this term
   - c) Teachers can create homework for this term
   - d) All of the above

4. What is a "Class Section"?
   - a) A room in the school
   - b) Subdivision of a grade (e.g., 8A, 8B)
   - c) A teaching period
   - d) A subject category

5. Why link subjects to grades?
   - a) To look organized
   - b) So only relevant subjects appear for each grade
   - c) It's optional
   - d) No reason

6. What does "teacher assignment" mean?
   - a) Homework given to teachers
   - b) Linking teacher to subject/grade/class
   - c) Teacher's office location
   - d) Teacher's schedule

7. Can a teacher teach multiple subjects?
   - a) No, only one subject
   - b) Yes, create multiple assignments
   - c) Only if same grade
   - d) Only if same class

8. What happens if a subject is not linked to a grade?
   - a) It still appears everywhere
   - b) It won't appear when creating homework for that grade
   - c) System crashes
   - d) Nothing

9. What is "Grade Level" used for?
   - a) Student performance
   - b) Sorting grades (1, 2, 3...)
   - c) Teacher ranking
   - d) Fee calculation

10. Can you change the current term mid-term?
    - a) No, locked once set
    - b) Yes, edit term and toggle "Is Current"
    - c) Only admin can
    - d) Requires system restart

11. What is required before creating class sections?
    - a) Students
    - b) Teachers
    - c) Grades
    - d) Homework

12. What identifies a class section?
    - a) Room number only
    - b) Grade + Section Name (e.g., Grade 8 + A = Grade 8A)
    - c) Teacher name
    - d) Student count

13. Can one teacher be class teacher for multiple sections?
    - a) Yes
    - b) No
    - c) Only if same grade
    - d) Only if different grades

14. What happens if you don't assign a teacher to a subject?
    - a) System auto-assigns
    - b) Teacher can't create homework for that subject
    - c) All teachers can teach it
    - d) Subject is deleted

15. What is the purpose of academic year start/end dates?
    - a) Decoration only
    - b) Defines period for all academic operations
    - c) Optional information
    - d) For calendar display

### Part B: Practical Exercise (25 points)

**Instructions**: Complete the full academic setup:

1. **Create Academic Year** (3 points)
   - Year: 2025
   - Start Date: Jan 1, 2025
   - End Date: Dec 31, 2025
   - Is Current: ON

2. **Create 3 Terms** (6 points, 2 each)
   - Term 1: Jan 15 - Apr 30 (Is Current: ON)
   - Term 2: May 1 - Aug 31 (Is Current: OFF)
   - Term 3: Sep 1 - Dec 15 (Is Current: OFF)

3. **Create 2 Grades** (4 points, 2 each)
   - Grade 7 (Level: 7, Section: Primary)
   - Grade 8 (Level: 8, Section: Junior Secondary)

4. **Create 2 Class Sections for Grade 8** (4 points, 2 each)
   - Grade 8A (Select a teacher)
   - Grade 8B (Select a teacher)

5. **Create 3 Subjects** (3 points, 1 each)
   - Mathematics
   - English
   - Science

6. **Link Subjects to Grade 8** (3 points)
   - Attach all 3 subjects to Grade 8

7. **Assign Teacher to Subject** (2 points)
   - Assign one teacher to Mathematics for Grade 8A

---

## Week 4 Assessment: Financial Management

**Time Allowed**: 60 minutes
**Total Points**: 40 points
**Passing Score**: 32 points (80%)

### Part A: Multiple Choice (15 points, 1 point each)

1. What is a "Fee Structure"?
   - a) An individual student's fee
   - b) A template for fees (amounts and types)
   - c) A payment record
   - d) A receipt

2. What is a "Student Fee"?
   - a) A template
   - b) Individual fee assigned to specific student
   - c) A payment
   - d) A balance

3. What is a "Payment Transaction"?
   - a) A fee structure
   - b) An assigned fee
   - c) Actual payment recorded
   - d) A balance

4. How is balance calculated?
   - a) Total Fee - Amount Paid
   - b) Amount Paid - Total Fee
   - c) Total Fee + Amount Paid
   - d) Manually entered

5. What payment statuses exist?
   - a) Paid only
   - b) Unpaid, Partial, Paid
   - c) Pending, Completed
   - d) Active, Inactive

6. When does status change from "Unpaid" to "Partial"?
   - a) Never
   - b) When amount paid > 0 but < total amount
   - c) When amount paid = total
   - d) Admin manually changes

7. What is auto-generated when recording payment?
   - a) New fee
   - b) Receipt number and receipt
   - c) Student account
   - d) Nothing

8. What payment methods are supported?
   - a) Cash only
   - b) Cash, Bank, Mobile Money, Cheque
   - c) Credit card only
   - d) Cash and bank only

9. Can you record partial payments?
   - a) No, must pay full amount
   - b) Yes, enter amount less than total
   - c) Only for bus fares
   - d) Only with admin approval

10. What is a fee statement?
    - a) A receipt
    - b) Summary of all fees and payments for a student
    - c) A payment method
    - d) A fee structure

11. How do you bulk assign fees to all Grade 8 students?
    - a) Create manually for each student
    - b) Fee Structures → Bulk Assign → Select Grade 8
    - c) Cannot bulk assign
    - d) Import from Excel

12. What appears on a payment receipt?
    - a) Only amount paid
    - b) Student info, fee details, amount, receipt number
    - c) Only receipt number
    - d) Only student name

13. Can parents view receipts?
    - a) No, admin only
    - b) Yes, from their dashboard
    - c) Only if emailed
    - d) Only printed copies

14. What is "Due Date" for a fee?
    - a) When fee was created
    - b) Deadline for payment
    - c) When payment was made
    - d) End of term

15. How do you reconcile daily collections?
    - a) Count cash and compare to system report
    - b) Not necessary
    - c) Only monthly
    - d) Automatic

### Part B: Practical Exercise (25 points)

**Instructions**: Complete financial management tasks:

1. **Create Fee Structure** (4 points)
   - Name: Test Grade 8 Tuition
   - Fee Type: Tuition
   - Amount: 3000.00
   - Grade: Grade 8
   - Is Active: ON

2. **Assign Fee to Student** (4 points)
   - Use student created in Week 2
   - Select fee structure above
   - Due Date: 30 days from today

3. **Record Full Payment** (5 points)
   - Student: Same student
   - Student Fee: Test Grade 8 Tuition
   - Amount: 3000.00
   - Payment Method: Mobile Money
   - Transaction Reference: TEST123456

4. **Verify Payment** (4 points)
   - Check payment status changed to "Paid"
   - Check balance is 0
   - Verify receipt generated

5. **Print Receipt** (3 points)
   - View receipt
   - Verify all details correct
   - Print or save as PDF

6. **Generate Fee Report** (5 points)
   - Navigate to Student Fees
   - Filter by Grade 8
   - Export to Excel
   - Verify student appears in report

---

## Week 5 Assessment: Advanced Features

**Time Allowed**: 90 minutes
**Total Points**: 50 points
**Passing Score**: 40 points (80%)

### Part A: Multiple Choice (20 points, 1 point each)

**Bus Management**

1. What are the two bus payment plans?
   - a) Daily and Weekly
   - b) Monthly and Per Term
   - c) Annual and Semester
   - d) Flexible and Fixed

2. What is required for bus pass to be available to student?
   - a) Payment status must be Unpaid
   - b) Payment status must be Paid or Partial
   - c) Admin approval
   - d) Parent consent

3. What does the bus pass QR code contain?
   - a) Student photo
   - b) BUS-PASS-{payment_id}-{student_id}
   - c) Student address
   - d) Payment amount

4. Where should the school logo be placed?
   - a) public/images/logo.png
   - b) public/logo.png
   - c) storage/logo.png
   - d) resources/logo.png

5. When does a monthly bus pass expire?
   - a) After 30 days
   - b) Last day of the month
   - c) When student wants
   - d) Never expires

**Payroll Management**

6. What is NAPSA rate?
   - a) 1%
   - b) 3%
   - c) 5%
   - d) 10%

7. What is NAPSA calculated on?
   - a) Gross salary
   - b) Basic salary
   - c) Net salary
   - d) Allowances only

8. What is NAPSA capped at?
   - a) ZMW 1,000
   - b) ZMW 2,301.60
   - c) ZMW 5,000
   - d) No cap

9. What is NHIMA rate?
   - a) 1% of gross
   - b) 2% of basic
   - c) 3% of net
   - d) 5% of gross

10. What is PAYE calculated on?
    - a) Gross salary
    - b) Basic salary
    - c) Taxable income (Gross - NAPSA)
    - d) Net salary

11. What are the PAYE tax rates?
    - a) Flat 25%
    - b) 0%, 25%, 30%, 37% (progressive)
    - c) Flat 30%
    - d) 10%, 20%, 30%

12. What is Net Salary?
    - a) Basic + Allowances
    - b) Gross - Deductions
    - c) Basic - NAPSA
    - d) Gross - PAYE

13. What appears on a payslip?
    - a) Only net salary
    - b) Earnings, deductions, net salary
    - c) Only basic salary
    - d) Only statutory deductions

14. How often is payroll typically generated?
    - a) Weekly
    - b) Monthly
    - c) Quarterly
    - d) Annually

15. Can you edit payroll after marking as "Paid"?
    - a) Yes, freely
    - b) Difficult, should recreate if error
    - c) No, locked forever
    - d) Only admin can

**Library Management**

16. What happens to available copies when book is loaned?
    - a) No change
    - b) Decreased by 1
    - c) Increased by 1
    - d) Reset to 0

17. When is a library fine issued?
    - a) When book is borrowed
    - b) When book is returned late or damaged
    - c) Every month
    - d) Never

18. What is the typical overdue fine rate?
    - a) ZMW 1 per day
    - b) ZMW 5 per day
    - c) ZMW 10 per day
    - d) ZMW 20 per day

19. What does "Student Clearance" verify?
    - a) Student grades only
    - b) All books returned and fines paid
    - c) Fee payment only
    - d) Attendance record

20. When should student clearance be checked?
    - a) Never
    - b) End of term, end of year, graduation, transfer
    - c) Only at graduation
    - d) Only when student asks

### Part B: Practical Exercise (30 points)

**Part B1: Bus Management** (10 points)

1. **Create Bus Route** (3 points)
   - Route Name: Test Route
   - Payment Plan: Monthly
   - Monthly Amount: 150.00
   - Is Active: ON

2. **Enroll Student** (4 points)
   - Student: Use existing student
   - Route: Test Route
   - Month: Current month
   - Amount Paid: 150.00

3. **Verify Bus Pass** (3 points)
   - Login as student (or view on their behalf)
   - Check "My Bus Passes" section appears
   - Verify bus pass is accessible
   - Check QR code displays

**Part B2: Payroll** (10 points)

1. **Create Payroll** (7 points)
   - Employee: Use existing teacher
   - Basic Salary: 8000.00
   - Allowances: Housing 2000, Transport 500
   - Verify NAPSA = 400.00
   - Verify NHIMA = 105.00
   - Verify PAYE calculates correctly
   - Status: Pending

2. **View Payslip** (3 points)
   - View payslip
   - Verify all calculations
   - Check statutory deductions highlighted

**Part B3: Library** (10 points)

1. **Add Book** (3 points)
   - Title: Test Book
   - Author: Test Author
   - Category: Fiction
   - Total Copies: 10
   - Available Copies: 10

2. **Lend Book** (3 points)
   - Student: Use existing student
   - Book: Test Book
   - Due Date: 14 days from today
   - Verify available copies = 9

3. **Return Book (Late)** (4 points)
   - Return the book
   - Return Date: 5 days after due date
   - Verify fine calculated (5 days × ZMW 5 = ZMW 25)
   - Verify available copies = 10

---

## Week 6 Assessment: Operations & Troubleshooting

**Time Allowed**: 60 minutes
**Total Points**: 40 points
**Passing Score**: 32 points (80%)

### Part A: Multiple Choice (15 points, 1 point each)

1. What should you do if a payment doesn't show up?
   - a) Record it again
   - b) Refresh page, check filters, verify saved
   - c) Ignore it
   - d) Call IT immediately

2. How do you resend student credentials?
   - a) Create new account
   - b) Edit student → Resend Credentials
   - c) Cannot resend
   - d) Send manually via WhatsApp

3. Why might a teacher not be able to create homework?
   - a) Internet is slow
   - b) Not assigned to subject/grade
   - c) Wrong browser
   - d) Account expired

4. What is the correct phone number format?
   - a) 0977123456
   - b) +260977123456
   - c) 977123456
   - d) 260-977-123456

5. Where do you check SMS delivery status?
   - a) Dashboard
   - b) Communication → SMS Logs
   - c) Settings
   - d) Cannot check

6. What should you do daily?
   - a) Nothing, system auto-manages
   - b) Record payments, check SMS logs, grade homework
   - c) Only backup
   - d) Only reconcile

7. How often should you backup the database?
   - a) Daily
   - b) Weekly
   - c) Monthly
   - d) Yearly

8. What do you do if student can't see bus pass?
   - a) Create new payment
   - b) Check payment status is Paid/Partial
   - c) Restart system
   - d) Ignore complaint

9. Where is the school logo stored for passes?
   - a) database
   - b) public/logo.png
   - c) storage/logo.png
   - d) resources/images/

10. How do you fix "Available Copies" mismatch?
    - a) Delete all loans
    - b) Verify all loans returned properly, admin recalculates
    - c) Ignore it
    - d) Buy new books

11. What keyboard shortcut clears browser cache?
    - a) F5
    - b) Ctrl + C
    - c) Ctrl + Shift + Delete
    - d) Alt + F4

12. When should you update "Is Current" term?
    - a) Never
    - b) At start of each new term
    - c) Only at year end
    - d) Monthly

13. What should you do with departed staff accounts?
    - a) Keep active
    - b) Deactivate immediately
    - c) Delete after 1 year
    - d) Share with new staff

14. How do you generate financial reports?
    - a) Cannot generate
    - b) Navigate to relevant module → Filter → Export
    - c) Dashboard only
    - d) Email request to IT

15. What is the first troubleshooting step for most issues?
    - a) Call IT
    - b) Restart computer
    - c) Refresh page, clear cache
    - d) Reinstall system

### Part B: Practical Troubleshooting Scenarios (25 points)

**Scenario 1: Missing Payment** (5 points)
You recorded a payment of ZMW 1,000 for student John Mwale, but it doesn't appear in the system.

**Tasks:**
1. List 4 troubleshooting steps you would take
2. Explain how to verify if payment was actually saved
3. Describe how to check if it's a filter issue

**Scenario 2: Teacher Can't Create Homework** (5 points)
Mrs. Banda says she cannot create homework for Grade 8 Mathematics.

**Tasks:**
1. List 3 things to check about her account
2. Explain what "teacher assignment" means
3. Describe how to fix this issue

**Scenario 3: Bus Pass Not Showing** (5 points)
A student says they paid for bus but can't see the pass on their dashboard.

**Tasks:**
1. List 3 things to verify about the payment
2. Explain where students should look for bus passes
3. Describe what payment statuses allow pass access

**Scenario 4: Library Clearance Issue** (5 points)
A Grade 12 student shows as "Not Cleared" but claims they returned all books.

**Tasks:**
1. List 3 things to check in the library system
2. Explain how to verify all books returned
3. Describe how to manually clear if verified

**Scenario 5: Payroll Calculation Wrong** (5 points)
An employee says their NAPSA deduction is incorrect.

**Tasks:**
1. State the NAPSA formula
2. Explain the cap amount
3. Show how to manually verify the calculation

---

## Final Comprehensive Assessment

**Time Allowed**: 120 minutes
**Total Points**: 100 points
**Passing Score**: 80 points (80%)

### Part A: Comprehensive Multiple Choice (30 points, 1 point each)

1. What is the first thing to do after first login?
2. How many current academic years can exist?
3. What phone number format is required?
4. What role_id is Admin?
5. How are teacher credentials sent?
6. What is a Class Section?
7. Why link subjects to grades?
8. What is Balance formula?
9. What payment statuses exist?
10. When is a receipt generated?
11. What are the two bus payment plans?
12. What is required for bus pass access?
13. What is NAPSA rate?
14. What is NAPSA calculated on?
15. What is NAPSA cap?
16. What is NHIMA rate?
17. What is PAYE calculated on?
18. What happens to available copies when book loaned?
19. When are library fines issued?
20. What does student clearance verify?
21. How often should database be backed up?
22. Where do you check SMS status?
23. What keyboard shortcut clears cache?
24. What should you do with departed staff accounts?
25. What is the correct logo location?
26. What is a Fee Structure?
27. What is a Payment Transaction?
28. Can partial payments be recorded?
29. Can one parent link to multiple students?
30. What is Net Salary formula?

### Part B: Comprehensive Practical Exercise (70 points)

**Complete End-to-End Scenario**: You are setting up the system for a new academic year.

**Task 1: Academic Setup** (15 points)
1. Create Academic Year 2026
2. Create 3 terms
3. Create Grade 9 and Grade 10
4. Create 2 class sections per grade
5. Create 5 subjects
6. Link subjects to both grades

**Task 2: User Management** (15 points)
1. Create 2 students (one Grade 9, one Grade 10)
2. Create 1 parent linked to both students
3. Create 1 teacher
4. Assign teacher to Mathematics for Grade 9

**Task 3: Financial Setup** (15 points)
1. Create fee structure for Grade 9 (ZMW 4000)
2. Create fee structure for Grade 10 (ZMW 4500)
3. Assign fees to both students
4. Record partial payment (ZMW 2000) for one student
5. Generate and print receipt

**Task 4: Homework Management** (10 points)
1. Create homework for Mathematics Grade 9
2. Enable SMS notification
3. View created homework as admin
4. Verify SMS sent (check logs)

**Task 5: Bus Management** (10 points)
1. Create bus route (Monthly, ZMW 200)
2. Enroll one student with full payment
3. Verify bus pass accessible
4. Print bus pass and receipt

**Task 6: Payroll** (10 points)
1. Create payroll for the teacher
2. Add Housing allowance ZMW 2000
3. Verify all statutory deductions calculate correctly
4. Print payslip

**Task 7: Library** (5 points)
1. Add 1 book
2. Lend to one student
3. Return book (on time, no fine)
4. Verify student clearance shows "Cleared"

**Task 8: Reporting** (5 points)
1. Generate fee collection report
2. Generate homework completion report
3. Export both to Excel

**Bonus Task: Troubleshooting** (5 bonus points)
1. Intentionally create an error (e.g., wrong phone format)
2. Identify the error
3. Fix the error
4. Document steps taken

---

## Answer Keys

### Week 1 Answers

**Part A:**
1. c) http://school.com/admin/login
2. c) Change it immediately
3. c) Left sidebar
4. b) System statistics and widgets
5. b) Profile Icon → Change Password
6. c) Admin
7. c) Weekly
8. b) Ctrl + Shift + Delete
9. b) Most list views have Export button
10. b) Click "Forgot Password" on login

**Part B:** Practical - verify with trainer

### Week 2 Answers

**Part A:**
1. b) Student ID number
2. c) SMS (and email if available)
3. b) +260977123456
4. b) 2
5. c) Both EMAIL and SMS
6. b) Create parent account first (or create during student creation)
7. b) Only financial module access
8. c) Librarian
9. a) Yes
10. b) Deactivate immediately

**Part B:** Practical - verify with trainer

### Week 3 Answers

**Part A:**
1. b) No, only ONE at a time
2. c) 3
3. d) All of the above
4. b) Subdivision of a grade (e.g., 8A, 8B)
5. b) So only relevant subjects appear for each grade
6. b) Linking teacher to subject/grade/class
7. b) Yes, create multiple assignments
8. b) It won't appear when creating homework for that grade
9. b) Sorting grades (1, 2, 3...)
10. b) Yes, edit term and toggle "Is Current"
11. c) Grades
12. b) Grade + Section Name
13. a) Yes
14. b) Teacher can't create homework for that subject
15. b) Defines period for all academic operations

**Part B:** Practical - verify with trainer

### Week 4 Answers

**Part A:**
1. b) A template for fees (amounts and types)
2. b) Individual fee assigned to specific student
3. c) Actual payment recorded
4. a) Total Fee - Amount Paid
5. b) Unpaid, Partial, Paid
6. b) When amount paid > 0 but < total amount
7. b) Receipt number and receipt
8. b) Cash, Bank, Mobile Money, Cheque
9. b) Yes, enter amount less than total
10. b) Summary of all fees and payments for a student
11. b) Fee Structures → Bulk Assign → Select Grade 8
12. b) Student info, fee details, amount, receipt number
13. b) Yes, from their dashboard
14. b) Deadline for payment
15. a) Count cash and compare to system report

**Part B:** Practical - verify with trainer

### Week 5 Answers

**Part A:**
1. b) Monthly and Per Term
2. b) Payment status must be Paid or Partial
3. b) BUS-PASS-{payment_id}-{student_id}
4. b) public/logo.png
5. b) Last day of the month
6. c) 5%
7. b) Basic salary
8. b) ZMW 2,301.60
9. a) 1% of gross
10. c) Taxable income (Gross - NAPSA)
11. b) 0%, 25%, 30%, 37% (progressive)
12. b) Gross - Deductions
13. b) Earnings, deductions, net salary
14. b) Monthly
15. b) Difficult, should recreate if error
16. b) Decreased by 1
17. b) When book is returned late or damaged
18. b) ZMW 5 per day
19. b) All books returned and fines paid
20. b) End of term, end of year, graduation, transfer

**Part B:** Practical - verify with trainer

### Week 6 Answers

**Part A:**
1. b) Refresh page, check filters, verify saved
2. b) Edit student → Resend Credentials
3. b) Not assigned to subject/grade
4. b) +260977123456
5. b) Communication → SMS Logs
6. b) Record payments, check SMS logs, grade homework
7. b) Weekly
8. b) Check payment status is Paid/Partial
9. b) public/logo.png
10. b) Verify all loans returned properly, admin recalculates
11. c) Ctrl + Shift + Delete
12. b) At start of each new term
13. b) Deactivate immediately
14. b) Navigate to relevant module → Filter → Export
15. c) Refresh page, clear cache

**Part B:** Practical scenarios - evaluate with trainer

### Final Assessment Answers

**Part A:** (Combines all previous answers - see above)

**Part B:** Comprehensive practical - full evaluation with trainer

---

## Scoring Guidelines

### Grade Scale
- **90-100%**: Excellent - Ready for independent work
- **80-89%**: Good - Ready with occasional supervision
- **70-79%**: Fair - Needs more practice
- **Below 70%**: Needs retraining

### Practical Exercise Evaluation Criteria
- **Accuracy**: Task completed correctly
- **Completeness**: All steps performed
- **Efficiency**: Completed in reasonable time
- **Understanding**: Can explain what was done

### Certification Requirements
To receive "SFA School Management System Administrator" certification:
- [ ] Pass all weekly assessments (80%+)
- [ ] Pass final comprehensive assessment (80%+)
- [ ] Complete all practical exercises
- [ ] Shadow live operations for 1 week
- [ ] Demonstrate troubleshooting skills

---

## Training Record Template

**Trainee Name**: ____________________
**Training Start Date**: ____________________
**Trainer Name**: ____________________

| Week | Assessment | Score | Pass/Fail | Date | Trainer Signature |
|------|-----------|-------|-----------|------|------------------|
| 1 | System Basics | __/20 | | | |
| 2 | User Management | __/30 | | | |
| 3 | Academic Setup | __/40 | | | |
| 4 | Financial Mgmt | __/40 | | | |
| 5 | Advanced Features | __/50 | | | |
| 6 | Operations | __/40 | | | |
| Final | Comprehensive | __/100 | | | |

**Overall Result**: _________________ (Pass/Fail)
**Certification Issued**: ☐ Yes ☐ No
**Certification Date**: ____________________
**Authorized By**: ____________________

---

**Good luck with your training! 🎓**
