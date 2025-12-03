<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CsvUploadService;

class UploadController extends Controller
{
    public function index()
    {
        // アップロード画面を返す
        return view('upload');
    }

    public function store(Request $request, CsvUploadService $csvUploadService)
    {
        // ファイルアップロード処理
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt',
    ], [
        'csv_file.required' => 'CSVファイルを選択してください。',
        'csv_file.file'     => '有効なファイルを選択してください。',
        'csv_file.mimes'    => 'CSV形式のファイルを選択してください。',
    ]);

        $file = $request->file('csv_file');
        $results = $csvUploadService->processCsv($file);

        return view('upload', compact('results'));
    }
}
