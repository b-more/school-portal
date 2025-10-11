# Student Homework View Access Fix - Documentation

## Issue
When students clicked "View Homework →" from the student dashboard, they received a 404 error:
```
GET http://102.23.120.249:11022/admin/teacher-homeworks/1 404 (Not Found)
```

## Root Cause

**Missing Student Access Permissions**

The `TeacherHomeworkResource` and `ViewTeacherHomework` page were configured to only allow ADMIN and TEACHER roles to access homework. Students were blocked from viewing homework details even though:
1. The view page existed (`ViewTeacherHomework.php`)
2. The route was registered (`/{record}`)
3. Students needed to view homework to submit assignments

### Specific Issues:

1. **Resource Query Filtering** - `getEloquentQuery()` only filtered for teachers and admins
2. **No canView() Method** - No explicit permission for students to view homework records
3. **Page Actions** - ViewTeacherHomework page only showed teacher/admin actions
4. **Statistics Visibility** - Students could see other students' submission statistics

## Solution Applied

### Step 1: Added Student Query Filtering
**File**: `app/Filament/Resources/TeacherHomeworkResource.php` (Lines 44-52)

**Added**:
```php
// If user is a student, show homework for their grade
if ($user->role_id === RoleConstants::STUDENT) {
    $student = \App\Models\Student::where('user_id', $user->id)->first();
    if ($student) {
        return $query->where('grade_id', $student->grade_id)
            ->where('status', 'active');
    }
    return $query->where('id', 0); // Return empty if student not found
}
```

**Why**: Students can now see homework assigned to their grade, filtered by active status only.

### Step 2: Added canView() Permission
**File**: `app/Filament/Resources/TeacherHomeworkResource.php` (Lines 365-369)

**Added**:
```php
public static function canView($record): bool
{
    // Allow admin, teachers, and students to view homework
    return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER, RoleConstants::STUDENT]) ?? false;
}
```

**Why**: Explicitly allows students to view homework detail pages.

### Step 3: Added Student-Specific Actions
**File**: `app/Filament/Resources/TeacherHomeworkResource/Pages/ViewTeacherHomework.php` (Lines 20-74)

**Changed**: `getHeaderActions()` to show different actions based on role

**Student Actions**:
```php
if ($isStudent) {
    return [
        Actions\Action::make('submit_homework')
            ->label('Submit Homework')
            ->icon('heroicon-o-paper-clip')
            ->color('primary')
            ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.create', [
                'homework_id' => $this->record->id,
            ]))
            ->visible(fn () => $this->record->status === 'active' && !$this->record->isSubmittedByStudent($this->getStudentId())),
        Actions\Action::make('view_my_submission')
            ->label('View My Submission')
            ->icon('heroicon-o-eye')
            ->color('success')
            ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.index'))
            ->visible(fn () => $this->record->isSubmittedByStudent($this->getStudentId())),
    ];
}
```

**Student Actions Explained**:
- **Submit Homework**: Visible only if homework is active and student hasn't submitted yet
- **View My Submission**: Visible only if student has already submitted

**Teacher Actions**: Edit, View Submissions, Mark Completed, Delete (unchanged)

### Step 4: Added Helper Method
**File**: `app/Filament/Resources/TeacherHomeworkResource/Pages/ViewTeacherHomework.php` (Lines 76-80)

**Added**:
```php
protected function getStudentId(): ?int
{
    $student = \App\Models\Student::where('user_id', auth()->id())->first();
    return $student?->id;
}
```

**Why**: Gets the student ID for the current user to check submission status.

### Step 5: Hid Teacher-Only Statistics
**File**: `app/Filament/Resources/TeacherHomeworkResource/Pages/ViewTeacherHomework.php` (Lines 206, 239)

**Changed**: Added role check to visibility conditions

**Before**:
```php
->visible(fn ($record) => $record->submissions()->exists())
```

**After**:
```php
->visible(fn ($record) => $record->submissions()->exists() && in_array(auth()->user()->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]))
```

**Sections Hidden from Students**:
- **Submission Statistics**: Total submissions, graded count, pending count, late count, average score
- **Recent Submissions**: List of other students' submissions

