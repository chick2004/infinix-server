<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostTag;

class PostTagController extends Controller
{
    public function index()
    {
        $tags = PostTag::select('tag')
            ->selectRaw('count(tag) as count')
            ->groupBy('tag')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($tags);
    }

    
}
