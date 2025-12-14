<?php

namespace App\Filament\Imports;

use App\Constants\RoleConstants;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\Term;
use App\Models\User;
use App\Models\UserCredential;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    // Store parent data temporarily
    protected ?array $parentData = null;

    public static function getColumns(): array
    {
        return [
            // Student Information
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('student_id_number')
                ->label('Student ID'),

            ImportColumn::make('gender')
                ->rules(['nullable', 'in:male,female']),

            ImportColumn::make('date_of_birth'),

            ImportColumn::make('place_of_birth'),

            ImportColumn::make('address'),

            ImportColumn::make('grade_id')
                ->label('Grade')
                ->requiredMapping(),

            ImportColumn::make('class_section_id')
                ->label('Class Section'),

            ImportColumn::make('enrollment_term_id')
                ->label('Term'),

            ImportColumn::make('standard_of_education')
                ->label('Level of Education'),

            ImportColumn::make('enrollment_status'),

            ImportColumn::make('admission_date'),

            ImportColumn::make('previous_school'),

            ImportColumn::make('religious_denomination'),

            ImportColumn::make('medical_information'),

            ImportColumn::make('notes'),

            // Parent/Guardian Information
            ImportColumn::make('parent_name')
                ->label('Parent/Guardian Name'),

            ImportColumn::make('parent_phone')
                ->label('Parent Phone'),

            ImportColumn::make('parent_email')
                ->label('Parent Email'),

            ImportColumn::make('parent_nrc')
                ->label('Parent NRC'),

            ImportColumn::make('parent_relationship')
                ->label('Relationship (father/mother/guardian)'),

            ImportColumn::make('parent_occupation')
                ->label('Parent Occupation'),

            ImportColumn::make('parent_address')
                ->label('Parent Address'),
        ];
    }

    public function resolveRecord(): ?Student
    {
        // Update existing student if student_id_number matches
        if (!empty($this->data['student_id_number'])) {
            return Student::firstOrNew([
                'student_id_number' => $this->data['student_id_number'],
            ]);
        }

        return new Student();
    }

    protected function beforeFill(): void
    {
        // Store parent data before it gets filtered out
        $this->parentData = [
            'name' => $this->data['parent_name'] ?? null,
            'phone' => $this->formatPhoneNumber($this->data['parent_phone'] ?? null),
            'email' => $this->data['parent_email'] ?? null,
            'nrc' => $this->data['parent_nrc'] ?? null,
            'relationship' => $this->normalizeRelationship($this->data['parent_relationship'] ?? null),
            'occupation' => $this->data['parent_occupation'] ?? null,
            'address' => $this->data['parent_address'] ?? $this->data['address'] ?? null,
        ];

        // Remove parent fields from student data
        unset(
            $this->data['parent_name'],
            $this->data['parent_phone'],
            $this->data['parent_email'],
            $this->data['parent_nrc'],
            $this->data['parent_relationship'],
            $this->data['parent_occupation'],
            $this->data['parent_address']
        );

        // Resolve grade_id from name (e.g., "Grade 2" -> 5)
        if (!empty($this->data['grade_id']) && !is_numeric($this->data['grade_id'])) {
            $grade = Grade::where('name', trim($this->data['grade_id']))->first();
            $this->data['grade_id'] = $grade?->id;
        }

        // Resolve class_section_id from full name (e.g., "Grade 2 A" -> 6)
        if (!empty($this->data['class_section_id']) && !is_numeric($this->data['class_section_id'])) {
            $fullName = trim($this->data['class_section_id']);

            // Match by combining grade name + section name (e.g., "Grade 2 A")
            $classSection = ClassSection::select('class_sections.*')
                ->join('grades', 'class_sections.grade_id', '=', 'grades.id')
                ->whereRaw("CONCAT(grades.name, ' ', class_sections.name) = ?", [$fullName])
                ->first();

            $this->data['class_section_id'] = $classSection?->id;
        }

        // Resolve enrollment_term_id from name (e.g., "Term 1" -> 1)
        if (!empty($this->data['enrollment_term_id']) && !is_numeric($this->data['enrollment_term_id'])) {
            $term = Term::where('name', trim($this->data['enrollment_term_id']))->first();
            $this->data['enrollment_term_id'] = $term?->id;
        }

        // Parse dates
        $this->data['date_of_birth'] = $this->parseDate($this->data['date_of_birth'] ?? null);
        $this->data['admission_date'] = $this->parseDate($this->data['admission_date'] ?? null);

        // Map enrollment status (enrolled -> active)
        $statusMap = ['enrolled' => 'active', 'withdrawn' => 'inactive', 'suspended' => 'inactive'];
        if (!empty($this->data['enrollment_status'])) {
            $status = strtolower(trim($this->data['enrollment_status']));
            $this->data['enrollment_status'] = $statusMap[$status] ?? $status;
        } else {
            $this->data['enrollment_status'] = 'active';
        }
    }

    protected function afterSave(): void
    {
        // Create or link parent after student is saved
        if ($this->parentData && !empty($this->parentData['name'])) {
            $parent = $this->findOrCreateParent();

            if ($parent) {
                // Link student to parent
                $this->record->update(['parent_guardian_id' => $parent->id]);
            }
        }
    }

    /**
     * Find existing parent by phone/email/NRC or create new one
     */
    protected function findOrCreateParent(): ?ParentGuardian
    {
        $parent = null;

        // Try to find existing parent by phone (most reliable identifier)
        if (!empty($this->parentData['phone'])) {
            $parent = ParentGuardian::where('phone', $this->parentData['phone'])->first();
        }

        // Try by NRC if not found
        if (!$parent && !empty($this->parentData['nrc'])) {
            $parent = ParentGuardian::where('nrc', $this->parentData['nrc'])->first();
        }

        // Try by email if not found
        if (!$parent && !empty($this->parentData['email'])) {
            $parent = ParentGuardian::where('email', $this->parentData['email'])->first();
        }

        // Create new parent if not found
        if (!$parent) {
            $parent = ParentGuardian::create([
                'name' => $this->parentData['name'],
                'phone' => $this->parentData['phone'],
                'email' => $this->parentData['email'],
                'nrc' => $this->parentData['nrc'],
                'relationship' => $this->parentData['relationship'] ?? 'guardian',
                'occupation' => $this->parentData['occupation'],
                'address' => $this->parentData['address'],
                'role_id' => RoleConstants::PARENT,
            ]);
        }

        return $parent;
    }

    /**
     * Format phone number to standard format (260XXXXXXXXX - 12 digits)
     */
    protected function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Convert to string and remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);

        // Handle case where phone already has 260 prefix with extra 0 (e.g., 2600975020473)
        if (str_starts_with($phone, '2600') && strlen($phone) === 13) {
            // Remove the extra 0 after 260
            $phone = '260' . substr($phone, 4);
        }

        // If already correct format (260 + 9 digits = 12 digits), return as is
        if (str_starts_with($phone, '260') && strlen($phone) === 12) {
            return $phone;
        }

        // If starts with 260 but wrong length, extract last 9 digits
        if (str_starts_with($phone, '260') && strlen($phone) > 12) {
            $phone = '260' . substr($phone, -9);
            return $phone;
        }

        // If starts with 0 (local format like 0975020473), replace 0 with 260
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            $phone = '260' . substr($phone, 1);
            return $phone;
        }

        // If 9 digits (no prefix), add 260
        if (strlen($phone) === 9) {
            $phone = '260' . $phone;
            return $phone;
        }

        // If 10+ digits starting with 0, remove leading 0 and add 260
        if (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
            // Ensure max 12 digits
            if (strlen($phone) > 12) {
                $phone = substr($phone, 0, 12);
            }
            return $phone;
        }

        // Fallback: take last 9 digits and add 260
        if (strlen($phone) >= 9) {
            $phone = '260' . substr($phone, -9);
        }

        return $phone;
    }

    /**
     * Normalize relationship value
     */
    protected function normalizeRelationship(?string $relationship): string
    {
        if (empty($relationship)) {
            return 'guardian';
        }

        $relationship = strtolower(trim($relationship));

        $validRelationships = ['father', 'mother', 'guardian', 'other'];

        if (in_array($relationship, $validRelationships)) {
            return $relationship;
        }

        // Map common variations
        $mappings = [
            'dad' => 'father',
            'papa' => 'father',
            'mom' => 'mother',
            'mum' => 'mother',
            'mama' => 'mother',
            'uncle' => 'guardian',
            'aunt' => 'guardian',
            'grandparent' => 'guardian',
            'grandfather' => 'guardian',
            'grandmother' => 'guardian',
        ];

        return $mappings[$relationship] ?? 'guardian';
    }

    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Try common date formats (dd/mm/yyyy first as primary format)
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, trim($date));
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import completed: ' . number_format($import->successful_rows) . ' students imported.';

        if ($failedRows = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRows) . ' rows failed.';
        }

        $body .= ' Parent records were automatically created and linked.';

        return $body;
    }
}
