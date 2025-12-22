// Create a view file for the setup wizard
// resources/views/filament/pages/setup-wizard.blade.php

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Progress indicator -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="relative">
                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                    <div style="width: {{ ($currentStep / $totalSteps) * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-600 transition-all duration-500"></div>
                </div>
                <div class="flex justify-between mt-2">
                    @for ($i = 1; $i <= $totalSteps; $i++)
                        <div class="flex flex-col items-center">
                            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $i <= $currentStep ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $i }}
                            </div>
                            <div class="text-xs mt-1 {{ $i <= $currentStep ? 'text-primary-600 font-medium' : 'text-gray-500' }}">
                                @switch($i)
                                    @case(1)
                                        School Info
                                        @break
                                    @case(2)
                                        Academic Year
                                        @break
                                    @case(3)
                                        School Sections
                                        @break
                                    @case(4)
                                        Terms
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Form content -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                @if ($currentStep === 1)
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">School Information</h2>
                        <p class="text-gray-600">Enter the basic information about your school.</p>
                    </div>
                    {{ $this->schoolForm }}
                @elseif ($currentStep === 2)
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Academic Year</h2>
                        <p class="text-gray-600">Configure the first academic year for your school.</p>
                    </div>
                    {{ $this->academicYearForm }}
                @elseif ($currentStep === 3)
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">School Sections</h2>
                        <p class="text-gray-600">Define the different sections of your school (e.g. Early Learning Center, Primary, Secondary).</p>
                    </div>
                    {{ $this->sectionsForm }}
                @elseif ($currentStep === 4)
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Terms Configuration</h2>
                        <p class="text-gray-600">Configure the terms for your academic year.</p>
                    </div>
                    {{ $this->termsForm }}
                @endif
            </div>

            <!-- Navigation buttons -->
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-between">
                <button
                    type="button"
                    @click="$wire.previousStep()"
                    @class([
                        'px-4 py-2 text-sm font-medium rounded-lg border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600',
                        'border-gray-300 bg-white text-gray-800 hover:bg-gray-100' => $currentStep > 1,
                        'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' => $currentStep <= 1,
                    ])
                    @if ($currentStep <= 1) disabled @endif
                >
                    Previous
                </button>

                <div>
                    @if ($currentStep < $totalSteps)
                        <button
                            type="button"
                            @click="$wire.nextStep()"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600"
                        >
                            Next
                        </button>
                    @else
                        <button
                            type="button"
                            @click="$wire.completeSetup()"
                            class="px-4 py-2 text-sm font-medium text-white bg-success-600 border border-transparent rounded-lg hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-600"
                        >
                            Complete Setup
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
