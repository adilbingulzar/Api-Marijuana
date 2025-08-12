<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SobrietyDate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Sobriety Date API Documentation",
 *      description="API for managing sobriety dates"
 * )
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 */
class SobrietyDateController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/sobriety-date",
     *      operationId="createSobrietyDate",
     *      tags={"Sobriety Date"},
     *      summary="Create a new sobriety date entry",
     *      description="Creates a new sobriety date entry or updates if device_id already exists",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"device_id","date"},
     *              @OA\Property(property="device_id", type="string", description="Unique device identifier"),
     *              @OA\Property(property="date", type="string", format="date", description="Date in YYYY-MM-DD format", example="2024-01-15")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Sobriety date created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Sobriety date created successfully"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="device_id", type="string", example="ABC123"),
     *                  @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                  @OA\Property(property="created_at", type="string", format="datetime"),
     *                  @OA\Property(property="updated_at", type="string", format="datetime")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation failed"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:255',
                'date' => 'required|date_format:Y-m-d'
            ]);

            $sobrietyDate = SobrietyDate::updateOrCreate(
                ['device_id' => $validated['device_id']],
                ['date' => $validated['date']]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sobriety date created successfully',
                'data' => $sobrietyDate
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating sobriety date'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/sobriety-date/{device_id}",
     *      operationId="updateSobrietyDate",
     *      tags={"Sobriety Date"},
     *      summary="Update sobriety date",
     *      description="Updates the date for a specific device ID",
     *      @OA\Parameter(
     *          name="device_id",
     *          description="Device ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"date"},
     *              @OA\Property(property="date", type="string", format="date", description="Date in YYYY-MM-DD format", example="2024-01-15")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Sobriety date updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Sobriety date updated successfully"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="device_id", type="string", example="ABC123"),
     *                  @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                  @OA\Property(property="created_at", type="string", format="datetime"),
     *                  @OA\Property(property="updated_at", type="string", format="datetime")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Sobriety not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Sobriety not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation failed"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function update(Request $request, string $deviceId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date_format:Y-m-d|after:today'
            ]);

            $sobrietyDate = SobrietyDate::where('device_id', $deviceId)->first();

            if (!$sobrietyDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sobriety not found'
                ], 404);
            }

            $sobrietyDate->update(['date' => $validated['date']]);

            return response()->json([
                'success' => true,
                'message' => 'Sobriety date updated successfully',
                'data' => $sobrietyDate->fresh()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating sobriety date'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/sobriety-date/{device_id}",
     *      operationId="getSobrietyDate",
     *      tags={"Sobriety Date"},
     *      summary="Get sobriety date",
     *      description="Retrieves the saved date for a specific device ID",
     *      @OA\Parameter(
     *          name="device_id",
     *          description="Device ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Sobriety date retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Sobriety date retrieved successfully"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="device_id", type="string", example="ABC123"),
     *                  @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                  @OA\Property(property="created_at", type="string", format="datetime"),
     *                  @OA\Property(property="updated_at", type="string", format="datetime")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Sobriety not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Sobriety not found")
     *          )
     *      )
     * )
     */
    public function fetch(string $deviceId): JsonResponse
    {
        try {
            $sobrietyDate = SobrietyDate::where('device_id', $deviceId)->first();

            if (!$sobrietyDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sobriety not found'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sobriety date retrieved successfully',
                'data' => $sobrietyDate
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving sobriety date'
            ], 500);
        }
    }
}
