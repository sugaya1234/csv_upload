<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客情報CSVアップロード</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4">顧客情報CSVアップロード</h1>

        {{-- アップロードフォーム --}}
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="block mb-2 font-medium" for="csv_file">CSVファイル選択</label>
            <input type="file" name="csv_file" id="csv_file"
                class="mb-4 w-full border rounded px-2 py-1" accept=".csv">
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                アップロード
            </button>
        </form>

        {{-- CSV処理結果 --}}
        @isset($results)
            <div class="mt-4">
                <p>総行数: {{ $results['total_count'] }}</p>
                <p>新規登録: {{ $results['new_count'] }}</p>
                <p>更新: {{ $results['update_count'] }}</p>
                <p>変更なし: {{ $results['unchanged_count'] }}</p>
                <p>エラー件数: {{ $results['error_count'] }}</p>

                @if (!empty($results['errors']))
                    <div class="mt-2 text-red-600">
                        <h2 class="font-bold">エラー詳細</h2>
                        <ul class="list-disc ml-5">
                            @foreach ($results['errors'] as $err)
                                <li>行 {{ $err['line'] }}: {{ implode(', ', $err['messages']) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endisset

    </div>
</body>
</html>
