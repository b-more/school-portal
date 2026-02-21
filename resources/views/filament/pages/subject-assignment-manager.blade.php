<x-filament::page>
    <div class="space-y-6">
        <div class="flex mb-6">
            <div class="space-x-2 flex">
                <x-filament::button
                    wire:click="$set('activeTab', 'create')"
                    :color="$activeTab === 'create' ? 'primary' : 'gray'"
                >
                    Create Subject
                </x-filament::button>

                <x-filament::button
                    wire:click="$set('activeTab', 'assign')"
                    :color="$activeTab === 'assign' ? 'primary' : 'gray'"
                >
                    Assign Subjects
                </x-filament::button>

                <x-filament::button
                    wire:click="$set('activeTab', 'view')"
                    :color="$activeTab === 'view' ? 'primary' : 'gray'"
                >
                    View Subjects
                </x-filament::button>
            </div>
        </div>

        <div x-show="$wire.activeTab === 'create'">
            <div class="mb-6">
                <h2 class="text-xl font-bold">Create New Subject</h2>
                <p class="text-gray-500">Add a new subject to the system</p>
            </div>

            {{ $this->form }}
        </div>

        <div x-show="$wire.activeTab === 'assign'">
            <div class="mb-6">
                <h2 class="text-xl font-bold">Assign Subjects to Teachers</h2>
                <p class="text-gray-500">Link subjects to teachers based on their department and classes</p>
            </div>

            {{ $this->assignmentForm }}
        </div>

        <div x-show="$wire.activeTab === 'view'">
            <div class="mb-6">
                <h2 class="text-xl font-bold">Current Subjects</h2>
                <p class="text-gray-500">View all subjects in the system</p>
            </div>

            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Grade Level</th>
                            <th scope="col" class="px-6 py-3">Description</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Teacher Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $subject->name }}</td>
                                <td class="px-6 py-4">{{ $subject->code }}</td>
                                <td class="px-6 py-4">{{ $subject->grade_level }}</td>
                                <td class="px-6 py-4">{{ Str::limit($subject->description, 50) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $subject->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $subject->employees_count ?? $subject->employees()->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-filament::form wire:submit="create">
        {{ $this->getFormActions() }}
    </x-filament::form>
</x-filament::page>
