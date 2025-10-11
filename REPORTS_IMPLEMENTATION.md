# Reports Implementation Guide

## Overview

This document outlines the comprehensive reporting system implemented for students, fees, and attendance tracking. The system provides powerful filtering, data visualization, and PDF export capabilities.

## Report Pages Created

### 1. Student Reports (`/admin/student-reports`)

**Location**: `app/Filament/Pages/StudentReports.php`

**Features**:
- Multiple report types:
  - All Students
  - By Class Section
  - By Grade
  - By Enrollment Status

**Filters**:
- Grade selection
- Class section selection
- Enrollment status (active, inactive, graduated, transferred, withdrawn)
- Gender filter
- Admission date range

**Columns Displayed**:
- Student ID Number
- Name
- Grade
- Class Section
- Gender
- Date of Birth
- Parent/Guardian Name & Contact
- Enrollment Status
- Admission Date

**Export Options**:
- Individual student PDF reports
- Bulk export selected students
- Export all filtered students as PDF

**Access**: Admin only

---

### 2. Fee Reports (`/admin/fee-reports`)

**Location**: `app/Filament/Pages/FeeReports.php`

**Features**:
- Multiple report types:
  - All Fees
  - By Class Section
  - By Grade
  - By Payment Status
  - Outstanding Balances Only

**Real-time Summary Stats**:
- Total Students with fees
- Total Fees amount
- Amount Paid
- Outstanding Balance

**Filters**:
- Academic Year
- Term
- Grade
- Class Section
- Payment Status (paid, partial, unpaid, overpaid)
- Payment Date Range
- Outstanding balances filter

**Columns Displayed**:
- Student ID & Name
- Grade
- Term
- Total Fee
- Amount Paid
- Balance (color-coded)
- Payment Status (badge)
- Last Payment Date
- Parent Contact

**Additional Features**:
- Payment history modal for each fee record
- View transaction timeline
- Track payment methods and references

**Export Options**:
- Individual fee statement PDF
- Bulk export selected fees
- Comprehensive fee summary PDF with statistics

**Access**: Admin only

---

### 3. Attendance Reports (`/admin/attendance-reports`)

**Location**: `app/Filament/Pages/AttendanceReports.php`

**Features**:
- Multiple report types:
  - All Attendance Records
  - By Class Section
  - By Grade
  - By Individual Student
  - By Status (present, absent, late, excused)
  - Attendance Summary

**Real-time Summary Stats**:
- Total Records
- Present Count (with attendance rate %)
- Absent Count
- Late Count
- Excused Count

**Filters**:
- Date range (from/to)
- Academic Year
- Term
- Grade
- Class Section
- Individual Student selection
- Attendance Status

**Columns Displayed**:
- Attendance Date
- Student ID & Name
- Grade
- Class Section
- Status (color-coded badge)
- Check-in Time
- Check-out Time
- Notes

**Additional Features**:
- Edit attendance inline
- Update status and add notes
- Track who marked attendance
- Time-based attendance tracking

**Export Options**:
- Bulk export selected attendance records
- Comprehensive attendance summary PDF with statistics

**Access**: Admin only

---

## Database Schema

### Attendance Table

**Migration**: `database/migrations/2025_10_10_130601_create_attendances_table.php`

**Columns**:
```php
- id (bigint, primary key)
- student_id (foreign key → students)
- class_section_id (foreign key → class_sections, nullable)
- grade_id (foreign key → grades, nullable)
- academic_year_id (foreign key → academic_years, nullable)
- term_id (foreign key → terms, nullable)
- attendance_date (date)
- status (enum: present, absent, late, excused)
- check_in_time (time, nullable)
- check_out_time (time, nullable)
- notes (text, nullable)
- marked_by (foreign key → users, nullable)
- created_at, updated_at (timestamps)
```

**Indexes** (for performance):
- student_id + attendance_date
- class_section_id + attendance_date
- grade_id + attendance_date
- academic_year_id + term_id
- status

**Constraints**:
- Unique constraint on student_id + attendance_date (prevents duplicate attendance for same day)

---

## Models Created/Updated

### 1. Attendance Model
**Location**: `app/Models/Attendance.php`

**Relationships**:
- `student()` → BelongsTo Student
- `classSection()` → BelongsTo ClassSection
- `grade()` → BelongsTo Grade
- `academicYear()` → BelongsTo AcademicYear
- `term()` → BelongsTo Term
- `markedBy()` → BelongsTo User

