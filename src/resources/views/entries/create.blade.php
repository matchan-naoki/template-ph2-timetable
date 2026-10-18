
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>授業登録 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow">

        <h1 class="text-2xl font-bold mb-6">授業を登録する</h1>

        <form action="/entries" method="POST">
            @csrf

            <label class="block mb-2">曜日</label>
            <select name="day" class="w-full border p-2 mb-4">
                <option value="1">月曜日</option>
                <option value="2">火曜日</option>
                <option value="3">水曜日</option>
                <option value="4">木曜日</option>
                <option value="5">金曜日</option>
            </select>

            <label class="block mb-2">時限</label>
            <select name="period" class="w-full border p-2 mb-4">
                <option value="1">1限</option>
                <option value="2">2限</option>
                <option value="3">3限</option>
                <option value="4">4限</option>
                <option value="5">5限</option>
            </select>

            <label class="block mb-2">授業名</label>
            <input type="text" name="name"
                   class="w-full border p-2 mb-4"
                   placeholder="例：統計学" required>

            <label class="block mb-2">教室</label>
            <input type="text" name="room"
                   class="w-full border p-2 mb-6"
                   placeholder="例：J201" required>

            <button type="submit"
                    class="w-full bg-blue-500 text-white p-3 rounded">
                登録する
            </button>
        </form>

        <a href="/entries" class="block mt-4 text-blue-500">
            ← 一覧に戻る
        </a>

    </div>

</body>
</html>
