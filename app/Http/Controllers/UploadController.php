<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index()
    {
        // アップロード画面を返す
        return view('upload');
    }

    public function store(Request $request)
    {
        // ファイルアップロード処理（後で実装）
    }
}
