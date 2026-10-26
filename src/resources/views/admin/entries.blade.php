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

    <!-- 承認待ちの申請一覧 -->
    <h2 class="text-2xl font-bold mb-4">
        📋 承認待ちの申請一覧
    </h2>

    <div class="bg-white rounded-lg shadow overflow-x-auto mb-10">

        <table class="w-full text-left">

            <thead class="bg-blue-100">
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

                        <!-- 申請者 -->
                        <td class="p-4">
                            {{ $entry->user->name }}
                        </td>

                        <!-- 授業名 -->
                        <td class="p-4">
                            {{ $entry->course->name }}
                        </td>

                        <!-- 申請状況 -->
                        <td class="p-4">
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-bold">
                                🟡 承認待ち
                            </span>
                        </td>

                        <!-- 承認・却下ボタン -->
                        <td class="p-4">

                            <div class="flex gap-3">

                                <!-- 承認 -->
                                <form
                                    action="{{ route('admin.entries.approve', $entry->id) }}"
                                    method="POST">

                                    @csrf

                                    <button
                                        type="submit"
                                        onclick="return confirm('この履修申請を承認しますか？')"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">

                                        ✓ 承認

                                    </button>
                                </form>

                                <!-- 却下 -->
                                <form
                                    action="{{ route('admin.entries.reject', $entry->id) }}"
                                    method="POST">

                                    @csrf

                                    <button
                                        type="submit"
                                        onclick="return confirm('この履修申請を却下しますか？')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">

                                        ✕ 却下

                                    </button>
                                </form>

                            </div>

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

    <!-- 授業ごとの定員状況 -->
    <h2 class="text-2xl font-bold mb-4">
        👥 授業ごとの定員状況
    </h2>

    <div class="bg-white rounded-lg shadow overflow-x-auto">

        <table class="w-full text-left">

            <thead class="bg-green-100">
                <tr>
                    <th class="p-4">授業名</th>
                    <th class="p-4">承認済み人数</th>
                    <th class="p-4">定員</th>
                    <th class="p-4">空き状況</th>
                </tr>
            </thead>

            <tbody>
                @foreach($courses as $course)

                    <tr class="border-t">

                        <td class="p-4">
                            {{ $course->name }}
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

                    </tr>

                @endforeach
            </tbody>

        </table>

    </div>

</div>

</body>
</html>