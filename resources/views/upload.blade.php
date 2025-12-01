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
    </div>
</body>
</html>
