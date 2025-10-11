# Attendance System - Complete Documentation

## Overview
A modern, role-based attendance management system for St. Francis of Assisi School with bulk marking, reporting, and export capabilities.

## Features

### ✅ Role-Based Access Control
- **Admin**: Full access to all attendance records
- **Teachers**: Access only to their assigned classes (as grade teacher)
- **Students**: View only their own attendance
- **Parents**: View attendance for their children

### ✅ Bulk Attendance Marking
- Mark entire class attendance in one action
- Select class, date, and default status
- Automatically creates or updates attendance records
- Shows summary: Created, Updated, Total Students

### ✅ Individual Attendance Management
- Create, edit, view, delete individual records
- Track check-in and check-out times
- Add notes for each attendance record
- Four status types: Present, Absent, Late, Excused

### ✅ Advanced Reporting & Export
- HTML report with statistics (printable)
- CSV export for Excel/Google Sheets
- Date range filtering
- Class section filtering
- Attendance statistics dashboard

## Database Structure

### Attendance Table
```
- id (primary key)
- student_id (foreign key to students)
- class_section_id (foreign key to class_sections)
- grade_id (foreign key to grades)
- academic_year_id (foreign key to academic_years)
- term_id (foreign key to terms)
- attendance_date (date)
- status (enum: present, absent, late, excused)
- check_in_time (time, nullable)
- check_out_time (time, nullable)
- notes (text, nullable)
- marked_by (foreign key to users)
- created_at
- updated_at
```

## Usage Guide

### For Teachers

#### 1. Bulk Mark Attendance
**URL**: `/admin/attendances`

1. Click **"Bulk Mark Attendance"** button
2. Select your class from dropdown
3. Choose date (defaults to today)
4. Select status: Present, Absent, Late, or Excused
5. Optionally set check-in time
6. Add notes (optional)
7. Click **"Submit"**

**Result**: All students in the class will be marked with the selected status.

**Smart Updates**:
- If attendance exists for that date → Updates the record
- If no attendance exists → Creates new record
- Shows summary: "Created: X | Updated: Y | Total Students: Z"

#### 2. Mark Individual Attendance
1. Click **"New Attendance"** button
2. Select class section
3. Select student (filtered by class)
4. Choose date
5. Select status
6. Set check-in/out times (optional)
7. Add notes
8. Click **"Create"**

#### 3. Bulk Actions on List
Select multiple attendance records and:
- **Mark as Present**: Sets status to 'present' + check-in time
- **Mark as Absent**: Sets status to 'absent', clears times
- **Mark as Late**: Sets status to 'late' + check-in time
- **Delete**: Remove selected records (Admin only)

#### 4. Export Reports
1. Click **"Export Report"** button
2. Opens in new tab showing:
   - Summary statistics
   - Present/Absent percentages
   - Full attendance table
3. Click **"Print Report"** to print
4. Click **"Download CSV"** for Excel export

### For Students

#### View My Attendance
**URL**: `/admin/attendances`

Students see:
- Their own attendance records only
- Date, Status, Check-in/out times
- Notes from teacher
- Cannot create, edit, or delete

**Columns Visible**:
- Date
- Status (color-coded badge)
- Check In Time
- Check Out Time
- Notes

**Filters Available**:
- Status filter
- Date range filter

### For Parents

#### View Children's Attendance
**URL**: `/admin/attendances`

Parents see:
- Attendance for all their children
- Student name column (which child)
- Class information
- All attendance details

### For Admins

**Full Access**:
- See all classes and students
- Create/Edit/Delete any record
- Access all reports
- Manage attendance for any class

## Access Control Details

### Primary Teachers
**Definition**: Teachers assigned to a class as the grade/class teacher

**Access**:
```php
// Teachers see attendance only for classes where they are grade teacher
$classSectionIds = $teacher->classSections()->pluck('class_sections.id');
$query->whereIn('class_section_id', $classSectionIds);
```