**Why**: Students should only see their own submission, not statistics about other students.

### Step 6: Formatted Code
**Action**: Ran Laravel Pint to ensure code style compliance

```bash
php vendor/bin/pint app/Filament/Resources/TeacherHomeworkResource.php \
                    app/Filament/Resources/TeacherHomeworkResource/Pages/ViewTeacherHomework.php
```

**Result**: 2 files formatted, style issues fixed ✅

### Step 7: Cleared Caches
**Action**: Cleared all Laravel and view caches

```bash
php artisan optimize:clear
php artisan view:clear
```

**Why**: Ensure new permissions and access controls take effect immediately.

## What Students See Now

### On Homework View Page

**Homework Details Section** ✅
- Title
- Subject (badge)
- Grade (badge)
- Status (badge)
- Description (formatted with markdown)

**Submission Details Section** ✅
- Submission Opens date/time
- Submission Deadline date/time
- Maximum Score
- Late Submission status (Allowed/Not Allowed)

**Attachments Section** ✅
- Homework Files (download links)
- Additional Resources (download links)

**Header Actions** ✅
- **Submit Homework** button (if not yet submitted)
- **View My Submission** button (if already submitted)

### What Students DON'T See

❌ Submission Statistics (total, graded, pending, late, average)
❌ Recent Submissions from other students
❌ Edit Homework button
❌ Delete Homework button
❌ Mark as Completed button
❌ View All Submissions button (teacher-only)

## Verification

### Test the Fix

1. ✅ Login as Hope Mulenga (hopemulenga@student.sfa.edu.zm / Password123)
2. ✅ Navigate to `/admin/student-dashboard`
3. ✅ Click "View Homework →" on the "Test" homework
4. ✅ Expected: Page loads at `/admin/teacher-homeworks/1`
5. ✅ Expected: Homework details are visible
6. ✅ Expected: "Submit Homework" button is visible
7. ✅ Expected: No teacher-only statistics or actions

### Check Access Control

**Test as Student**:
```bash
php artisan tinker

// Login as student
$user = App\Models\User::where('email', 'hopemulenga@student.sfa.edu.zm')->first();
Auth::login($user);

// Check if canView returns true
App\Filament\Resources\TeacherHomeworkResource::canView(
    App\Models\Homework::find(1)
);
// Expected: true
```

**Test Query Filtering**:
```bash
php artisan tinker

// Login as student
$user = App\Models\User::where('email', 'hopemulenga@student.sfa.edu.zm')->first();
Auth::login($user);

// Get homework query for student
$homework = App\Filament\Resources\TeacherHomeworkResource::getEloquentQuery()->get();
// Expected: Collection with 1 homework (ID: 1, Title: "Test")
```

## Complete Student Workflow

### Step-by-Step Flow

1. ✅ **Student logs in** → Redirected to `/admin/student-dashboard`
2. ✅ **Dashboard displays** → Shows pending homework in "Pending Homework" section
3. ✅ **Student clicks "View Homework →"** → Loads `/admin/teacher-homeworks/1`
4. ✅ **Homework details visible** → Shows title, description, due date, files
5. ⏳ **Student clicks "Submit Homework"** → Redirects to submission form (to be tested)
6. ⏳ **Student submits homework** → Creates HomeworkSubmission record (to be tested)
7. ⏳ **Teacher grades submission** → Updates submission with marks and feedback (to be tested)
8. ⏳ **Student sees graded submission** → Visible in "Recent Submissions" on dashboard (to be tested)

## Files Modified

### 1. `app/Filament/Resources/TeacherHomeworkResource.php`

**Lines 31-52**: Updated `getEloquentQuery()` to include student filtering
```php
// Added student query filtering
if ($user->role_id === RoleConstants::STUDENT) {
    $student = \App\Models\Student::where('user_id', $user->id)->first();
    if ($student) {
        return $query->where('grade_id', $student->grade_id)
            ->where('status', 'active');
    }
    return $query->where('id', 0);
}
```

