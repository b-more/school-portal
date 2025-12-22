<x-filament-panels::page>
    <div class="container">
        <div class="flex justify-center">
            <div class="w-full max-w-5xl">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 bg-primary-500 text-white">
                        <h4 class="text-lg font-semibold">Fee Statement Generator</h4>
                    </div>
                    <div class="p-6">
                        @if (session('error'))
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('fee-statements.generate') }}" target="_blank">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select name="report_type" id="report_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                        <option value="">Select Report Type</option>
                                        <option value="individual">Individual Student</option>
                                        <option value="grade">By Grade</option>
                                        <option value="section">By Section</option>
                                        <option value="term">By Term</option>
                                        <option value="all">All Students</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="student-section hidden">
                                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                                    <select name="student_id" id="student_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select Student</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->grade }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grade-section hidden">
                                    <label for="grade_id" class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                                    <select name="grade_id" id="grade_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select Grade</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="section-section hidden">
                                    <label for="section_id" class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                                    <select name="section_id" id="section_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term (Optional for some reports)</label>
                                    <select name="term_id" id="term_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">All Terms</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="pdf" id="pdf" value="1" checked class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">Generate PDF</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="download" id="download" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">Download PDF (instead of viewing in browser)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-start">
                                <button type="submit" class="bg-primary-500 text-white px-4 py-2 rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Generate Statement
                                </button>
                            </div>
                        </form>

                        <hr class="my-8">

                        <h5 class="text-lg font-semibold text-gray-800 mb-4">Payment Summary Report</h5>
                        <form method="POST" action="{{ route('fee-statements.summary') }}" target="_blank">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="summary_academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                                    <select name="academic_year_id" id="summary_academic_year_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="summary_term_id" class="block text-sm font-medium text-gray-700 mb-2">Term (Optional)</label>
                                    <select name="term_id" id="summary_term_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">All Terms</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="pdf" id="summary_pdf" value="1" checked class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">Generate PDF</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="download" id="summary_download" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">Download PDF (instead of viewing in browser)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-start">
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Generate Summary
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportTypeSelect = document.getElementById('report_type');
            const studentSection = document.querySelector('.student-section');
            const gradeSection = document.querySelector('.grade-section');
            const sectionSection = document.querySelector('.section-section');

            reportTypeSelect.addEventListener('change', function() {
                // Hide all sections first
                studentSection.classList.add('hidden');
                gradeSection.classList.add('hidden');
                sectionSection.classList.add('hidden');

                // Show appropriate section based on selection
                switch(this.value) {
                    case 'individual':
                        studentSection.classList.remove('hidden');
                        break;
                    case 'grade':
                        gradeSection.classList.remove('hidden');
                        break;
                    case 'section':
                        sectionSection.classList.remove('hidden');
                        break;
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