**Capabilities**:
- ✅ Mark attendance for their classes
- ✅ Edit attendance for their students
- ✅ View reports for their classes
- ✅ Bulk mark attendance
- ❌ Cannot see other teachers' classes
- ❌ Cannot delete (Admin only)

### Secondary Grade Teachers
**Definition**: Teachers assigned as grade teacher for specific class sections

**How It Works**:
- Each ClassSection has a `teacher_id` (the grade teacher)
- Teacher relationship: `classSections()` returns classes they're grade teacher for
- Only shows attendance for those specific class sections

**Example**:
- Teacher A is grade teacher for "Grade 9 - A"
- Teacher A can ONLY mark/view attendance for Grade 9-A students
- Teacher A cannot see Grade 9-B (even if teaching a subject there)

## Bulk Marking Workflow

### Step-by-Step Process

1. **Teacher Opens Bulk Mark Form**
   ```
   Button: "Bulk Mark Attendance"
   Opens: Slide-over modal (3xl width)
   ```

2. **Selects Class**
   ```
   Dropdown shows: "Grade Name - Section Name"
   Example: "Baby Class - A"
   Only shows teacher's assigned classes
   ```

3. **Chooses Date**
   ```
   Date Picker (max: today)
   Default: Current date
   Format: DD/MM/YYYY
   ```

4. **Selects Default Status**
   ```
   Options:
   - Present (most common)
   - Absent
   - Late
   - Excused
   ```

5. **Optional: Sets Check-in Time**
   ```
   Visible for: Present, Late
   Hidden for: Absent, Excused
   Format: HH:MM (24-hour)
   ```

6. **Optional: Adds Notes**
   ```
   Textarea (max 500 characters)
   Example: "School assembly today"
   ```

7. **System Processing**
   ```php
   foreach ($students as $student) {
       // Check if attendance exists for this date
       $existing = Attendance::where('student_id', $student->id)
           ->where('attendance_date', $date)
           ->first();

       if ($existing) {
           $existing->update($attendanceData); // Update
           $updated++;
       } else {
           Attendance::create($attendanceData); // Create
           $created++;
       }
   }
   ```

8. **Success Notification**
   ```
   "Attendance Marked Successfully"
   "Created: 15 | Updated: 5 | Total Students: 20"
   ```

## Reports & Export

### HTML Report (Printable)

**URL**: `/attendance/export`

**Features**:
- School header
- Date range display
- Statistics cards:
  - Total Records
  - Present (count & percentage)
  - Absent (count & percentage)
  - Late count
  - Excused count
- Full data table
- Print-friendly styling
- "Print Report" button
- "Download CSV" button

**Statistics Calculation**:
```php
$totalRecords = $attendanceRecords->count();
$presentCount = $attendanceRecords->where('status', 'present')->count();
$absentCount = $attendanceRecords->where('status', 'absent')->count();
$presentPercentage = ($presentCount / $totalRecords) * 100;
```

### CSV Export

**URL**: `/attendance/export?format=csv`

**File Format**:
```csv
Date,Student,Class,Status,Check In,Check Out,Notes,Marked By
10/10/2025,Hope Mulenga,Baby Class - A,Present,08:30,-,-,Yvonne Mudenda
```

**Use Cases**:
- Import into Excel
- Data analysis
- External reporting systems
- Backup/archival

### Role-Based Filtering

**Teachers**:
```php
// Only their class sections
$classSectionIds = $teacher->classSections()->pluck('class_sections.id');
$query->whereIn('class_section_id', $classSectionIds);
```

**Students**:
```php
// Only their own records
$query->where('student_id', $student->id);
```

**Parents**:
```php
// All their children
$studentIds = $parent->students()->pluck('id');
$query->whereIn('student_id', $studentIds);
```

## Table Columns