**Lines 365-369**: Added `canView()` method
```php
public static function canView($record): bool
{
    return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER, RoleConstants::STUDENT]) ?? false;
}
```

### 2. `app/Filament/Resources/TeacherHomeworkResource/Pages/ViewTeacherHomework.php`

**Lines 5-6**: Added RoleConstants import
```php
use App\Constants\RoleConstants;
```

**Lines 20-74**: Updated `getHeaderActions()` with role-based actions
```php
// Different actions for students vs teachers
if ($isStudent) {
    // Submit Homework and View My Submission
} else {
    // Edit, View Submissions, Mark Completed, Delete
}
```

**Lines 76-80**: Added `getStudentId()` helper method
```php
protected function getStudentId(): ?int
{
    $student = \App\Models\Student::where('user_id', auth()->id())->first();
    return $student?->id;
}
```

**Lines 206, 239**: Added role check to visibility conditions
```php
->visible(fn ($record) => $record->submissions()->exists() && in_array(auth()->user()->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]))
```

## Security Considerations

### Access Control

✅ **Students can only view homework for their own grade**
- Query filtered by `student->grade_id`
- Only active homework is visible

✅ **Students cannot see other students' data**
- Submission statistics hidden
- Recent submissions list hidden
- Only their own submission is accessible

✅ **Students cannot modify homework**
- No Edit action
- No Delete action
- No Mark as Completed action

✅ **Students cannot view all submissions**
- "View Submissions" button hidden
- Statistics hidden

### Data Privacy

- Students see homework details (public information)
- Students see their own submission status
- Students DON'T see:
  - Other students' names in submissions
  - Other students' grades
  - Overall class statistics
  - Who else submitted

## Related Fixes

- `STUDENT_DASHBOARD_FIX.md` - Fixed route error in student dashboard
- `STUDENT_HOMEWORK_VISIBILITY_FIX.md` - Fixed homework visibility on dashboard
- `TEACHER_RESOURCES_FIX.md` - Fixed teacher homework resources
- `TEACHER_DASHBOARD_FIX.md` - Fixed route errors in teacher dashboard

## Prevention

### Best Practices

1. **Always Add Role-Based Access Control**
   ```php
   public static function canView($record): bool
   {
       return in_array(auth()->user()?->role_id, [/* allowed roles */]);
   }
   ```

2. **Filter Queries by Role**
   ```php
   public static function getEloquentQuery(): Builder
   {
       $query = parent::getEloquentQuery();
       if (auth()->user()->role_id === RoleConstants::STUDENT) {
           // Filter for student
       }
       return $query;
   }
   ```

3. **Use Role-Based Action Visibility**
   ```php
   Actions\EditAction::make()
       ->visible(fn() => in_array(auth()->user()->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]))
   ```

4. **Hide Sensitive Data from Students**
   ```php
   Section::make('Statistics')
       ->visible(fn() => auth()->user()->role_id !== RoleConstants::STUDENT)
   ```

## Testing Checklist

- [x] Student can view homework detail page
- [x] Student sees "Submit Homework" button (when not submitted)
- [x] Student doesn't see teacher-only actions (Edit, Delete)
- [x] Student doesn't see other students' statistics
- [x] Student can only see active homework for their grade
- [x] Caches cleared
- [x] Code formatted with Pint
- [ ] Student can submit homework (requires user testing)
- [ ] Student sees "View My Submission" after submitting (requires user testing)

## Summary

The 404 error when viewing homework has been fixed by:
1. ✅ Adding student role to `canView()` permission
2. ✅ Filtering homework query to include student's grade
3. ✅ Adding student-specific actions (Submit Homework, View My Submission)
4. ✅ Hiding teacher-only statistics and actions from students
5. ✅ Clearing all caches

**URL**: http://102.23.120.249:11022/admin/teacher-homeworks/1
**Status**: ✅ Fixed and accessible to students

**Student Login**:
- Email: hopemulenga@student.sfa.edu.zm
- Password: Password123

Students can now:
- ✅ View homework details from the dashboard
- ✅ See homework files and resources
- ✅ See submission deadline and requirements
- ⏳ Submit homework (next step to test)
