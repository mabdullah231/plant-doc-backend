<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends Controller
{
    /**
     * Add or update a questionnaire (questions & answers for a plant type).
     */
    public function storeOrUpdateQuestionnaire(Request $request)
    {
        try {
            $request->validate([
                'plant_type_id' => 'required|exists:plant_types,id',
                'questions' => 'required|array',
                'questions.*.id' => 'nullable|exists:questions,id',
                'questions.*.question_text' => 'required|string',
                'questions.*.order' => 'nullable|integer',
                'questions.*.answers' => 'required|array',
                'questions.*.answers.*.id' => 'nullable|exists:answers,id',
                'questions.*.answers.*.answer_text' => 'required|string',
            ]);

            DB::beginTransaction();

            $savedQuestions = [];

            foreach ($request->questions as $questionData) {
                // Create or update question
                $question = Question::updateOrCreate(
                    ['id' => $questionData['id'] ?? null],
                    [
                        'plant_type_id' => $request->plant_type_id,
                        'question_text' => $questionData['question_text'],
                        'order' => $questionData['order'] ?? null,
                    ]
                );

                $savedAnswers = [];

                foreach ($questionData['answers'] as $answerData) {
                    // Create or update answer
                    $answer = Answer::updateOrCreate(
                        ['id' => $answerData['id'] ?? null],
                        ['answer_text' => $answerData['answer_text']]
                    );

                    $savedAnswers[] = $answer->id;
                }

                // Sync answers with the question using the correct pivot table
                $question->answers()->sync($savedAnswers);

                $savedQuestions[] = $question->load('answers');
            }

            DB::commit();

            return response()->json([
                'message' => 'Questionnaire saved successfully.',
                'data' => $savedQuestions,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to save questionnaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a questionnaire (all questions & answers under a plant type).
     */
    public function deleteQuestionnaire($plantTypeId)
    {
        try {
            DB::beginTransaction();

            // Fetch questions linked to the given plant type
            $questions = Question::where('plant_type_id', $plantTypeId)->get();

            if ($questions->isEmpty()) {
                return response()->json([
                    'message' => 'No questionnaire found for the given plant type.',
                ], 200); // Return 200 with a message
            }

            // Collect answer IDs before deleting questions
            $answerIds = [];

            foreach ($questions as $question) {
                $answerIds = array_merge($answerIds, $question->answers()->pluck('answers.id')->toArray());
                $question->answers()->detach(); // Remove pivot table relationships
                $question->delete(); // Delete the question
            }

            // Delete answers that are no longer linked to any questions
            Answer::whereNotIn('id', DB::table('question_answer')->pluck('answer_id'))->delete();

            DB::commit();

            return response()->json([
                'message' => 'Questionnaire deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete questionnaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch all questions & answers under a plant type.
     */
    public function viewQuestionnaire($plantTypeId = null)
    {
        try {
            $query = Question::with('answers', 'plantType'); // Include related plant type

            if ($plantTypeId) {
                $query->where('plant_type_id', $plantTypeId);
            }

            $questions = $query->get();

            if ($questions->isEmpty()) {
                return response()->json([
                    'message' => 'No questionnaire found.',
                ], 200); // Return 200 with a message
            }

            return response()->json([
                'plant_details' => $plantTypeId ? $questions->first()->plantType : null,
                'questions' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch questionnaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch all questionnaires.
     */
    public function getAllQuestionnaires()
    {
        try {
            $questionnaires = Question::with('answers', 'plantType')->get();

            if ($questionnaires->isEmpty()) {
                return response()->json([
                    'message' => 'No questionnaires found.',
                ], 200); // Return 200 with a message
            }

            return response()->json([
                'questionnaires' => $questionnaires,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch questionnaires.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}