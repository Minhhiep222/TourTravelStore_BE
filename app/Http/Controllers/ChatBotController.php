<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Gemini\Laravel\Facades\Gemini;  

class ChatBotController extends Controller
{
    public function chatbot(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'message' => 'required',
            ], [
                'message.required' => 'Please enter your question',
            ]);
            $question = $request->message;
            $result = Gemini::geminiPro()->generateContent($question);
            $result->text();
            return response()->json([
                "message" => $result,
            ], 200);
        }
         catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
