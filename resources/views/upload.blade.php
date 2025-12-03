<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客情報CSVアップロード</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center pt-20">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-xl">
            <h1 class="text-3xl font-extrabold text-blue-600 tracking-wide mb-6 text-center">
                <span class="border-b-4 border-blue-400 pb-1 px-2">顧客情報 CSV アップロード</span>
            </h1>

        {{-- アップロードフォーム --}}
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" id="csvForm" class="flex flex-col items-center">
            @csrf

            <div class="flex flex-wrap items-center justify-center gap-2">
                <input type="file" name="csv_file" id="csv_file" class="border rounded px-2 py-1" accept=".csv">

                <!-- 選択されたファイル名表示 -->
                <span id="fileName" class="text-gray-700"></span>

                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition shadow">
                    アップロード
                </button>
            </div>

            <!-- バリデーションメッセージ -->
            @error('csv_file')
                <p class="text-red-600 text-sm mt-1 self-start ml-12">{{ $message }}</p>
            @enderror
        </form>

        {{-- CSV処理結果 --}}
        @isset($results)
            <div class="mt-4">
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-green-50 border border-green-200 rounded">
                        <p class="text-sm text-green-800">新規登録</p>
                        <h3 class="text-xl font-bold text-green-700">{{ $results['new_count'] }}</h3>
                    </div>

                    <div class="p-4 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-blue-800">更新</p>
                        <h3 class="text-xl font-bold text-blue-700">{{ $results['update_count'] }}</h3>
                    </div>

                    <div class="p-4 bg-gray-50 border border-gray-300 rounded">
                        <p class="text-sm text-gray-700">変更なし</p>
                        <h3 class="text-xl font-bold text-gray-600">{{ $results['unchanged_count'] }}</h3>
                    </div>

                    <div class="p-4 bg-red-50 border border-red-200 rounded">
                        <p class="text-sm text-red-800">エラー</p>
                        <h3 class="text-xl font-bold text-red-700">{{ $results['error_count'] }}</h3>
                    </div>
                </div>
                    {{-- エラーアコーディオン --}}
                    @if (!empty($results['errors']))
                        <div class="mt-5">

                            <button type="button"
                                onclick="document.getElementById('errorBox').classList.toggle('hidden')"
                                class="text-red-600 font-semibold underline">
                                エラー詳細を表示する
                            </button>

                            <div id="errorBox"
                                class="hidden mt-3 bg-red-50 border border-red-200 rounded p-4 max-h-60 overflow-y-auto">
                                @foreach ($results['errors'] as $err)
                                    <p class="text-sm text-red-700">
                                        行 {{ $err['line'] }}：{{ implode('、', $err['messages']) }}
                                    </p>
                                @endforeach
                            </div>

                        </div>
                    @endif

                </div>
            @endisset

        </div>

    </div>

</body>
</html>
