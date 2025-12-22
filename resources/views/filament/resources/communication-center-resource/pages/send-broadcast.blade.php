<x-filament-panels::page>
    <div class="space-y-6" style="background-color: #f8fafc; min-height: 100vh; padding: 20px;">
        {{-- Title --}}
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ $record->title }}</h1>
            <p style="color: #6b7280;">Created {{ $record->created_at->diffForHumans() }}</p>
        </div>

        {{-- Stats --}}
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; text-align: center;">
                <div>
                    <div style="font-size: 36px; font-weight: bold; color: #2563eb;">{{ count($recipients) ?: $record->total_recipients }}</div>
                    <div style="font-size: 14px; color: #6b7280;">Recipients</div>
                </div>
                <div>
                    <div style="font-size: 36px; font-weight: bold; color: #16a34a;">{{ $successCount }}</div>
                    <div style="font-size: 14px; color: #6b7280;">Sent</div>
                </div>
                <div>
                    <div style="font-size: 36px; font-weight: bold; color: #dc2626;">{{ $failureCount }}</div>
                    <div style="font-size: 14px; color: #6b7280;">Failed</div>
                </div>
                <div>
                    <div style="font-size: 36px; font-weight: bold; color: #7c3aed;">{{ $progress }}%</div>
                    <div style="font-size: 14px; color: #6b7280;">Complete</div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px;">Progress</h3>
            <div style="width: 100%; background-color: #e5e7eb; border-radius: 9999px; height: 16px; margin-bottom: 8px;">
                <div style="background-color: #2563eb; height: 16px; border-radius: 9999px; width: {{ $progress }}%; transition: width 0.5s ease;"></div>
            </div>
            <p style="font-size: 14px; color: #6b7280;">{{ $progress }}% Complete</p>
        </div>

        {{-- Action Button --}}
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; text-align: center;">
            @if($processingComplete)
                <div style="color: #16a34a; font-size: 20px; font-weight: 600; margin-bottom: 16px;">✅ Broadcast Complete!</div>
                <p style="color: #6b7280; margin-bottom: 16px;">Successfully sent {{ $successCount }} messages</p>
                <a href="{{ \App\Filament\Resources\CommunicationCenterResource::getUrl('index') }}"
                   style="background-color: #2563eb; color: white; padding: 12px 24px; border-radius: 8px; font-size: 18px; font-weight: 500; text-decoration: none; display: inline-block;">
                    Back to Communication Center
                </a>
            @elseif($isProcessing)
                <div style="color: #2563eb; font-size: 20px; font-weight: 600; margin-bottom: 16px;">📱 Sending Messages...</div>
                <p style="color: #6b7280;">Processing batch {{ $currentBatch + 1 }} of {{ $totalBatches }}</p>
                <p style="font-size: 14px; color: #9ca3af; margin-top: 8px;">Please keep this page open</p>
            @else
                <button
                    wire:click="startProcessing"
                    style="background-color: #16a34a; color: white; padding: 16px 32px; border-radius: 8px; font-size: 20px; font-weight: 600; border: none; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'"
                >
                    🚀 START SENDING MESSAGES
                </button>
                <p style="color: #6b7280; margin-top: 16px;">Click to send to {{ count($recipients) ?: $record->total_recipients }} recipients</p>
            @endif
        </div>

        {{-- Message Preview --}}
        <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px;">Message</h3>
            <div style="background-color: #f3f4f6; padding: 16px; border-radius: 4px; border-left: 4px solid #2563eb;">
                <p style="color: #1f2937; line-height: 1.6;">{{ $record->message }}</p>
            </div>
        </div>

        {{-- Recipients List --}}
        @if(count($recipients) > 0)
            <div style="background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px;">Recipients ({{ count($recipients) }})</h3>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($recipients as $index => $recipient)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background-color: #f9fafb; border-radius: 4px;">
                            <div>
                                <span style="font-weight: 500; color: #1f2937;">{{ $index + 1 }}. {{ $recipient['name'] ?? 'Unknown' }}</span>
                                <span style="color: #6b7280; margin-left: 8px;">{{ $recipient['phone'] ?? 'No phone' }}</span>
                            </div>
                            <div style="font-size: 14px; color: #9ca3af;">
                                @if(!empty($recipient['student_name']))
                                    {{ $recipient['student_name'] }}
                                @endif
                                @if(!empty($recipient['grade']))
                                    ({{ $recipient['grade'] }})
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Auto-refresh when processing --}}
    @if($isProcessing)
        <script>
            setTimeout(() => {
                if (window.Livewire) {
                    window.Livewire.find('{{ $this->getId() }}').call('$refresh');
                }
            }, 2000);
        </script>
    @endif
</x-filament-panels::page>
