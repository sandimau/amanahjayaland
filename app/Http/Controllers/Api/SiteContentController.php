<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteContentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['page' => ['required', Rule::in(SiteContent::PAGES)]]);

        return response()->json(SiteContent::whereIn('page', ['global', $data['page']])
            ->orderByRaw("CASE WHEN page = 'global' THEN 0 ELSE 1 END")
            ->get(['page', 'selector', 'kind', 'node_index', 'value']))
            ->header('Cache-Control', 'no-store');
    }
}
