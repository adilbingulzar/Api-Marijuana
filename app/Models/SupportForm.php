<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupportForm extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'message',
        'email_sent',
        'email_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Support form types
     */
    const TYPE_MEMBER = 'member';
    const TYPE_APP = 'app';

    /**
     * Get all valid support form types
     *
     * @return array
     */
    public static function getValidTypes(): array
    {
        return [self::TYPE_MEMBER, self::TYPE_APP];
    }

    /**
     * Get the email address where this support form should be sent
     *
     * @return string
     */
    public function getDestinationEmail(): string
    {
        return match ($this->type) {
            self::TYPE_MEMBER => env('MEMBER_SUPPORT_EMAIL'),
            self::TYPE_APP => env('APP_SUPPORT_EMAIL'),
            default => env('MEMBER_SUPPORT_EMAIL'), // fallback
        };
    }

    /**
     * Get the subject line for the email based on form type
     *
     * @return string
     */
    public function getEmailSubject(): string
    {
        return match ($this->type) {
            self::TYPE_MEMBER => 'New Member Support Request from ' . $this->name,
            self::TYPE_APP => 'New App Support Issue from ' . $this->name,
            default => 'New Support Request from ' . $this->name,
        };
    }

    /**
     * Mark the email as sent
     *
     * @return void
     */
    public function markEmailSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => Carbon::now(),
        ]);
    }

    /**
     * Scope to filter by form type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get forms where email was sent
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmailSent($query)
    {
        return $query->where('email_sent', true);
    }

    /**
     * Scope to get forms where email was not sent
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmailNotSent($query)
    {
        return $query->where('email_sent', false);
    }
}
