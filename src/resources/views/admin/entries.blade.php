<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>履修申請管理 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto">

    <!-- タイトル -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">
            👑 履修申請管理
        </h1>

        <a href="/entries"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            ← 自分の時間割に戻る
        </a>
    </div>

    <!-- 成功メッセージ -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- エラーメッセージ -->
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- 承認待ち件数 -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <p class="text-gray-600">承認待ちの申請</p>
        <p class="text-3xl font-bold text-yellow-600">
            {{ $pending->count() }} 件
        </p>
    </div>

    <!-- 授業ごとの抽選管理 -->
    <h2 class="text-2xl font-bold mb-4">
        🎲 授業ごとの抽選管理
    </h2>

    <div class="bg-white rounded-lg shadow overflow-x-auto mb-10">
        <table class="w-full text-left">

            <thead class="bg-green-100">
                <tr>
                    <th class="p-4">授業名</th>
                    <th class="p-4">承認済み人数</th>
                    <th class="p-4">定員</th>
                    <th class="p-4">空き状況</th>
                    <th class="p-4">申請締切</th>
                    <th class="p-4">締切設定</th>
                    <th class="p-4">抽選</th>
                </tr>
            </thead>

            <tbody>
                @foreach($courses as $course)
                    <tr class="border-t hover:bg-gray-50">

                        <td class="p-4 font-bold">
                            {{ $course->name }}

                            @if($course->required)
                                <div class="text-xs text-blue-600 mt-1">
                                    必修授業
                                </div>
                            @endif
                        </td>

                        <td class="p-4">
                            {{ $course->approved_count }} 人
                        </td>

                        <td class="p-4">
                            {{ $course->capacity }} 人
                        </td>

                        <td class="p-4">
                            @if($course->approved_count >= $course->capacity)
                                <span class="text-red-600 font-bold">
                                    満員
                                </span>
                            @else
                                <span class="text-green-600 font-bold">
                                    残り {{ $course->capacity - $course->approved_count }} 人
                                </span>
                            @endif
                        </td>

                        <!-- 申請締切 -->
                        <td class="p-4">
                            @if($course->application_deadline)
                                {{ $course->application_deadline->format('Y/m/d H:i') }}
                            @else
                                <span class="text-gray-400">
                                    未設定
                                </span>
                            @endif

                            @if($course->lottery_completed_at)
                                <div class="text-green-600 font-bold text-sm mt-1">
                                    ✓ 抽選完了
                                </div>
                            @endif
                        </td>

                        <!-- 締切設定 -->
                        <td class="p-4">
                            @if(!$course->required && !$course->lottery_completed_at)

                                <form
                                    action="{{ route('admin.courses.deadline', $course->id) }}"
                                    method="POST"
                                    class="flex flex-col gap-2">

                                    @csrf

                                    <input
                                        type="datetime-local"
                                        name="application_deadline"
                                        value="{{ $course->application_deadline?->format('Y-m-d\TH:i') }}"
                                        required
                                        class="border rounded px-2 py-2">

                                    <button
                                        type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded">
                                        締切を保存
                                    </button>
                                </form>

                            @else
                                <span class="text-gray-500">
                                    変更不可
                                </span>
                            @endif
                        </td>

                        <!-- 抽選実行 -->
                        <td class="p-4">
                            @if($course->required)

                                <span class="text-gray-400">
                                    対象外
                                </span>

                            @elseif($course->lottery_completed_at)

                                <span class="text-green-600 font-bold">
                                    ✓ 抽選済み
                                </span>

                            @elseif(!$course->application_deadline)

                                <span class="text-gray-500">
                                    締切未設定
                                </span>

                            @elseif(now()->lessThan($course->application_deadline))

                                <span class="text-yellow-600 font-bold">
                                    締切前
                                </span>

                            @else

                                <form
                                    action="{{ route('admin.courses.lottery', $course->id) }}"
                                    method="POST">

                                    @csrf

                                    <button
                                        type="submit"
                                        onclick="return confirm('この授業の履修抽選を実行しますか？抽選後はやり直せません。')"
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded whitespace-nowrap">
                                        🎲 抽選実行
                                    </button>
                                </form>

                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 承認待ちの申請一覧 -->
    <h2 class="text-2xl font-bold mb-4">
        📋 承認待ちの申請一覧
    </h2>

    <div class="bg-white rounded-lg shadow overflow-x-auto mb-10">
        <table class="w-full text-left">

            <thead class="bg-green-100">
                <tr>
                    <th class="p-4">申請者</th>
                    <th class="p-4">授業名</th>
                    <th class="p-4">申請状況</th>
                    <th class="p-4">操作</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pending as $entry)

                    <tr class="border-t hover:bg-gray-50">

                        <td class="p-4">
                            {{ $entry->user->name }}
                        </td>

                        <td class="p-4">
                            {{ $entry->course->name }}
                        </td>

                        <td class="p-4">
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-bold">
                                🟡 承認待ち
                            </span>
                        </td>

                        <td class="p-4">
                            @if(
                                $entry->course->required ||
                                $entry->course->lottery_completed_at
                            )
                                <span class="text-gray-500">
                                    操作不可
                                </span>
                            @else
                                <span class="text-purple-600 font-bold">
                                    抽選待ち
                                </span>
                            @endif
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500">
                            現在、承認待ちの申請はありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

</body>
</html>
