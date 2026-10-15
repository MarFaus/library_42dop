<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reader;

class ReaderController extends Controller
{
    public function index()
    {
        return view('readers.index', [
            'readers' => Reader::withCount('activeBorrowings')->orderBy('name')->paginate(12),
        ]);
    }
}
