<x-filament::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-lg shadow">
            <div class="space-y-4">
                <h3 class="text-xl font-bold">Broadcast Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Title</p>
                        <p class="text-base font-medium">{{ $record->title }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">Recipients</p>
                        <p class="text-base font-medium">{{ $record->total_recipients }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-base font-medium">
                            @if($record->status === 'draft')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            @elseif($record->status === 'sending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Sending
                                </span>
                            @elseif($record->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Completed
                                </span>
                            @elseif($record->status === 'failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Failed
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm font-medium text-gray-500">Message</p>
                    <p class="text-base whitespace-pre-line">{{ $record->message }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow">
            <div class="space-y-4">
                <h3 class="text-xl font-bold">Sending Progress</h3>

                @if($processingComplete)
                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mt-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Broadcast Complete</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>Successfully sent {{ $successCount }} messages, Failed {{ $failureCount }} messages.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('filament.admin.resources.communication-center.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Back to Communication Center
                    </a>
                @else
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                    Progress
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-indigo-600">
                                    {{ $progress }}%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                            <div style="width:{{ $progress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <div class="border rounded-md p-4">
                                <h4 class="font-semibold text-lg">{{ $successCount }}</h4>
                                <p class="text-sm text-gray-500">Messages Sent</p>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="border rounded-md p-4">
                                <h4 class="font-semibold text-lg">{{ $failureCount }}</h4>
                                <p class="text-sm text-gray-500">Failed Messages</p>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="border rounded-md p-4">
                                <h4 class="font-semibold text-lg">{{ $currentBatch }} / {{ $totalBatches }}</h4>
                                <p class="text-sm text-gray-500">Batches Processed</p>
                            </div>
                        </div>
                    </div>

                    @if(!$isProcessing)
                        <button type="button" wire:click="startProcessing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $currentBatch > 0 ? 'Resume Sending' : 'Start Sending' }}
                        </button>
                    @else
                        <div class="flex items-center space-x-2">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processing batch {{ $currentBatch + 1 }} of {{ $totalBatches }}...</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
