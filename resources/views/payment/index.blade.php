<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School Fee Payment - St. Francis Assisi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        .card-shadow {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .logo-container {
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        @media print {
            body {
                background: white !important;
            }

            /* Hide everything except receipt */
            body > div {
                visibility: hidden;
            }

            #receiptSection {
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide print button when printing */
            button {
                display: none !important;
            }

            /* Ensure receipt looks good on print */
            #receiptSection .bg-white {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-md mx-auto">
            <!-- Header with Logo -->
            <div class="text-center mb-8 fade-in">
                <div class="flex justify-center mb-4">
                    <div class="logo-container">
                        <img src="{{ asset('images/logo.png') }}" alt="St. Francis Assisi Logo" class="w-20 h-20 object-contain">
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2 drop-shadow-lg">St. Francis Assisi School</h1>
                <p class="text-white/90 text-sm font-medium">School Fee Payment Portal</p>
                <div class="mt-3 inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <p class="text-white text-xs font-semibold">Secure Mobile Money Payments</p>
                </div>
            </div>

            <!-- Search Student Section -->
            <div id="searchSection" class="bg-white rounded-2xl card-shadow p-8 mb-6 fade-in">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-100 p-3 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Find Student</h2>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Student ID or Name
                    </label>
                    <input
                        type="text"
                        id="studentSearch"
                        class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base"
                        placeholder="Enter Student ID or Name"
                    >
                </div>

                <button
                    id="searchBtn"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center transform hover:scale-[1.02]"
                >
                    <span id="searchBtnText">Search Student</span>
                    <span id="searchSpinner" class="spinner ml-2 hidden"></span>
                </button>

                <div id="searchError" class="mt-4 p-4 bg-red-50 border-2 border-red-200 rounded-xl text-red-700 text-sm hidden"></div>
            </div>

            <!-- Student Details & Payment Form Section (hidden initially) -->
            <div id="paymentSection" class="hidden">
                <!-- Student Information Card -->
                <div class="bg-white rounded-2xl card-shadow p-8 mb-6 fade-in">
                    <div class="flex items-center mb-6">
                        <div class="bg-green-100 p-3 rounded-xl mr-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Student Information</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Student ID:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayStudentId"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Name:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayName"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Class:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayGrade"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Academic Year:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayYear"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Term:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayTerm"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b-2 border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Fees:</span>
                            <span class="text-sm font-bold text-gray-800" id="displayTotal"></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 bg-green-50 px-4 py-3 rounded-xl border-2 border-green-100">
                            <span class="text-sm font-medium text-gray-600">Amount Paid:</span>
                            <span class="text-base font-bold text-green-600" id="displayPaid"></span>
                        </div>
                        <div class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-4 rounded-xl border-2 border-blue-200">
                            <span class="text-base font-bold text-gray-700">Balance Due:</span>
                            <span class="text-xl font-bold text-blue-600" id="displayBalance"></span>
                        </div>
                    </div>

                    <button
                        id="changeStudentBtn"
                        class="mt-6 w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm border-2 border-gray-200"
                    >
                        Search Different Student
                    </button>
                </div>

                <!-- Payment Form Card -->
                <div class="bg-white rounded-2xl card-shadow p-8 fade-in">
                    <div class="flex items-center mb-6">
                        <div class="bg-purple-100 p-3 rounded-xl mr-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Make Payment</h2>
                    </div>

                    <form id="paymentForm">
                        <input type="hidden" id="studentId" name="student_id">

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                📱 Mobile Number
                            </label>
                            <input
                                type="tel"
                                id="mobileNumber"
                                name="mobile_number"
                                class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-base"
                                placeholder="0977123456"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-2 ml-1">Enter the mobile money number to be charged</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                💰 Amount to Pay (ZMW)
                            </label>
                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                step="0.01"
                                min="1"
                                class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-base"
                                placeholder="0.00"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-2 ml-1">Enter any amount up to the balance due</p>
                        </div>

                        <div id="paymentError" class="mb-4 p-4 bg-red-50 border-2 border-red-200 rounded-xl text-red-700 text-sm hidden"></div>

                        <button
                            type="submit"
                            id="payBtn"
                            class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 rounded-xl font-semibold hover:from-green-700 hover:to-green-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center transform hover:scale-[1.02]"
                        >
                            <span id="payBtnText">Process Payment</span>
                            <span id="payBtnTimer" class="ml-3 px-3 py-1 bg-white/20 rounded-lg font-mono text-lg hidden">2:30</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Payment Status Section (hidden initially) -->
            <div id="statusSection" class="hidden">
                <div class="bg-white rounded-2xl card-shadow p-8 text-center fade-in">
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full mb-6 shadow-lg">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Payment Initiated</h3>
                        <p class="text-base text-gray-600 mb-6" id="statusMessage">
                            Please check your phone to complete the payment.
                        </p>

                        <!-- Countdown Timer -->
                        <div class="mb-6">
                            <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-6 rounded-xl border-2 border-orange-200">
                                <p class="text-sm font-semibold text-orange-700 mb-3">Complete payment within:</p>
                                <div class="text-5xl font-bold text-orange-600" id="countdown">2:30</div>
                                <p class="text-xs text-orange-600 mt-2">Time remaining</p>
                            </div>
                        </div>

                        <!-- Payment Instructions -->
                        <div class="bg-blue-50 p-5 rounded-xl mb-6 border-2 border-blue-200">
                            <div class="flex items-start mb-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-blue-800 mb-2">What to do now:</p>
                                    <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
                                        <li>Check your mobile phone for a payment prompt</li>
                                        <li>Enter your mobile money PIN to authorize the payment</li>
                                        <li>Wait for confirmation - this page updates automatically</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-5 rounded-xl mb-6 border-2 border-gray-200">
                            <p class="text-xs font-semibold text-gray-600 mb-2">Payment Reference:</p>
                            <p class="text-lg font-mono font-bold text-gray-800" id="paymentReference"></p>
                        </div>

                        <!-- Check Status Section -->
                        <div id="statusCheckSection">
                            <button
                                id="checkStatusBtn"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center transform hover:scale-[1.02] mb-4"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span id="checkStatusBtnText">Check Payment Status</span>
                                <span id="checkStatusSpinner" class="spinner ml-2 hidden"></span>
                            </button>

                            <p class="text-xs text-center text-gray-600 mb-4">
                                Click the button above after you've authorized the payment on your phone
                            </p>
                        </div>
                    </div>

                    <button
                        id="newPaymentBtn"
                        class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm border-2 border-gray-200"
                    >
                        Cancel & Go Back
                    </button>
                </div>
            </div>

            <!-- Receipt Section (hidden initially) -->
            <div id="receiptSection" class="hidden">
                <div class="bg-white rounded-2xl card-shadow p-8 fade-in">
                    <!-- Success Header -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full mb-4 shadow-xl">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-green-600 mb-2">Payment Successful!</h2>
                        <p class="text-gray-600">Your payment has been processed successfully</p>
                    </div>

                    <!-- Receipt Details -->
                    <div class="border-t-4 border-b-4 border-green-500 py-6 mb-6">
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/logo.png') }}" alt="School Logo" class="w-16 h-16 mx-auto mb-2 object-contain">
                            <h3 class="text-xl font-bold text-gray-800">St. Francis Assisi School</h3>
                            <p class="text-sm text-gray-600">Payment Receipt</p>
                        </div>

                        <div class="space-y-3 mt-6">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Receipt Number:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptNumber"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Date & Time:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptDateTime"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Payment Reference:</span>
                                <span class="text-sm font-mono font-bold text-gray-800" id="receiptPaymentRef"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Student ID:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptStudentId"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Student Name:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptStudentName"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Class:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptGrade"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Academic Year:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptYear"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Term:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptTerm"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Mobile Number:</span>
                                <span class="text-sm font-bold text-gray-800" id="receiptMobile"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-sm font-medium text-gray-600">Transaction ID:</span>
                                <span class="text-xs font-mono font-bold text-gray-800" id="receiptTransactionId"></span>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="mt-6 bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-xl border-2 border-blue-200">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-base font-semibold text-gray-700">Amount Paid:</span>
                                <span class="text-2xl font-bold text-green-600" id="receiptAmountPaid"></span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-base font-semibold text-gray-700">Previous Balance:</span>
                                <span class="text-lg font-bold text-gray-800" id="receiptPreviousBalance"></span>
                            </div>
                            <div class="border-t-2 border-blue-300 pt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-800">New Balance:</span>
                                    <span class="text-2xl font-bold text-blue-600" id="receiptNewBalance"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button
                            onclick="window.print()"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center transform hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Receipt
                        </button>

                        <button
                            id="newPaymentBtn2"
                            class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm border-2 border-gray-200"
                        >
                            Make Another Payment
                        </button>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                        <p class="text-xs text-yellow-800 text-center">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Please save or screenshot this receipt for your records
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 fade-in">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                    <div class="flex justify-center items-center mb-3">
                        <svg class="w-5 h-5 text-white/80 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-white/90 text-sm font-semibold">Secure Payment Portal</p>
                    </div>
                    <p class="text-white/80 text-xs mb-2">&copy; {{ date('Y') }} St. Francis Assisi School. All rights reserved.</p>
                    <p class="text-white/70 text-xs">For assistance, contact the school office.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token Setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Elements
        const searchSection = document.getElementById('searchSection');
        const paymentSection = document.getElementById('paymentSection');
        const statusSection = document.getElementById('statusSection');
        const searchBtn = document.getElementById('searchBtn');
        const searchBtnText = document.getElementById('searchBtnText');
        const searchSpinner = document.getElementById('searchSpinner');
        const studentSearch = document.getElementById('studentSearch');
        const searchError = document.getElementById('searchError');
        const paymentForm = document.getElementById('paymentForm');
        const payBtn = document.getElementById('payBtn');
        const payBtnText = document.getElementById('payBtnText');
        const payBtnTimer = document.getElementById('payBtnTimer');
        const paymentError = document.getElementById('paymentError');
        const changeStudentBtn = document.getElementById('changeStudentBtn');
        const newPaymentBtn = document.getElementById('newPaymentBtn');
        const newPaymentBtn2 = document.getElementById('newPaymentBtn2');
        const checkStatusBtn = document.getElementById('checkStatusBtn');
        const checkStatusBtnText = document.getElementById('checkStatusBtnText');
        const checkStatusSpinner = document.getElementById('checkStatusSpinner');

        let currentStudent = null;
        let currentPaymentId = null;
        let countdownInterval = null;
        let timeRemaining = 150; // 2 minutes 30 seconds
        let paymentData = {}; // Store payment details

        // Search Student
        searchBtn.addEventListener('click', async () => {
            const search = studentSearch.value.trim();

            if (!search) {
                showError(searchError, 'Please enter a Student ID or Name');
                return;
            }

            setLoading(searchBtn, searchBtnText, searchSpinner, true);
            hideError(searchError);

            try {
                const response = await fetch('{{ route("payment.search-student") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ search })
                });

                const data = await response.json();

                if (data.success) {
                    currentStudent = data.student;
                    displayStudentInfo(data.student);
                    showSection('payment');
                } else {
                    showError(searchError, data.message);
                }
            } catch (error) {
                showError(searchError, 'An error occurred. Please try again.');
            } finally {
                setLoading(searchBtn, searchBtnText, searchSpinner, false);
            }
        });

        // Process Payment
        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                student_id: document.getElementById('studentId').value,
                mobile_number: document.getElementById('mobileNumber').value,
                amount: parseFloat(document.getElementById('amount').value)
            };

            if (formData.amount > currentStudent.balance) {
                showError(paymentError, 'Amount cannot exceed the balance due (ZMW ' + currentStudent.balance.toFixed(2) + ')');
                return;
            }

            setLoading(payBtn, payBtnText, payBtnTimer, true);
            hideError(paymentError);

            // Start countdown timer immediately
            startCountdown();

            try {
                const response = await fetch('{{ route("payment.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    currentPaymentId = data.qr_payment_id;
                    paymentData = {
                        reference: data.payment_reference,
                        amount: formData.amount,
                        mobile: formData.mobile_number
                    };
                    document.getElementById('paymentReference').textContent = data.payment_reference;
                    document.getElementById('statusMessage').textContent = data.message;
                    showSection('status');
                } else {
                    // Payment initiation failed
                    stopTimers();

                    // Check if it's a network/timeout error
                    const errorMessage = data.message || 'Payment initiation failed. Please try again.';

                    // If it's a serious error, show it on the status page instead of the form
                    if (errorMessage.includes('timeout') || errorMessage.includes('Network') || errorMessage.includes('service')) {
                        currentPaymentId = null;
                        document.getElementById('paymentReference').textContent = 'N/A';
                        showSection('status');
                        showFailedPayment(errorMessage);
                    } else {
                        // Show in form for validation errors
                        showError(paymentError, errorMessage);
                    }
                }
            } catch (error) {
                stopTimers();
                const errorMsg = 'Unable to process your request. Please check your internet connection and try again.';
                currentPaymentId = null;
                document.getElementById('paymentReference').textContent = 'N/A';
                showSection('status');
                showFailedPayment(errorMsg);
            } finally {
                setLoading(payBtn, payBtnText, payBtnTimer, false);
            }
        });

        // Manual payment status check
        checkStatusBtn.addEventListener('click', async () => {
            if (!currentPaymentId) return;

            setLoading(checkStatusBtn, checkStatusBtnText, checkStatusSpinner, true);

            try {
                const response = await fetch('{{ route("payment.check-status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ payment_id: currentPaymentId })
                });

                const data = await response.json();

                if (data.success && data.status === 'completed') {
                    // Payment successful - store transaction details
                    paymentData.transactionId = data.transaction_id;
                    paymentData.completedAt = data.completed_at;

                    stopTimers();
                    showReceipt();
                } else if (data.success && data.status === 'failed') {
                    // Payment failed
                    stopTimers();
                    showFailedPayment(data.message || 'Payment was not successful. Please try again.');
                } else {
                    // Still pending/processing
                    const statusMessages = {
                        'pending': 'Payment is still pending. Please check your phone and authorize the payment, then check again.',
                        'processing': 'Payment is being processed. Please wait a moment and check again.'
                    };

                    alert(statusMessages[data.status] || 'Payment is still being processed. Please wait and try again.');
                }
            } catch (error) {
                console.error('Check status error:', error);
                alert('Unable to check payment status. Please check your internet connection and try again.');
            } finally {
                setLoading(checkStatusBtn, checkStatusBtnText, checkStatusSpinner, false);
            }
        });

        // Show failed payment
        function showFailedPayment(message) {
            stopTimers();

            // Hide countdown timer and other status elements
            const countdownSection = document.querySelector('#statusSection .bg-gradient-to-r.from-orange-50');
            if (countdownSection) {
                countdownSection.style.display = 'none';
            }

            // Determine if it's a timeout or general failure
            const isTimeout = message.includes('timeout') || message.includes('timed out') || message.includes('delays') || message.includes('expired');
            const icon = isTimeout ? `
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            ` : `
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;

            const title = isTimeout ? 'Payment Request Timeout' : 'Payment Failed';
            const bgColor = isTimeout ? 'from-orange-50 to-orange-100 border-orange-300' : 'from-red-50 to-red-100 border-red-300';
            const iconColor = isTimeout ? 'bg-orange-500' : 'bg-red-500';
            const textColor = isTimeout ? 'text-orange-800' : 'text-red-800';

            // Show error in a modal/alert style - replace entire status section content
            const statusSectionContent = document.querySelector('#statusSection > div');
            statusSectionContent.innerHTML = `
                <div class="text-center mb-6">
                    <div class="bg-gradient-to-r ${bgColor} border-2 rounded-2xl p-8 shadow-lg mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 ${iconColor} rounded-full mb-4 shadow-lg">
                            ${icon}
                        </div>
                        <h3 class="text-2xl font-bold ${textColor} mb-3">${title}</h3>
                        <p class="${textColor} text-base leading-relaxed">${message}</p>
                    </div>

                    <button onclick="location.reload()" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl mb-3">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Try Again
                    </button>

                    <button onclick="location.href='{{ url('/pay') }}'" class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm border-2 border-gray-200">
                        Back to Home
                    </button>
                </div>
            `;
        }

        // Change Student Button
        changeStudentBtn.addEventListener('click', () => {
            showSection('search');
            studentSearch.value = '';
            currentStudent = null;
        });

        // New Payment Button
        newPaymentBtn.addEventListener('click', () => {
            resetPayment();
        });

        newPaymentBtn2.addEventListener('click', () => {
            resetPayment();
        });

        // Reset payment
        function resetPayment() {
            stopTimers();
            showSection('search');
            studentSearch.value = '';
            currentStudent = null;
            currentPaymentId = null;
            paymentData = {};
            timeRemaining = 150; // 2:30
            paymentForm.reset();
        }

        // Start countdown timer
        function startCountdown() {
            timeRemaining = 150; // Reset to 2:30
            updateCountdownDisplay();

            countdownInterval = setInterval(() => {
                timeRemaining--;
                updateCountdownDisplay();

                if (timeRemaining <= 0) {
                    handlePaymentTimeout();
                }
            }, 1000);
        }

        // Handle payment timeout
        async function handlePaymentTimeout() {
            stopTimers();

            // Check one more time if payment was completed
            if (currentPaymentId) {
                try {
                    const response = await fetch('{{ route("payment.check-status") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ payment_id: currentPaymentId })
                    });

                    const data = await response.json();

                    if (data.success && data.status === 'completed') {
                        showReceipt();
                        return;
                    }
                } catch (error) {
                    console.error('Final check error:', error);
                }
            }

            // If not completed, show timeout message
            showFailedPayment('Payment time expired. The transaction may have timed out. Please check with the school if the payment was deducted from your account.');
        }

        // Update countdown display
        function updateCountdownDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('countdown').textContent = display;

            // Also update button timer if visible
            if (payBtnTimer && !payBtnTimer.classList.contains('hidden')) {
                payBtnTimer.textContent = display;
            }

            // Change color when time is running out
            const countdownEl = document.getElementById('countdown');
            if (timeRemaining <= 60) {
                countdownEl.classList.remove('text-orange-600');
                countdownEl.classList.add('text-red-600');
            }
        }

        // Stop all timers
        function stopTimers() {
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        // Show receipt
        function showReceipt() {
            // Populate receipt details
            const now = new Date();
            const dateTimeStr = paymentData.completedAt ?
                new Date(paymentData.completedAt).toLocaleString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }) :
                now.toLocaleString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

            document.getElementById('receiptNumber').textContent = 'RCP-' + now.getFullYear() + '-' + String(now.getTime()).slice(-6);
            document.getElementById('receiptDateTime').textContent = dateTimeStr;
            document.getElementById('receiptPaymentRef').textContent = paymentData.reference || document.getElementById('paymentReference').textContent;
            document.getElementById('receiptStudentId').textContent = currentStudent.student_id;
            document.getElementById('receiptStudentName').textContent = currentStudent.name;
            document.getElementById('receiptGrade').textContent = currentStudent.grade;
            document.getElementById('receiptYear').textContent = currentStudent.academic_year;
            document.getElementById('receiptTerm').textContent = currentStudent.term;
            document.getElementById('receiptMobile').textContent = paymentData.mobile || document.getElementById('mobileNumber').value;
            document.getElementById('receiptTransactionId').textContent = paymentData.transactionId || 'N/A';

            const amountPaid = paymentData.amount || parseFloat(document.getElementById('amount').value);
            const previousBalance = currentStudent.balance;
            const newBalance = Math.max(0, previousBalance - amountPaid);

            document.getElementById('receiptAmountPaid').textContent = 'ZMW ' + amountPaid.toFixed(2);
            document.getElementById('receiptPreviousBalance').textContent = 'ZMW ' + previousBalance.toFixed(2);
            document.getElementById('receiptNewBalance').textContent = 'ZMW ' + newBalance.toFixed(2);

            showSection('receipt');
        }

        // Helper Functions
        function displayStudentInfo(student) {
            document.getElementById('studentId').value = student.id;
            document.getElementById('displayStudentId').textContent = student.student_id;
            document.getElementById('displayName').textContent = student.name;
            document.getElementById('displayGrade').textContent = student.grade;
            document.getElementById('displayYear').textContent = student.academic_year;
            document.getElementById('displayTerm').textContent = student.term;
            document.getElementById('displayTotal').textContent = 'ZMW ' + student.total_amount.toFixed(2);
            document.getElementById('displayPaid').textContent = 'ZMW ' + student.amount_paid.toFixed(2);
            document.getElementById('displayBalance').textContent = 'ZMW ' + student.balance.toFixed(2);
            document.getElementById('mobileNumber').value = student.parent_mobile;
        }

        function showSection(section) {
            searchSection.classList.add('hidden');
            paymentSection.classList.add('hidden');
            statusSection.classList.add('hidden');
            document.getElementById('receiptSection').classList.add('hidden');

            if (section === 'search') {
                searchSection.classList.remove('hidden');
            } else if (section === 'payment') {
                paymentSection.classList.remove('hidden');
            } else if (section === 'status') {
                statusSection.classList.remove('hidden');
            } else if (section === 'receipt') {
                document.getElementById('receiptSection').classList.remove('hidden');
            }
        }

        function setLoading(btn, btnText, timerElement, loading) {
            btn.disabled = loading;
            if (loading) {
                // Store original text if not already stored
                if (!btnText.dataset.originalText) {
                    btnText.dataset.originalText = btnText.textContent;
                }

                if (timerElement && timerElement.id === 'payBtnTimer') {
                    btnText.textContent = 'Processing Payment';
                    timerElement.classList.remove('hidden');
                } else if (timerElement && timerElement.id === 'checkStatusSpinner') {
                    btnText.textContent = 'Checking...';
                    timerElement.classList.remove('hidden');
                } else if (timerElement) {
                    // For other buttons (like search) that still use spinner
                    btnText.textContent = 'Please wait...';
                    timerElement.classList.remove('hidden');
                }
            } else {
                btnText.textContent = btnText.dataset.originalText || 'Submit';
                if (timerElement) {
                    timerElement.classList.add('hidden');
                }
            }
        }

        function showError(element, message) {
            element.textContent = message;
            element.classList.remove('hidden');
        }

        function hideError(element) {
            element.classList.add('hidden');
        }

        // Store original button text
        searchBtnText.dataset.originalText = searchBtnText.textContent;
        payBtnText.dataset.originalText = payBtnText.textContent;
        checkStatusBtnText.dataset.originalText = checkStatusBtnText.textContent;
    </script>
</body>
</html>
