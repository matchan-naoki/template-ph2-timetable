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

    <!-- タイトル -->
    <h1 class="text-3xl font-bold mb-6">
        授業一覧
    </h1>

    <!-- 授業登録方法 -->
    <div class="flex gap-3 mb-6">

        <a href="/entries/create"
           class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
            プルダウンから選ぶ
        </a>

        <a href="/entries/courses"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            授業一覧から選ぶ
        </a>

    </div>

    <!-- エラーメッセージ -->
    @if ($errors->any())

        <div class="bg-red-100 text-red-600 p-4 rounded mb-4">

            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach

        </div>

    @endif

    <!-- タグによる絞り込み -->
<div class="mb-6">

    <h2 class="text-lg font-bold mb-3">
        🏷️ タグから探す
    </h2>

    <div class="flex flex-wrap gap-3">

        <!-- すべて -->
        <a href="{{ url('/entries/courses') }}?{{ http_build_query(['q' => $q]) }}"
           class="px-4 py-2 rounded-full text-sm font-bold transition
           {{ !$tagId
               ? 'bg-blue-600 text-white'
               : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">

            すべて

        </a>

        <!-- 各タグ -->
        @foreach($tags as $tag)

            <a href="{{ url('/entries/courses') }}?{{ http_build_query(['tag' => $tag->id, 'q' => $q]) }}"
               class="px-4 py-2 rounded-full text-sm font-bold transition
               {{ $tagId == $tag->id
                   ? 'bg-blue-600 text-white'
                   : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">

                {{ $tag->name }}

            </a>

        @endforeach

    </div>

</div>


    <!-- 授業検索フォーム -->
    <form action="/entries/courses"
          method="GET"
          class="flex gap-3 mb-6">

        <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="授業名・担当教員名で検索"
            class="flex-1 border border-gray-300 rounded px-4 py-2"
        >

        <!-- 選択中のタグを検索時にも維持 -->
        @if($tagId)
            <input type="hidden"
                   name="tag"
                   value="{{ $tagId }}">
        @endif

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


    <!-- 絞り込み解除 -->
    @if($tagId)

        <div class="mb-4">

            <a href="{{ url('/entries/courses') }}?{{ http_build_query(['q' => $q]) }}"
               class="inline-block bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg text-sm">
                ✕ タグの絞り込みを解除
            </a>

        </div>

    @endif


    <!-- 検索結果件数 -->
    <p class="text-gray-600 mb-4">
        検索結果：{{ $courses->count() }}件
    </p>


    <!-- 授業一覧 -->
    <div class="overflow-x-auto">

        <table class="w-full border-collapse text-center">

            <thead class="bg-blue-100">

                <tr>
                    <th class="border p-3">曜日</th>
                    <th class="border p-3">時限</th>
                    <th class="border p-3">科目コード</th>
                    <th class="border p-3">授業名・タグ</th>
                    <th class="border p-3">担当教員</th>
                    <th class="border p-3">教室</th>
                    <th class="border p-3">操作</th>
                </tr>

            </thead>

            <tbody>

                @forelse ($courses as $course)

                    <tr class="hover:bg-gray-50">

                        <!-- 曜日 -->
                        <td class="border p-3">
                            {{ ['月', '火', '水', '木', '金'][$course->day - 1] }}
                        </td>

                        <!-- 時限 -->
                        <td class="border p-3">
                            {{ $course->period }}
                        </td>

                        <!-- 科目コード -->
                        <td class="border p-3">
                            {{ $course->code }}
                        </td>

                        <!-- 授業名とタグ -->
                        <td class="border p-3 text-left">

<!-- 授業名とお気に入り -->
<div class="flex items-center gap-3">

    <!-- 授業名 -->
    <a href="{{ url('/courses/' . $course->id) }}"
       class="font-bold text-blue-600 hover:underline">
        {{ $course->name }}
    </a>

    <!-- お気に入りボタン -->
    <form action="{{ route('courses.favorite', $course->id) }}"
          method="POST">
        @csrf

        <button type="submit"
                class="flex items-center gap-1 hover:scale-110 transition">

            @if(in_array($course->id, $favoriteCourseIds))
                <span class="text-yellow-500 text-xl">★</span>
            @else
                <span class="text-gray-400 text-xl">☆</span>
            @endif

            <span class="text-sm text-gray-600">
                {{ $course->favorites_count }}
            </span>

        </button>
    </form>

</div>

                            <!-- タグ -->
                            <div class="flex flex-wrap gap-2 mt-2">

                                @foreach($course->tags as $tag)

                                    <a href="{{ url('/entries/courses') }}?{{ http_build_query(['tag' => $tag->id, 'q' => $q]) }}"
                                       class="text-xs px-3 py-1 rounded-full transition
                                       {{ $tagId == $tag->id
                                           ? 'bg-blue-600 text-white'
                                           : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">

                                        🏷️ {{ $tag->name }}

                                    </a>

                                @endforeach

                            </div>

                        </td>

                        <!-- 担当教員 -->
                        <td class="border p-3">
                            {{ $course->teacher }}
                        </td>

                        <!-- 教室 -->
                        <td class="border p-3">
                            {{ $course->room ?? '未定' }}
                        </td>

                        <!-- 授業登録 -->
                        <td class="border p-3">

                            @if (in_array($course->id, $registeredCourseIds))

                                <span class="text-gray-500 font-bold">
                                    登録済み
                                </span>

                            @else

                                <form action="/entries" method="POST">

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="course_id"
                                        value="{{ $course->id }}"
                                    >

                                    <button
                                        type="submit"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">

                                        登録

                                    </button>

                                </form>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7"
                            class="border p-8 text-gray-500">

                            条件に一致する授業がありません。

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <!-- 戻るボタン -->
    <a href="/entries"
       class="inline-block mt-6 text-blue-600 hover:underline">

        ← 自分の時間割に戻る

    </a>

</div>

</body>
</html>
