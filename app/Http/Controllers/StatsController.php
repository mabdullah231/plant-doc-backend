<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PlantType;
use App\Models\UserReport;
use App\Models\Question;

class StatsController extends Controller
{
    /**
     * Get Admin Stats: Count rows in User, PlantType, UserReport, and Questions grouped by plant_type_id.
     */
    public function adminStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_plant_types' => PlantType::count(),
            'total_user_reports' => UserReport::count(),
            'questions_by_plant_type' => Question::join('plant_types', 'questions.plant_type_id', '=', 'plant_types.id')
                ->selectRaw('questions.plant_type_id, plant_types.name as plant_name, COUNT(*) as total')
                ->groupBy('questions.plant_type_id', 'plant_types.name')
                ->get(),
        ], 200);
    }
    

    /**
     * Get User Stats: Fetch UserReports for the authenticated user.
     */
    public function userStats()
    {
        $userId = Auth::id();
    
        return response()->json([
            'total_reports' => UserReport::where('user_id', $userId)->count(),
            'recent_reports' => UserReport::where('user_id', $userId)->latest()->take(5)->get(),
        ], 200);
    }
    
}
