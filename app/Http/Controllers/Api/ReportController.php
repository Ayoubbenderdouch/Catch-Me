<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/reports",
     *     summary="Report a user",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reported_user_id","reason"},
     *             @OA\Property(property="reported_user_id", type="integer"),
     *             @OA\Property(property="reason", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Report submitted")
     * )
     */
    public function reportUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reporter = $request->user();

        // Can't report yourself
        if ($reporter->id === $request->reported_user_id) {
            return response()->json([
                'message' => __('messages.cannot_report_yourself'),
            ], 400);
        }

        // Check if already reported this user
        $existingReport = Report::where('reporter_id', $reporter->id)
            ->where('reported_user_id', $request->reported_user_id)
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => __('messages.already_reported'),
            ], 400);
        }

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $request->reported_user_id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => __('messages.report_submitted'),
            'report' => $report,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/reports",
     *     summary="Get user's reports",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Reports retrieved")
     * )
     */
    public function myReports(Request $request)
    {
        $reports = Report::where('reporter_id', $request->user()->id)
            ->with('reportedUser:id,name,profile_image')
            ->latest()
            ->get();

        return response()->json(['reports' => $reports]);
    }
}
