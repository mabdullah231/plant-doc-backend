<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionController extends Controller
{
    /**
     * Add or update multiple questions.
     */
    public function manageQuestion(Request $request)
    {
        try {
            $request->validate([
                'questions' => 'required|array',
                'questions.*.id' => 'nullable|exists:questions,id',
                'questions.*.plant_type_id' => 'required|exists:plant_types,id',
                'questions.*.question_text' => 'required|string',
                'questions.*.order' => 'nullable|integer',
            ]);

            $savedQuestions = [];

            foreach ($request->questions as $questionData) {
                $question = Question::updateOrCreate(
                    ['id' => $questionData['id']],
                    $questionData
                );
                $savedQuestions[] = $question;
            }

            return response()->json([
                'message' => 'Questions saved successfully.',
                'data' => $savedQuestions,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save questions.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion($id)
    {
        try {
            // Find the question by ID
            $question = Question::findOrFail($id);
    
            // Delete associated answers
            $question->answers()->detach(); // Detach answers from the pivot table
    
            // Optionally, delete the answers themselves if you want to remove them from the database
            // $question->answers()->delete(); // Uncomment this line if you want to delete the answers
    
            // Delete the question
            $question->delete();
    
            return response()->json([
                'message' => 'Question and associated answers deleted successfully.',
            ], 200); // HTTP 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Question not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete question.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Fetch all questions or a specific question by ID.
     */
    public function viewQuestion($plantTypeId)
    {
        try {
            // Fetch questions associated with the given plant type ID
            $questions = Question::with('answers')->where('plant_type_id', $plantTypeId)->get();
    
            return response()->json([
                'data' => $questions,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch questions.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}