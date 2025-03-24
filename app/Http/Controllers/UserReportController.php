<?php

namespace App\Http\Controllers;

use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    /**
     * Add or update a user report.
     */
    public function manageUserReport(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:user_reports,id',
            'user_id' => 'required|exists:users,id',
            'plant_type_id' => 'required|exists:plant_types,id',
            'question_ids' => 'required|array',
            'answer_ids' => 'required|array',
            'ai_diagnosis' => 'nullable|string',
            'treatment_recommendations' => 'nullable|string',
            'severity_level' => 'nullable|string',
        ]);

        $userReport = UserReport::updateOrCreate(
            ['id' => $request->id],
            $request->only([
                'user_id',
                'plant_type_id',
                'ai_diagnosis',
                'treatment_recommendations',
                'severity_level',
            ])
        );

        // Attach questions and answers
        $userReport->questions()->sync($request->question_ids);
        $userReport->answers()->sync($request->answer_ids);

        return response()->json([
            'message' => 'User report saved successfully.',
            'data' => $userReport,
        ]);
    }

    /**
     * Delete a user report.
     */
    public function deleteUserReport($id)
    {
        $userReport = UserReport::findOrFail($id);
        $userReport->delete();

        return response()->json([
            'message' => 'User report deleted successfully.',
        ]);
    }

    /**
     * Fetch all user reports or a specific report by ID.
     */
    public function viewUserReports()
    {
        $userReports = UserReport::with(['plantType'])->where('user_id',Auth::id())->get();
        return response()->json([
            'data' => $userReports,
        ]);
    }
}