**Scopes**:
- `byStudent($studentId)`
- `byClassSection($classSectionId)`
- `byGrade($gradeId)`
- `byDateRange($startDate, $endDate)`
- `byStatus($status)`
- `byAcademicYear($academicYearId)`
- `byTerm($termId)`

**Helper Methods**:
- `wasPresent()` → boolean
- `wasAbsent()` → boolean
- `wasLate()` → boolean
- `getFormattedStatusAttribute()` → string

### 2. Student Model (Updated)
**Location**: `app/Models/Student.php`

**Added Relationship**:
```php
public function attendances(): HasMany
{
    return $this->hasMany(Attendance::class);
}
```

---

## Views Created

### 1. Student Reports View
**Location**: `resources/views/filament/pages/student-reports.blade.php`

### 2. Fee Reports View
**Location**: `resources/views/filament/pages/fee-reports.blade.php`
- Includes real-time statistics dashboard
- Summary cards for total fees, paid amounts, and balances

### 3. Attendance Reports View
**Location**: `resources/views/filament/pages/attendance-reports.blade.php`
- Includes real-time attendance statistics
- Visual breakdown of attendance status

### 4. Payment History Component
**Location**: `resources/views/filament/components/payment-history.blade.php`
- Displays transaction timeline
- Shows payment methods and references
- Calculates running balances

---

## PDF Templates Required

**Note**: The following PDF templates are referenced but need to be created:

### Student Reports:
1. `resources/views/pdf/student-report.blade.php` - Individual student details
2. `resources/views/pdf/students-list.blade.php` - List of multiple students

### Fee Reports:
3. `resources/views/pdf/fee-report.blade.php` - Individual fee statement
4. `resources/views/pdf/fees-list.blade.php` - List of fees
5. `resources/views/pdf/fee-summary.blade.php` - Fee summary with statistics

### Attendance Reports:
6. `resources/views/pdf/attendance-list.blade.php` - Attendance records list
7. `resources/views/pdf/attendance-summary.blade.php` - Attendance summary with statistics

### PDF Template Variables

Each template receives the following variables:

**Student Report**:
- `$student` (with relationships loaded)
- `$schoolName`
- `$reportDate`

**Fee Report**:
- `$fee` or `$fees`
- `$summary` (totals and counts)
- `$schoolName`
- `$reportDate`
- `$reportType`
- `$filters`

**Attendance Report**:
- `$attendances`
- `$summary` (statistics)
- `$schoolName`
- `$reportDate`
- `$dateFrom`, `$dateTo`
- `$filters`

---

## Navigation

All report pages are grouped under **"Reports"** in the Filament navigation menu:

1. **Student Reports** (Sort: 1)
2. **Fee Reports** (Sort: 2)
3. **Attendance Reports** (Sort: 3)

---

## Permission & Access Control

All reports are **Admin-only** and use:
```php
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
}
```

To extend access to other roles, modify the `shouldRegisterNavigation()` method in each report page.

---

## Key Features

### Performance Optimizations
- Eager loading of relationships to prevent N+1 queries
- Database indexes on attendance table for fast queries
- Efficient query scopes for filtering
- Paginated results (10, 25, 50, 100 per page)

### User Experience
- Real-time filter updates with Livewire
- Summary statistics visible without opening reports
- Color-coded status badges
- Bulk operations support
- Export functionality for archiving and sharing

### Data Integrity
- Unique constraint prevents duplicate attendance records
- Proper foreign key relationships
- Cascade deletes where appropriate
- Nullable fields for flexibility

---

## Usage Instructions

### 1. Accessing Reports
Navigate to **Reports** → **[Report Type]** in the admin panel.

### 2. Applying Filters
1. Select report type from dropdown
2. Choose date ranges, academic year, term as needed
3. Apply additional filters (grade, class, student, status)
4. Table updates automatically

### 3. Exporting Reports
- **Individual**: Click "PDF" button on any row
- **Selected**: Select multiple rows → "Export Selected as PDF"
- **All Filtered**: Click "Export All as PDF" or "Export Summary PDF" button

### 4. Viewing Payment History (Fee Reports)
Click "History" button on any fee record to view:
- All payment transactions
- Payment methods and dates
- Transaction references
- Running balance calculation

