
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>授業を編集</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">

        <h1 class="text-2xl font-bold mb-6">授業を編集</h1>

        <form action="/entries/{{ $entry->id }}" method="POST">
            @csrf
            @method('PUT')

            <label class="block mb-2">曜日</label>
            <select name="day" class="border p-2 w-full mb-4">
                @foreach(['月', '火', '水', '木', '金'] as $index => $day)
                    <option value="{{ $index + 1 }}"
                        {{ old('day', $entry->day) == $index + 1 ? 'selected' : '' }}>
                        {{ $day }}曜日
                    </option>
                @endforeach
            </select>

            <label class="block mb-2">時限</label>
            <select name="period" class="border p-2 w-full mb-4">
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}"
                        {{ old('period', $entry->period) == $i ? 'selected' : '' }}>
                        {{ $i }}限
                    </option>
                @endfor
            </select>

            <label class="block mb-2">授業名</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $entry->name) }}"
                   class="border p-2 w-full mb-4"
                   required>

            <label class="block mb-2">教室</label>
            <input type="text"
                   name="room"
                   value="{{ old('room', $entry->room) }}"
                   class="border p-2 w-full mb-4"
                   required>

            <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded">
                保存する
            </button>

            <a href="/entries" class="ml-4 text-gray-500">
                戻る
            </a>
        </form>

    </div>

</body>
</html>
