
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>授業一覧 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-8 rounded shadow">

    <h1 class="text-3xl font-bold mb-6">
        授業一覧
    </h1>

    <div class="flex gap-3 mb-6">
        <a href="/entries/create"
           class="bg-gray-200 px-4 py-2 rounded">
            プルダウンから選ぶ
        </a>

        <a href="/entries/courses"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            授業一覧から選ぶ
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-600 p-4 rounded mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- 授業検索フォーム -->
<form action="/entries/courses" method="GET"
      class="flex gap-3 mb-6">

    <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="授業名・担当教員名で検索"
        class="flex-1 border border-gray-300 rounded px-4 py-2"
    >

    <button
        type="submit"
        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
        🔍 検索
    </button>

    <a href="/entries/courses"
       class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
        クリア
    </a>

</form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-center">
            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-3">曜日</th>
                    <th class="border p-3">時限</th>
                    <th class="border p-3">科目コード</th>
                    <th class="border p-3">授業名</th>
                    <th class="border p-3">担当教員</th>
                    <th class="border p-3">教室</th>
                    <th class="border p-3">操作</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td class="border p-3">
                            {{ ['月', '火', '水', '木', '金'][$course->day - 1] }}
                        </td>

                        <td class="border p-3">
                            {{ $course->period }}
                        </td>

                        <td class="border p-3">
                            {{ $course->code }}
                        </td>

<td class="border p-3 text-left">
    <a href="{{ route('courses.show', $course->id) }}"
       class="text-blue-600 font-bold hover:underline">
        {{ $course->name }}
    </a>
</td>

                        <td class="border p-3">
                            {{ $course->teacher }}
                        </td>

                        <td class="border p-3">
                            {{ $course->room ?? '未定' }}
                        </td>

                        <td class="border p-3">
                            @if (in_array($course->id, $registeredCourseIds))
                                <span class="text-gray-500 font-bold">
                                    登録済み
                                </span>
                            @else
                                <form action="/entries" method="POST">
                                    @csrf

                                    <input type="hidden"
                                           name="course_id"
                                           value="{{ $course->id }}">

                                    <button type="submit"
                                            class="bg-blue-500 text-white px-4 py-2 rounded">
                                        登録
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <a href="/entries"
       class="inline-block mt-6 text-blue-600 underline">
        ← 自分の時間割に戻る
    </a>

</div>

</body>
</html>
