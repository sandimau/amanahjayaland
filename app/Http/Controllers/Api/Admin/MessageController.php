<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ContactMessage::latest()->paginate(20));
    }

    public function show(ContactMessage $message): JsonResponse
    {
        return response()->json($message);
    }

    public function destroy(ContactMessage $message): JsonResponse
    {
        $message->delete();

        return response()->json(null, 204);
    }
}
