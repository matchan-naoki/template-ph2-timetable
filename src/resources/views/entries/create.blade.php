
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>授業を登録する | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">

    <h1 class="text-3xl font-bold mb-6">
        授業を登録する
    </h1>

    <div class="flex gap-3 mb-6">
        <a href="/entries/create"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            プルダウンから選ぶ
        </a>

        <a href="/entries/courses"
           class="bg-gray-200 px-4 py-2 rounded">
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

    <form action="/entries" method="POST">
        @csrf

        <label for="course_id" class="block font-bold mb-2">
            登録する授業
        </label>

        <select name="course_id"
                id="course_id"
                class="w-full border rounded p-3 mb-5"
                required>

            <option value="">授業を選択してください</option>

            @foreach ($courses as $course)
                <option value="{{ $course->id }}"
                    {{ old('course_id') == $course->id ? 'selected' : '' }}
                    {{ in_array($course->id, $registeredCourseIds) ? 'disabled' : '' }}>

                    {{ ['月', '火', '水', '木', '金'][$course->day - 1] }}
                    {{ $course->period }}限：
                    {{ $course->name }}
                    （{{ $course->code }}）

                </option>
            @endforeach

        </select>

        <button type="submit"
                class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600">
            登録する
        </button>
    </form>

    <a href="/entries"
       class="inline-block mt-6 text-blue-600 underline">
        ← 自分の時間割に戻る
    </a>

</div>

</body>
</html>