### Teacher/Admin View
- ✅ Date (sortable, searchable)
- ✅ Student Name (sortable, searchable)
- ✅ Class (Grade - Section)
- ✅ Status (color-coded badge)
- ✅ Check In Time
- ✅ Check Out Time
- ✅ Notes
- ✅ Marked By (toggleable, hidden by default)

### Student View
- ✅ Date
- ✅ Status (badge)
- ✅ Check In Time
- ✅ Check Out Time
- ✅ Notes
- ❌ Student Name (hidden - they only see their own)
- ❌ Class (hidden)
- ❌ Marked By (hidden)

## Filters

### 1. Class Section Filter
```php
SelectFilter::make('class_section')
    ->relationship('classSection', 'name')
```
- Shows all class sections (filtered by role)
- Multi-select enabled

### 2. Status Filter
```php
SelectFilter::make('status')
    ->options([
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'excused' => 'Excused',
    ])
```

### 3. Date Range Filter
```php
Filter::make('date_range')
    ->form([
        DatePicker::make('from'),
        DatePicker::make('to'),
    ])
```
- From Date
- To Date
- Filters attendance_date

## Status Badges

### Color Coding
- **Present**: Green badge (success)
- **Absent**: Red badge (danger)
- **Late**: Orange badge (warning)
- **Excused**: Blue badge (info)

### Display
```php
BadgeColumn::make('status')
    ->colors([
        'success' => 'present',
        'danger' => 'absent',
        'warning' => 'late',
        'info' => 'excused',
    ])
```

## Navigation

### Menu Location
**Group**: Academic
**Icon**: Clipboard Document Check
**Label**: Attendance
**Sort Order**: 5

### Visible To
- ✅ Admin
- ✅ Teachers
- ✅ Students
- ✅ Parents
- ❌ Other roles

## File Structure

```
app/
├── Models/
│   └── Attendance.php              # Model with scopes
├── Filament/
│   └── Resources/
│       ├── AttendanceResource.php  # Main resource
│       └── AttendanceResource/
│           └── Pages/
│               ├── ListAttendances.php    # List + Bulk Mark
│               ├── CreateAttendance.php   # Create single
│               ├── EditAttendance.php     # Edit
│               └── ViewAttendance.php     # View
├── Http/
│   └── Controllers/
│       └── AttendanceController.php       # Export controller
resources/
└── views/
    └── attendance/
        └── report.blade.php               # Print/Export view
routes/
└── web.php                                # Export route
```

## API Endpoints

### Export Report
```
GET /attendance/export
GET /attendance/export?format=csv
GET /attendance/export?class_section_id=1
GET /attendance/export?start_date=2025-10-01&end_date=2025-10-31
```

**Parameters**:
- `format`: html (default) | csv
- `class_section_id`: Filter by class
- `start_date`: Start of date range
- `end_date`: End of date range

## Security Features

