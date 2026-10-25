<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>自分の時間割 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">

    <div class="max-w-6xl mx-auto">

        <!-- タイトル・ログアウト -->
        <div class="flex justify-between items-center mb-6">

            <h1 class="text-3xl font-bold">
                自分の時間割
            </h1>

            <form action="/logout" method="POST">
                @csrf

                <button
                    type="submit"
                    onclick="return confirm('ログアウトしますか？')"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    ログアウト
                </button>
            </form>

        </div>

        <!-- 授業登録・全削除 -->
        <div class="flex justify-between items-center mb-6">

            <a href="/entries/create"
               class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                ＋ 授業を登録する
            </a>

            @if($entries->isNotEmpty())

                <form action="/entries" method="POST">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        onclick="return confirm('本当にすべての授業を削除しますか？この操作は取り消せません。')"
                        class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded">
                        全削除
                    </button>
                </form>

            @endif

        </div>

        <!-- 登録件数 -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">

            <p class="text-gray-600">
                登録している授業
            </p>

            <p class="text-2xl font-bold text-blue-600">
                {{ $entries->count() }} 件
            </p>

        </div>

        <!-- 登録完了メッセージ -->
        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                {{ session('success') }}
            </div>

        @endif

        <!-- 時間割 -->
        @if($entries->isEmpty())

            <p class="bg-white p-6 rounded shadow">
                まだ授業が登録されていません
            </p>

        @else

            <div class="overflow-x-auto bg-white rounded-lg shadow">

                <table class="w-full border-collapse">

                    <thead>
                        <tr class="bg-blue-100">

                            <th class="border p-3">曜日</th>
                            <th class="border p-3">時限</th>
                            <th class="border p-3">授業名</th>
                            <th class="border p-3">担当教員</th>
                            <th class="border p-3">教室</th>

                            <!-- 新しく追加 -->
                            <th class="border p-3">履修人数</th>

                            <th class="border p-3">操作</th>

                        </tr>
                    </thead>

                    <tbody>

                        @foreach($entries as $entry)

                            <tr class="hover:bg-gray-50">

                                <!-- 曜日 -->
                                <td class="border p-3 text-center">
                                    {{ ['月', '火', '水', '木', '金'][$entry->course->day - 1] }}
                                </td>

                                <!-- 時限 -->
                                <td class="border p-3 text-center">
                                    {{ $entry->course->period }}限
                                </td>

                                <!-- 授業名 -->
                                <td class="border p-3">

                                    <a href="{{ route('courses.show', $entry->course_id) }}"
                                       class="text-blue-600 font-bold hover:underline">

                                        {{ $entry->course->name }}

                                    </a>

                                </td>

                                <!-- 担当教員 -->
                                <td class="border p-3 text-center">
                                    {{ $entry->course->teacher ?? '未定' }}
                                </td>

                                <!-- 教室 -->
                                <td class="border p-3 text-center">
                                    {{ $entry->course->room ?? '教室未定' }}
                                </td>

                                <!-- 履修人数 -->
                                <td class="border p-3 text-center">

                                    <a href="{{ route('courses.show', $entry->course_id) }}"
                                       class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-3 py-1 rounded-full hover:bg-blue-200">

                                        👥 {{ $entry->course->timetable_entries_count }}人

                                    </a>

                                </td>

                                <!-- 削除ボタン -->
                                <td class="border p-3 text-center">

                                    <form action="/entries/{{ $entry->id }}"
                                          method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('この授業を削除しますか？')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">

                                            削除

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</body>
</html>