<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\SupportForm;
use App\Mail\SupportFormMail;
use Exception;

class SendSupportFormEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The support form instance.
     */
    protected SupportForm $supportForm;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retry after 30s, 60s, and 120s
    }

    /**
     * Create a new job instance.
     *
     * @param SupportForm $supportForm
     * @return void
     */
    public function __construct(SupportForm $supportForm)
    {
        $this->supportForm = $supportForm;
        
        // Set job configuration
        $this->onQueue('emails'); // Use dedicated email queue
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Log job start for monitoring
            \Log::info('Processing support form email job', [
                'support_form_id' => $this->supportForm->id,
                'form_type' => $this->supportForm->type,
                'destination' => $this->supportForm->getDestinationEmail(),
            ]);

            // Send the email using the mailable class
            Mail::send(new SupportFormMail($this->supportForm));

            // Mark the email as sent in the database
            $this->supportForm->markEmailSent();

            // Log successful completion
            \Log::info('Support form email sent successfully', [
                'support_form_id' => $this->supportForm->id,
                'form_type' => $this->supportForm->type,
                'sent_to' => $this->supportForm->getDestinationEmail(),
                'sent_at' => now(),
            ]);

        } catch (Exception $exception) {
            // Log the error for debugging
            \Log::error('Failed to send support form email', [
                'support_form_id' => $this->supportForm->id,
                'form_type' => $this->supportForm->type,
                'destination' => $this->supportForm->getDestinationEmail(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw the exception to trigger retry mechanism
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Log final failure after all retries exhausted
        \Log::critical('Support form email job failed permanently', [
            'support_form_id' => $this->supportForm->id,
            'form_type' => $this->supportForm->type,
            'destination' => $this->supportForm->getDestinationEmail(),
            'final_error' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);

        // Optionally, you could:
        // 1. Send notification to administrators
        // 2. Mark the support form with a failure flag
        // 3. Queue a manual review task
        
        // For now, just ensure we don't lose the support form data
        // by keeping email_sent as false so it can be manually processed
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        // Give up after 1 hour total (including all retries)
        return now()->addHour();
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'support-form',
            'email',
            'form-type:' . $this->supportForm->type,
            'form-id:' . $this->supportForm->id,
        ];
    }
}
