<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AnswerController extends Controller
{
    /**
     * Add or update an answer.
     */
    public function manageAnswer(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'nullable|exists:answers,id',
            'answers.*.question_id' => 'required|exists:questions,id', // Validate question_id
            'answers.*.answer_text' => 'required|string',
        ]);
    
        $savedAnswers = [];
    
        foreach ($request->answers as $answerData) {
            // Create or update the answer
            $answer = Answer::updateOrCreate(
                ['id' => $answerData['id']],
                $answerData // Use the entire answer data
            );
    
            // Attach the answer to the question in the pivot table
            $answer->questions()->syncWithoutDetaching([$answerData['question_id']]);
    
            $savedAnswers[] = $answer; // Collect saved answers
        }
    
        return response()->json([
            'message' => 'Answers saved successfully.',
            'data' => $savedAnswers,
        ], 200); // HTTP 200 OK
    }
    /**
     * Delete an answer.
     */
    public function deleteAnswer($id)
    {
        try {
            $answer = Answer::findOrFail($id);
            $answer->delete();

            return response()->json([
                'message' => 'Answer deleted successfully.',
            ], 200); // HTTP 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Answer not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete answer.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Fetch all answers for a specific question by its ID.
     */
    public function viewAnswer($questionId)
    {
        try {
            // Fetch answers associated with the given question ID
            $answers = Answer::where('question_id', $questionId)->get();

            return response()->json([
                'data' => $answers,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch answers.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}