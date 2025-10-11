# Student Resource SQL Ambiguous Column Fix

## Issue
When a **teacher** tried to access `/admin/students`, the following SQL error occurred:

```
SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'id' in field list is ambiguous
```

**SQL Query:**
```sql
select `id` from `class_sections`
inner join `subject_teachings` on `class_sections`.`id` = `subject_teachings`.`class_section_id`
where `subject_teachings`.`teacher_id` = 9
```

## Root Cause
In `StudentResource.php` line 65, when fetching class section IDs for a teacher:

```php
$classSectionIds = $teacher->classSections()->pluck('id')->toArray();
```

The `classSections()` relationship goes through the `subject_teachings` pivot table (many-to-many). Both `class_sections` and `subject_teachings` tables have an `id` column, so the database couldn't determine which `id` to return.

## Solution
Specify the table name when plucking the ID column:

**Before:**
```php
$classSectionIds = $teacher->classSections()->pluck('id')->toArray();
```

**After:**
```php
$classSectionIds = $teacher->classSections()->pluck('class_sections.id')->toArray();
```

## Code Location
- **File**: `app/Filament/Resources/StudentResource.php`
- **Method**: `getEloquentQuery()`
- **Line**: 66 (after fix)

## Why This Happened
- Teachers access students through their assigned class sections
- The relationship `Teacher::classSections()` is a many-to-many relationship through `subject_teachings`
- When Laravel joins tables, both have `id` columns
- Without specifying the table, SQL doesn't know which `id` to select

## Impact
This fix allows teachers to:
- ✅ View the students list page
- ✅ See only students in their assigned classes
- ✅ Access all student management features

## Testing
To verify the fix works:

1. **Login as a teacher** (e.g., Yvonne Mudenda)
2. **Navigate to** `/admin/students`
3. **Expected result**: Teacher sees only students from their assigned classes
4. **No SQL errors**

## Related Models
- `Teacher` model (has many classSections through subject_teachings)
- `ClassSection` model
- `SubjectTeaching` model (pivot table)
- `Student` model

## Prevention
When using `pluck()` on relationships that involve joins with multiple tables:
- Always specify the table name: `pluck('table_name.column_name')`
- This prevents ambiguous column errors
- Especially important for common column names like `id`, `name`, `created_at`

## Files Modified
- `app/Filament/Resources/StudentResource.php` (Line 66)

## Similar Issues to Watch For
Check other places where we might have the same issue:
```bash
grep -r "->pluck('id')" app/Filament/Resources/
```

Any relationship that goes through pivot tables should use fully qualified column names.