### 1. Role-Based Queries
Every query is automatically filtered by user role:
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = Auth::user();

    // Apply role-specific filtering
    if ($user->role_id === RoleConstants::TEACHER) {
        // Only their classes
    } elseif ($user->role_id === RoleConstants::STUDENT) {
        // Only their records
    }

    return $query;
}
```

### 2. Permission Checks
```php
canCreate(): Only Admin, Teacher
canEdit(): Only Admin, Teacher
canDelete(): Only Admin
canDeleteAny(): Only Admin
```

### 3. Automatic marked_by
```php
'marked_by' => Auth::id()
```
Every attendance record tracks who created/modified it.

### 4. Date Validation
```php
->maxDate(now())  // Cannot mark future dates
```

## Best Practices

### For Teachers

1. **Mark Attendance Daily**
   - Use bulk mark for entire class
   - Update individual students as needed

2. **Use Status Appropriately**
   - **Present**: Student is in class
   - **Late**: Student arrived after start time
   - **Absent**: Student did not attend
   - **Excused**: Absent with valid reason (sick note, etc.)

3. **Add Notes**
   - For absences: "Sick - Parent called"
   - For late arrivals: "Parent meeting"
   - For excused: "Medical appointment"

4. **Check Previous Records**
   - Before bulk marking, check if already marked
   - System will update, not duplicate

### For Admins

1. **Monitor Attendance Rates**
   - Use export reports monthly
   - Check for patterns (frequent absences)
   - Identify students needing intervention

2. **Review Teacher Marking**
   - "Marked By" column shows who entered data
   - Check for classes not being marked

3. **Data Integrity**
   - Delete duplicate records if needed
   - Verify grade_id matches class_section

## Common Workflows

### Daily Attendance - Primary School
1. Teacher logs in at 8:00 AM
2. Opens Attendance → Bulk Mark
3. Selects "Baby Class - A"
4. Date: Today
5. Status: Present
6. Check-in time: 08:00
7. Clicks Submit
8. All 25 students marked present
9. Manually updates:
   - 2 students absent
   - 1 student late (arrived 8:15)

### Daily Attendance - Secondary School
1. Grade Teacher logs in
2. Opens Attendance → Bulk Mark
3. Selects "Grade 9 - A"
4. Date: Today
5. Status: Present
6. Check-in time: 07:30
7. All students marked
8. Individual updates for absences/late

### Monthly Report
1. Admin/Teacher opens Attendance
2. Clicks "Export Report"
3. System defaults to current month
4. Views statistics:
   - Total: 500 records
   - Present: 450 (90%)
   - Absent: 30 (6%)
   - Late: 15 (3%)
   - Excused: 5 (1%)
5. Clicks "Download CSV" for analysis

## Testing Checklist

### Teacher Access
- [ ] Can see only their assigned classes
- [ ] Can bulk mark attendance
- [ ] Can create individual records
- [ ] Can edit records they created
- [ ] Cannot see other teachers' classes
- [ ] Cannot delete records

### Student Access
- [ ] Can see only their own attendance
- [ ] Cannot create/edit/delete
- [ ] Can filter and search their records
- [ ] Can export their own report

### Bulk Marking
- [ ] Creates new records correctly
- [ ] Updates existing records
- [ ] Shows accurate summary
- [ ] Sets marked_by to current user
- [ ] Validates all required fields

### Export
- [ ] HTML report displays correctly
- [ ] Print formatting works
- [ ] CSV downloads properly
- [ ] Role-based filtering applies
- [ ] Statistics are accurate

## Troubleshooting

### Issue: Teacher Can't See Classes
**Solution**: Check if teacher is assigned as grade teacher for the class_section
```sql
SELECT * FROM class_section_teacher
WHERE teacher_id = ? AND class_section_id = ?;
```

### Issue: Duplicate Attendance Records
**Solution**: Bulk mark with same date updates existing, doesn't duplicate
Check for manual duplicates:
```sql
SELECT student_id, attendance_date, COUNT(*)
FROM attendances
GROUP BY student_id, attendance_date
HAVING COUNT(*) > 1;
```

### Issue: Export Shows No Data
**Solution**: Check role-based filtering and date range
- Teachers: Only their classes
- Students: Only their records
- Date range defaults to current month

## URLs

### Main Pages
- **Attendance List**: `/admin/attendances`
- **Create Attendance**: `/admin/attendances/create`
- **View Attendance**: `/admin/attendances/{id}`
- **Edit Attendance**: `/admin/attendances/{id}/edit`
- **Export Report**: `/attendance/export`
- **Export CSV**: `/attendance/export?format=csv`

## Summary

The Attendance System provides:
- ✅ **Role-based access** for security
- ✅ **Bulk marking** for efficiency
- ✅ **Individual management** for flexibility
- ✅ **Advanced reporting** for insights
- ✅ **Export capabilities** for external use
- ✅ **Student/Parent visibility** for transparency
- ✅ **Modern UI** with Filament 3
- ✅ **Complete audit trail** (marked_by tracking)

Perfect for managing daily attendance across both Primary and Secondary school levels!
