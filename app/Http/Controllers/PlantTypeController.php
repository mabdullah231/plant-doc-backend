<?php

namespace App\Http\Controllers;

use App\Models\PlantType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PlantTypeController extends Controller
{
    /**
     * Add or update a plant type.
     */
    public function allPlantTypes()
    {
        try {

            $plantTypes = PlantType::all();

            return response()->json([
                'message' => 'Plant types Fetched successfully.',
                'data' => $plantTypes,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch plant types.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    public function managePlantType(Request $request)
    {
        try {
            $request->validate([
                'id' => 'nullable|exists:plant_types,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $plantType = PlantType::updateOrCreate(
                ['id' => $request->id],
                $request->only(['name', 'description'])
            );

            return response()->json([
                'message' => 'Plant type saved successfully.',
                'data' => $plantType,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save plant type.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Delete a plant type.
     */
    public function deletePlantType($id)
    {
        try {
            // Find the plant type by ID
            $plantType = PlantType::findOrFail($id);
    
            // Get all questions associated with the plant type
            $questions = $plantType->questions; // Assuming you have a relationship defined in PlantType model
    
            // Loop through each question to detach answers and delete the question
            foreach ($questions as $question) {
                // Get all answers associated with the question
                $answers = $question->answers; // Fetch answers associated with the question
    
                // Delete each answer
                foreach ($answers as $answer) {
                    $answer->delete(); // Delete the answer from the answers table
                }
    
                // Detach answers from the pivot table
                $question->answers()->detach(); // Remove associations in the pivot table
    
                // Delete the question
                $question->delete();
            }
    
            // Finally, delete the plant type
            $plantType->delete();
    
            return response()->json([
                'message' => 'Plant type and associated questions and answers deleted successfully.',
            ], 200); // HTTP 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Plant type not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete plant type.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Fetch all plant types or a specific plant type by ID.
     */
    public function viewPlantType($id = null)
    {
        try {
            if ($id) {
                $plantType = PlantType::findOrFail($id);
                return response()->json([
                    'data' => $plantType,
                ], 200); // HTTP 200 OK
            }

            $plantTypes = PlantType::all();
            return response()->json([
                'data' => $plantTypes,
            ], 200); // HTTP 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Plant type not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch plant types.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}