### 5. Editing Attendance
Click "Edit" button on attendance record to update:
- Status (present/absent/late/excused)
- Attendance date
- Notes

---

## Testing

### Test the Reports:

1. **Student Reports**:
```bash
# Ensure students exist with various statuses
php artisan db:seed --class=StudentSeeder
```

2. **Fee Reports**:
```bash
# Ensure fee structures and student fees exist
php artisan db:seed --class=FeeStructureSeeder
```

3. **Attendance Reports**:
```php
# Create sample attendance records
Attendance::factory(100)->create();
```

Or use Tinker:
```bash
php artisan tinker
>>> \App\Models\Attendance::factory(50)->create()
```

### Sample Attendance Creation (Tinker):
```php
$students = \App\Models\Student::where('enrollment_status', 'active')->get();
$currentTerm = \App\Models\Term::where('is_active', true)->first();
$academicYear = \App\Models\AcademicYear::where('is_active', true)->first();

foreach ($students as $student) {
    for ($i = 0; $i < 20; $i++) {
        \App\Models\Attendance::create([
            'student_id' => $student->id,
            'class_section_id' => $student->class_section_id,
            'grade_id' => $student->grade_id,
            'academic_year_id' => $academicYear->id,
            'term_id' => $currentTerm->id,
            'attendance_date' => now()->subDays(rand(1, 30)),
            'status' => collect(['present', 'absent', 'late', 'excused'])->random(),
            'check_in_time' => now()->setTime(rand(7, 9), rand(0, 59)),
            'check_out_time' => now()->setTime(rand(14, 16), rand(0, 59)),
            'marked_by' => 1, // Admin user
        ]);
    }
}
```

---

## Next Steps

### 1. Create PDF Templates
Create the 7 PDF template files listed above in `resources/views/pdf/`.

**Sample PDF Template Structure**:
```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $schoolName }} - Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Report Title</h2>
        <p>Date: {{ $reportDate }}</p>
    </div>
    <!-- Content here -->
</body>
</html>
```

### 2. Add Attendance Management Resource
Create a full Filament Resource for managing attendance:
```bash
php artisan make:filament-resource Attendance
```

### 3. Create Attendance Recording Interface
Build a quick attendance marking interface where teachers can:
- Mark attendance for entire class at once
- Quickly toggle present/absent/late
- Add bulk notes

### 4. Add Scheduled Reports
Create scheduled commands to email reports:
- Daily attendance summary
- Weekly fee collection report
- Monthly comprehensive report

### 5. Add Dashboard Widgets
Create widgets for the main dashboard:
- Today's attendance summary
- Outstanding fees widget
- Student enrollment trends

---

## Troubleshooting

### Issue: Reports not showing in navigation
**Solution**: Ensure you're logged in as Admin (role_id = 1)

### Issue: PDF export fails
**Solution**:
1. Ensure DomPDF is installed: `composer require barryvdh/laravel-dompdf`
2. Create the PDF template views
3. Check file permissions on storage directory

### Issue: No attendance data
**Solution**: Create sample data using the Tinker commands above

### Issue: Filters not working
**Solution**: Clear Livewire cache: `php artisan livewire:clear`

---

## Maintenance

### Regular Tasks:
1. **Archive old attendance records** (older than 2 years)
2. **Backup exported reports** regularly
3. **Monitor PDF generation performance** for large datasets
4. **Review and optimize queries** as data grows

### Performance Monitoring:
```bash
# Monitor slow queries
php artisan telescope:clear
php artisan telescope:prune --hours=48
```

---

## Support & Documentation

For questions or issues:
1. Check Laravel Filament documentation: https://filamentphp.com/docs
2. Review this implementation guide
3. Check application logs: `storage/logs/laravel.log`

---

## Changelog

### Version 1.0 (October 10, 2025)
- ✅ Created Attendance model and migration
- ✅ Implemented Student Reports page
- ✅ Implemented Fee Reports page
- ✅ Implemented Attendance Reports page
- ✅ Added PDF export functionality (templates required)
- ✅ Added real-time filtering and statistics
- ✅ Added bulk operations support
- ✅ Optimized queries with eager loading and indexes
- ✅ Added inline attendance editing
- ✅ Created payment history component

---

## License

This reporting system is part of the St. Francis of Assisi School Management System.
