<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SupportForm;

class SupportFormMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The support form instance.
     */
    public SupportForm $supportForm;

    /**
     * Create a new message instance.
     *
     * @param SupportForm $supportForm
     * @return void
     */
    public function __construct(SupportForm $supportForm)
    {
        $this->supportForm = $supportForm;
        
        // Set queue configuration for email processing
        $this->onQueue('emails'); // Use dedicated email queue
        $this->delay(now()->addSeconds(5)); // Small delay to ensure database transaction is committed
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get the appropriate email template based on support form type
        $view = $this->getEmailView();
        
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($this->supportForm->getDestinationEmail())
                    ->subject($this->supportForm->getEmailSubject())
                    ->view($view)
                    ->with([
                        'supportForm' => $this->supportForm,
                        'formType' => $this->supportForm->type,
                        'userName' => $this->supportForm->name,
                        'userEmail' => $this->supportForm->email,
                        'userMessage' => $this->supportForm->message,
                        'submittedAt' => $this->supportForm->created_at,
                    ]);
    }

    /**
     * Get the appropriate email view based on support form type
     *
     * @return string
     */
    private function getEmailView(): string
    {
        return match ($this->supportForm->type) {
            SupportForm::TYPE_MEMBER => 'emails.support.member-support',
            SupportForm::TYPE_APP => 'emails.support.app-support',
            default => 'emails.support.general-support',
        };
    }

    /**
     * Handle a job failure.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        // Log the failure for monitoring
        \Log::error('Support form email failed to send', [
            'support_form_id' => $this->supportForm->id,
            'form_type' => $this->supportForm->type,
            'destination_email' => $this->supportForm->getDestinationEmail(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        // Optionally, you could implement retry logic or notification to admins here
    }
}
