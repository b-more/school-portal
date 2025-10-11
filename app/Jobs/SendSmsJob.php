<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phoneNumber,
        public string $message,
        public string $messageType = 'general',
        public ?int $referenceId = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        // Check if this job is part of a batch and if the batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            // Don't send SMS if batch was cancelled
            return;
        }

        try {
            // Send the SMS using the centralized service
            $sent = $smsService->send(
                $this->message,
                $this->phoneNumber,
                $this->messageType,
                $this->referenceId
            );

            if (!$sent) {
                // If sending failed, throw exception to trigger retry
                throw new \Exception('SMS sending failed - will retry');
            }

            Log::info('SMS job completed successfully', [
                'phone' => substr($this->phoneNumber, 0, 6) . '****',
                'type' => $this->messageType,
                'attempts' => $this->attempts()
            ]);

        } catch (\Exception $e) {
            Log::error('SMS job failed', [
                'phone' => substr($this->phoneNumber, 0, 6) . '****',
                'type' => $this->messageType,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'error' => $e->getMessage()
            ]);

            // If we've exceeded max attempts, mark as permanently failed
            if ($this->attempts() >= $this->tries) {
                Log::critical('SMS job permanently failed after all retries', [
                    'phone' => substr($this->phoneNumber, 0, 6) . '****',
                    'type' => $this->messageType,
                    'attempts' => $this->attempts()
                ]);
            }

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SMS job failed permanently', [
            'phone' => substr($this->phoneNumber, 0, 6) . '****',
            'type' => $this->messageType,
            'reference_id' => $this->referenceId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // You could notify administrators here or log to a monitoring service
    }
}
