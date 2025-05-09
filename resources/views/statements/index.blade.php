@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Fee Statement Generator</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('fee-statements.generate') }}" target="_blank">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type"><strong>Report Type</strong></label>
                                    <select name="report_type" id="report_type" class="form-control" required>
                                        <option value="">Select Report Type</option>
                                        <option value="individual">Individual Student</option>
                                        <option value="grade">By Grade</option>
                                        <option value="section">By Section</option>
                                        <option value="term">By Term</option>
                                        <option value="all">All Students</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="academic_year_id"><strong>Academic Year</strong></label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group student-section" style="display: none;">
                                    <label for="student_id"><strong>Student</strong></label>
                                    <select name="student_id" id="student_id" class="form-control">
                                        <option value="">Select Student</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->grade }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group grade-section" style="display: none;">
                                    <label for="grade_id"><strong>Grade</strong></label>
                                    <select name="grade_id" id="grade_id" class="form-control">
                                        <option value="">Select Grade</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group section-section" style="display: none;">
                                    <label for="section_id"><strong>Section</strong></label>
                                    <select name="section_id" id="section_id" class="form-control">
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="term_id"><strong>Term</strong> (Optional for some reports)</label>
                                    <select name="term_id" id="term_id" class="form-control">
                                        <option value="">All Terms</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pdf" id="pdf" value="1" checked>
                                    <label class="form-check-label" for="pdf">
                                        Generate PDF
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="download" id="download" value="1">
                                    <label class="form-check-label" for="download">
                                        Download PDF (instead of viewing in browser)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-file-pdf"></i> Generate Statement
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h5 class="mb-3">Payment Summary Report</h5>
                    <form method="POST" action="{{ route('fee-statements.summary') }}" target="_blank">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="summary_academic_year_id"><strong>Academic Year</strong></label>
                                    <select name="academic_year_id" id="summary_academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="summary_term_id"><strong>Term</strong> (Optional)</label>
                                    <select name="term_id" id="summary_term_id" class="form-control">
                                        <option value="">All Terms</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pdf" id="summary_pdf" value="1" checked>
                                    <label class="form-check-label" for="summary_pdf">
                                        Generate PDF
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="download" id="summary_download" value="1">
                                    <label class="form-check-label" for="summary_download">
                                        Download PDF (instead of viewing in browser)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-chart-bar"></i> Generate Summary
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Show/hide appropriate form sections based on report type
        $('#report_type').on('change', function() {
            var reportType = $(this).val();

            // Hide all conditional sections first
            $('.student-section, .grade-section, .section-section').hide();

            // Show sections based on report type
            switch(reportType) {
                case 'individual':
                    $('.student-section').show();
                    break;
                case 'grade':
                    $('.grade-section').show();
                    break;
                case 'section':
                    $('.section-section').show();
                    break;
            }
        });
    });
</script>
@endpush
@endsection
