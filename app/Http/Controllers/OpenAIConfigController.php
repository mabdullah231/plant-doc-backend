<?php

namespace App\Http\Controllers;

use App\Models\OpenAIConfig;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class OpenAIConfigController extends Controller
{
    /**
     * Add or update OpenAI configuration.
     */
    public function manageOpenAIConfig(Request $request)
    {
        try {
            $request->validate([
                'api_key' => 'required|string',
                'model' => 'required|string',
                'temperature' => 'required|numeric|between:0,1',
            ]);

            // Fetch the existing config or create a new one
            $config = OpenAIConfig::firstOrNew([]);

            // Update the config
            $config->api_key = $request->api_key;
            $config->model = $request->model;
            $config->temperature = $request->temperature;
            $config->save();

            return response()->json([
                'message' => 'OpenAI configuration saved successfully.',
                'data' => $config,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save OpenAI configuration.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Fetch the current OpenAI configuration.
     */
    public function viewOpenAIConfig()
    {
        try {
            $config = OpenAIConfig::first();

            // Set default values if no config exists
            if (!$config) {
                $config = [
                    'api_key' => '',
                    'model' => 'gpt-3.5-turbo', // Default free model
                    'temperature' => 0.5, // Default temperature
                ];
            }

            return response()->json([
                'data' => $config,
            ], 200); // HTTP 200 OK
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'OpenAI configuration not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch OpenAI configuration.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    public function generateContent(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'text' => 'required|string',
                'plant_type_id' => 'required|exists:plant_types,id',
            ]);

            // Fetch the OpenAI configuration
            $config = OpenAIConfig::firstOrFail();
            $apiKey = $config->api_key;

            // Extract plant name and assessment responses from input
            $userInput = $request->text;

            // Create a detailed, expert prompt that will generate properly formatted responses
//             $expertPrompt = <<<EOT
// You are PlantDoc AI, an expert botanist and plant pathologist with decades of experience diagnosing plant diseases and health issues. 
// Your role is to analyze the provided plant assessment and identify the most likely disease or health issue affecting the plant.

// Here's the plant assessment information:

// $userInput

// Please provide a detailed, well-structured diagnosis and treatment plan, formatted as markdown with the following sections:

// ## Summary of Symptoms
// - Summarize the key symptoms reported in the assessment

// ## Diagnosis
// - Identify the most likely disease or health issue affecting the plant
// - Explain why this diagnosis fits the symptoms (evidence-based reasoning)
// - If there are multiple possible issues, rank them from most to least likely

// ## Treatment Plan
// - Provide specific, actionable steps to treat the identified issue
// - Include organic/natural options when available
// - Include preventative measures for future health

// ## Additional Care Tips
// - Offer 2-3 specific tips for general care of this plant type that could help prevent future issues

// Your response must be clear, professional, and follow proper markdown formatting with headings, bullet points, and emphasis where appropriate. The response should be comprehensive but focused specifically on the most likely diagnosis rather than covering every possibility.
// EOT;
$expertPrompt = <<<EOT
You are PlantDoc AI, an expert botanist and plant pathologist with decades of experience diagnosing plant diseases and health issues. 
Your role is to analyze the provided plant assessment and identify the most likely disease or health issue affecting the plant.

Here's the plant assessment information:

$userInput

Provide a concise, well-structured diagnosis and treatment plan, formatted as markdown with the following sections:

## Summary of Symptoms  
- Briefly summarize the key symptoms reported in the assessment.  

## Diagnosis  
- Identify the most likely disease or health issue affecting the plant.  
- Provide a brief explanation of why this diagnosis fits the symptoms (evidence-based reasoning).  
- If multiple issues are possible, rank them from most to least likely in a single sentence.  

## Treatment Plan  
- List specific, actionable steps to treat the identified issue.  
- Prioritize organic/natural options when available.  
- Include 1-2 key preventative measures for future health.  

## Additional Care Tips  
- Offer 1-2 specific tips for general care of this plant type to prevent future issues.  

Your response must be clear, professional, and concise. Use markdown formatting with headings, bullet points, and emphasis where appropriate. Focus on the most likely diagnosis and avoid unnecessary details.
EOT;

            // Create the payload for the API request using the new format
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $expertPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topP' => 0.95,
                    'topK' => 40
                ]
            ];

            // Send a POST request to the Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", $payload);

            // Check if the response is successful
            // if ($response->successful()) {
            //     // Return the content from the response
            //     return response()->json([
            //         'data' => $response->json()['candidates'][0]['content']['parts'][0]['text'],
            //     ], 200); // HTTP 200 OK
            // } else {
            //     return response()->json([
            //         'message' => 'Failed to generate content.',
            //         'error' => $response->json(),
            //     ], 500); // HTTP 500 Internal Server Error
            // }
            if ($response->successful()) {
                $aiDiagnosis = $response->json()['candidates'][0]['content']['parts'][0]['text'];
                
                // Save the AI diagnosis to the user_reports table
                UserReport::create([
                    'user_id' => Auth::id(),  // Get authenticated user ID
                    'ai_diagnosis' => $aiDiagnosis, // Store AI-generated text
                    'plant_type_id' => $request->plant_type_id, // Store AI-generated text
                ]);
            
                return response()->json([
                    'message' => 'Diagnosis saved successfully.',
                    'data' => $aiDiagnosis,
                ], 200); // HTTP 200 OK
            } else {
                return response()->json([
                    'message' => 'Failed to generate content.',
                    'error' => $response->json(),
                ], 500); // HTTP 500 Internal Server Error
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'OpenAI configuration not found.',
                'error' => $e->getMessage(),
            ], 404); // HTTP 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while generating content.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}
