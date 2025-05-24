<?php

// namespace App\Filament\Resources\SmsLogResource\Pages;

// use App\Filament\Resources\SmsLogResource;
// use Filament\Actions;
// use Filament\Resources\Pages\ListRecords;

// class ListSmsLogs extends ListRecords
// {
//     protected static string $resource = SmsLogResource::class;

//     protected function getHeaderWidgets(): array
//     {
//         return [
//             SmsLogResource\Widgets\SmsDashboardWidget::class,
//             SmsLogResource\Widgets\SmsTypeDistributionWidget::class,
//             SmsLogResource\Widgets\DailySmsTrendWidget::class,
//             SmsLogResource\Widgets\SmsCostOverview::class,
//         ];
//     }

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\Action::make('retry_all_failed')
//                 ->label('Retry All Failed Messages')
//                 ->icon('heroicon-o-arrow-path')
//                 ->color('danger')
//                 ->action(function () {
//                     $failedLogs = \App\Models\SmsLog::where('status', 'failed')
//                         ->take(30) // Limit to 30 at a time
//                         ->get();

//                     $total = $failedLogs->count();
//                     $success = 0;
//                     $failed = 0;

//                     foreach ($failedLogs as $log) {
//                         try {
//                             // Replace @ with (at) for SMS compatibility
//                             $sms_message = str_replace('@', '(at)', $log->message);
//                             $url_encoded_message = urlencode($sms_message);

//                             // Send the SMS
//                             $sendSenderSMS = \Illuminate\Support\Facades\Http::withoutVerifying()
//                                 ->timeout(20)
//                                 ->connectTimeout(10)
//                                 ->retry(3, 2000)
//                                 ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $log->recipient . '&api_key=121231313213123123');

//                             $isSuccessful = $sendSenderSMS->successful() &&
//                                           (strtolower($sendSenderSMS->body()) === 'success' ||
//                                            strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

//                             // Create a new SMS log entry for the resent message
//                             \App\Models\SmsLog::create([
//                                 'recipient' => $log->recipient,
//                                 'message' => $log->message,
//                                 'status' => $isSuccessful ? 'sent' : 'failed',
//                                 'message_type' => $log->message_type,
//                                 'reference_id' => $log->reference_id,
//                                 'cost' => $log->cost,
//                                 'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
//                                 'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
//                                 'sent_by' => \Illuminate\Support\Facades\Auth::id(),
//                             ]);

//                             if ($isSuccessful) {
//                                 $success++;
//                             } else {
//                                 $failed++;
//                             }

//                             // Add a small delay between sends
//                             usleep(200000); // 200ms

//                         } catch (\Exception $e) {
//                             // Log error and create a failed SMS log
//                             \App\Models\SmsLog::create([
//                                 'recipient' => $log->recipient,
//                                 'message' => $log->message,
//                                 'status' => 'failed',
//                                 'message_type' => $log->message_type,
//                                 'reference_id' => $log->reference_id,
//                                 'cost' => $log->cost,
//                                 'error_message' => "Retry failed: {$e->getMessage()}",
//                                 'sent_by' => \Illuminate\Support\Facades\Auth::id(),
//                             ]);

//                             $failed++;
//                         }
//                     }

//                     \Filament\Notifications\Notification::make()
//                         ->title('Retry All Failed Complete')
//                         ->body("Total: $total, Successfully retried: $success, Failed: $failed")
//                         ->color($failed === 0 ? 'success' : 'warning')
//                         ->send();
//                 })
//                 ->visible(fn () => \App\Models\SmsLog::where('status', 'failed')->exists()),
//         ];
//     }
// }
