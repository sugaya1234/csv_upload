<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CsvUploadService
{
    public function processCsv($file)
    {
        $results = [
        'total_count'   => 0,
        'new_count'     => 0,
        'update_count'  => 0,
        'unchanged_count'=> 0,
        'error_count' => 0,
        'errors' => []
        ];

        // 一時ファイルのパス
        $filepath = $file->getRealPath();

        // CSV 読み込み準備
        $csv = new \SplFileObject($filepath);
        $csv->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );

        // ヘッダマッピング
        $headerMap = [
            '顧客ID' => 'customer_code',
            '名前' => 'name',
            'メールアドレス' => 'email',
            '電話番号' => 'tel',
            '郵便番号' => 'post_code',
            '住所' => 'address',
            '年齢' => 'age',
        ];

        // ヘッダ処理
        $csv->rewind();
        $headerRow = $csv->current();
        $csvHeader = array_map(fn($h) => $headerMap[$h] ?? null, $headerRow);
        $csv->next(); // データ行から開始

        // CSV 内の重複チェック用
        $csvCustomerCodes = [];
        $csvEmails = [];

        DB::beginTransaction();

        try {
            foreach ($csv as $line => $row) {

                // ヘッダ行をスキップ
                if ($line === 0) continue;

                // $row が配列でなければスキップ
                if (!is_array($row)) continue;

                // 空行スキップ
                if (empty(array_filter($row))) continue;

                $results['total_count']++;

                // 列数チェックを入れる
                if (count($row) !== count($csvHeader)) {
                    $results['error_count']++;
                    $results['errors'][] = [
                        'line' => $line + 1,
                        'messages' => ['行の列数が不正']
                    ];
                    continue;
                }

                $data = [];
                foreach ($csvHeader as $index => $colName) {
                    if ($colName === null) continue;

                    $value = $row[$index] ?? null;
                    if (is_string($value)) {
                            $value = trim($value);

                            // 全角英数字を半角に変換（メール・数字対応）
                            $value = mb_convert_kana($value, 'a');

                            // tel と post_code はさらに全角数字→半角数字
                            if (in_array($colName, ['tel', 'post_code'])) {
                                $value = mb_convert_kana($value, 'n');
                            }

                            // 数値型も文字列に変換
                            if (in_array($colName, ['tel', 'post_code', 'age'])) {
                                $value = (string)$value;
                            }
                        }
                    $data[$colName] = $value;
                }

                // ユニークキー項目の CSV 内重複チェック
                if (!empty($data['customer_code']) && in_array($data['customer_code'], $csvCustomerCodes)) {
                    $results['error_count']++;
                    $results['errors'][] = [
                        'line' => $line + 1,
                        'messages' => ['顧客ID：CSV内で重複']
                    ];
                    continue;
                }
                if (!empty($data['email']) && in_array($data['email'], $csvEmails)) {
                    $results['error_count']++;
                    $results['errors'][] = [
                        'line' => $line + 1,
                        'messages' => ['メールアドレス：CSV内で重複']
                    ];
                    continue;
                }

                // バリデーション
                $validator = Validator::make(
                    $data,
                    [
                        'name' => 'required|string|max:50',
                        'email' => 'required|email|max:100',
                        'tel' => ['nullable', 'regex:/^\d{10,11}$/'],
                        'post_code' => ['nullable', 'regex:/^\d{7}$/'],
                        'address' => 'nullable|max:200',
                        'age' => 'nullable|integer|min:0',
                    ],
                    [
                        'name.required' => '名前：必須項目未入力',
                        'name.string' => '名前：文字列のみ',
                        'name.max' => '名前：字数制限超過',
                        'email.required' => 'メール：必須項目未入力',
                        'email.email' => 'メール：形式不正',
                        'email.max' => 'メール：字数制限超過',
                        'tel.regex'    => '電話番号：数字のみ',
                        'tel.digits_between' => '電話番号：桁数不正',
                        'post_code.regex'    => '郵便番号：数字のみ',
                        'post_code.digits' => '郵便番号：桁数不正',
                        'address.max' => '住所：字数制限超過',
                        'age.integer' => '年齢：数字のみ',
                        'age.min' => '年齢：0以上',
                    ]
                );

                if ($validator->fails()) {
                    $results['error_count']++;
                    $results['errors'][] = [
                        'line' => $line + 1,
                        'messages' => $validator->errors()->all(),
                    ];
                    continue;
                }

                // customer_code が空の場合割り振り
                $isNew = empty($data['customer_code']);
                if ($isNew) {
                    // 最新 customer_code をロックして取得
                    $lastCode = User::lockForUpdate()->orderBy('id', 'desc')->value('customer_code');
                    $number = $lastCode ? (int)substr($lastCode, 2) + 1 : 1;
                    $data['customer_code'] = 'UN' . str_pad($number, 5, '0', STR_PAD_LEFT);
                }

                // DB 照合・更新 or 新規登録
                if ($isNew) {
                    // 新規登録
                    $emailExists = User::where('email', $data['email'])->exists();
                    if ($emailExists) {
                        $results['error_count']++;
                        $results['errors'][] = [
                            'line' => $line + 1,
                            'messages' => ['メール：重複、登録済み']
                        ];
                        continue;
                    }

                    User::create($data);
                    $results['new_count']++;
                } else {
                    // 既存更新
                    $existingUser = User::where('customer_code', $data['customer_code'])->first();
                    if (!$existingUser) {
                        $results['error_count']++;
                        $results['errors'][] = [
                            'line' => $line + 1,
                            'messages' => ['顧客ID：DBに存在なし']
                        ];
                        continue;
                    }

                    // メール重複チェック（他の顧客コードに同じメールが存在するか）
                    $emailConflict = User::where('email', $data['email'])
                        ->where('customer_code', '!=', $data['customer_code'])
                        ->exists();

                    if ($emailConflict) {
                        $results['error_count']++;
                        $results['errors'][] = [
                            'line' => $line + 1,
                            'messages' => ['メール：重複、登録済み']
                        ];
                        continue;
                    }

                    // 差分チェック
                    $changed = false;
                    foreach ($data as $key => $value) {
                        if ($key === 'customer_code') continue;
                        if ($existingUser->$key != $value) {
                            $changed = true;
                            break;
                        }
                    }

                    if ($changed) {
                        $existingUser->update($data);
                        $results['update_count']++;
                    } else {
                        $results['unchanged_count']++;
                    }
                }

                // CSV 内重複チェック用に追加
                $csvCustomerCodes[] = $data['customer_code'];
                $csvEmails[] = $data['email'];
            }

            DB::commit(); // 例外なしなら確定
        } catch (\Exception $e) {
                DB::rollBack(); // エラーがあった場合は全件ロールバック
                throw $e;
        }

        return $results;
    }
}
