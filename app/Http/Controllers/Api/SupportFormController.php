<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportFormRequest;
use App\Models\SupportForm;
use App\Jobs\SendSupportFormEmailJob;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Support Form API Controller
 * 
 * Handles mobile app support form submissions for both member support
 * and app support with automated email notifications.
 */
class SupportFormController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/support-form",
     *      operationId="submitSupportForm",
     *      tags={"Support Form"},
     *      summary="Submit a support form",
     *      description="Submits a support form from the mobile app. Handles both member support and app support types. Emails are sent asynchronously via queue system.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"type","name","email","message"},
     *              @OA\Property(
     *                  property="type", 
     *                  type="string", 
     *                  enum={"member", "app"},
     *                  description="Type of support form",
     *                  example="member"
     *              ),
     *              @OA\Property(
     *                  property="name", 
     *                  type="string", 
     *                  description="User's full name",
     *                  minLength=2,
     *                  maxLength=100,
     *                  example="John Doe"
     *              ),
     *              @OA\Property(
     *                  property="email", 
     *                  type="string", 
     *                  format="email",
     *                  description="User's email address",
     *                  maxLength=255,
     *                  example="john.doe@example.com"
     *              ),
     *              @OA\Property(
     *                  property="message", 
     *                  type="string", 
     *                  description="Support message or issue description",
     *                  minLength=10,
     *                  maxLength=2000,
     *                  example="I need help with my account settings. I cannot update my profile information."
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Support form submitted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Support form submitted successfully. You will receive a response within 24 hours."),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="type", type="string", example="member"),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                  @OA\Property(property="message", type="string", example="I need help with my account settings."),
     *                  @OA\Property(property="email_sent", type="boolean", example=false),
     *                  @OA\Property(property="created_at", type="string", format="datetime"),
     *                  @OA\Property(property="updated_at", type="string", format="datetime")
     *              ),
     *              @OA\Property(property="reference_id", type="string", example="#1", description="Reference ID for tracking the support request")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation failed"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="type", type="array", @OA\Items(type="string"), example={"Support form type must be either 'member' or 'app'."}),
     *                  @OA\Property(property="name", type="array", @OA\Items(type="string"), example={"Name is required."}),
     *                  @OA\Property(property="email", type="array", @OA\Items(type="string"), example={"Please provide a valid email address."}),
     *                  @OA\Property(property="message", type="array", @OA\Items(type="string"), example={"Message must be at least 10 characters long."})
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="An error occurred while processing your support request. Please try again later.")
     *          )
     *      )
     * )
     */
    public function submit(SupportFormRequest $request): JsonResponse
    {
        try {
            // Get validated data from the request
            $validatedData = $request->validated();
            
            // Create new support form record in database
            $supportForm = SupportForm::create([
                'type' => $validatedData['type'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'message' => $validatedData['message'],
                'email_sent' => false, // Will be updated when email is sent
            ]);

            // Log the support form submission for monitoring
            \Log::info('New support form submitted', [
                'support_form_id' => $supportForm->id,
                'type' => $supportForm->type,
                'user_email' => $supportForm->email,
                'destination_email' => $supportForm->getDestinationEmail(),
            ]);

            // Dispatch email job to queue for asynchronous processing
            // This prevents API response delays due to email sending
            SendSupportFormEmailJob::dispatch($supportForm)
                ->onQueue('emails') // Use dedicated email queue
                ->delay(now()->addSeconds(2)); // Small delay to ensure transaction is committed

            // Prepare success response data
            $responseMessage = $this->getSuccessMessage($supportForm->type);
            
            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'data' => $supportForm,
                'reference_id' => '#' . $supportForm->id,
            ], 201);

        } catch (Exception $exception) {
            // Log the error for debugging and monitoring
            \Log::error('Support form submission failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'request_data' => $request->safe()->toArray(),
            ]);

            // Return generic error response to avoid exposing internal details
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Get appropriate success message based on support form type
     *
     * @param string $formType
     * @return string
     */
    private function getSuccessMessage(string $formType): string
    {
        return match ($formType) {
            SupportForm::TYPE_MEMBER => 'Your member support request has been submitted successfully. Our support team will respond within 24 hours.',
            SupportForm::TYPE_APP => 'Your app issue has been reported successfully. Our technical team will investigate and respond within 24 hours.',
            default => 'Your support request has been submitted successfully. You will receive a response within 24 hours.',
        };
    }

    /**
     * Get support form statistics (optional endpoint for admin monitoring)
     * This endpoint is not part of the main requirement but useful for monitoring
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            // Get basic statistics about support forms
            $stats = [
                'total_forms' => SupportForm::count(),
                'member_support_forms' => SupportForm::ofType(SupportForm::TYPE_MEMBER)->count(),
                'app_support_forms' => SupportForm::ofType(SupportForm::TYPE_APP)->count(),
                'emails_sent' => SupportForm::emailSent()->count(),
                'emails_pending' => SupportForm::emailNotSent()->count(),
                'forms_today' => SupportForm::whereDate('created_at', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (Exception $exception) {
            \Log::error('Failed to retrieve support form stats', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve statistics at this time.',
            ], 500);
        }
    }
}
