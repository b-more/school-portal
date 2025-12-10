# TRAINING DOCUMENTS INDEX

**Location:** `/var/www/html/school-portal/`
**Created:** October 17, 2025

---

## ALL TRAINING DOCUMENTS

### 1. Portal Credentials
**File:** `TRAINING_PORTAL_CREDENTIALS.md`
**Size:** 13 KB
**Contains:**
- Teacher login credentials (17 teachers)
- Student login credentials (52 students by class)
- Parent login credentials (51 parents)
- All default passwords

---

### 2. Teacher Training Guidelines
**File:** `TEACHER_TRAINING_GUIDELINES.md`
**Size:** 21 KB
**Contains:**
- Complete teacher training manual
- Login instructions
- Attendance recording
- Homework management
- Grade entry procedures
- Communication tools
- Troubleshooting guide

---

### 3. Student Training Guidelines
**File:** `STUDENT_TRAINING_GUIDELINES.md`
**Size:** 21 KB
**Contains:**
- Student-friendly training guide
- Simple login steps
- How to view homework
- How to submit assignments
- Checking grades
- Safety and responsible use

---

### 4. Parent Training Guidelines
**File:** `PARENT_TRAINING_GUIDELINES.md`
**Size:** 36 KB
**Contains:**
- Comprehensive parent manual
- Monitoring child's progress
- Attendance tracking
- Fee management
- Teacher communication
- Report card access

---

## HOW TO ACCESS THESE FILES

### Using Command Line:
```bash
cd /var/www/html/school-portal/
ls -lh *TRAINING* *GUIDELINES*
```

### Using File Browser:
Navigate to: `/var/www/html/school-portal/`
Look for files with "TRAINING" or "GUIDELINES" in the name

### Download All Files:
```bash
# Create a zip archive
cd /var/www/html/school-portal/
zip -r TRAINING_DOCUMENTS.zip *TRAINING*.md *GUIDELINES*.md
```

---

## VERIFICATION

To verify all files exist, run:
```bash
ls -lh /var/www/html/school-portal/ | grep -E "(TRAINING|GUIDELINES)"
```

You should see:
- PARENT_TRAINING_GUIDELINES.md
- STUDENT_TRAINING_GUIDELINES.md
- TEACHER_TRAINING_GUIDELINES.md
- TRAINING_ASSESSMENT_TESTS.md
- TRAINING_PORTAL_CREDENTIALS.md

---

**If you still cannot see the files, please:**
1. Refresh your file browser
2. Check you're in the correct directory
3. Verify file permissions
4. Contact system administrator

**All files were created successfully on October 17, 2025 at 04:24-04:29 UTC**
