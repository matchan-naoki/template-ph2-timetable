
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>自分の時間割 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-4xl mx-auto">

        <h1 class="text-3xl font-bold mb-6">自分の時間割</h1>

        <a href="/entries/create"
           class="inline-block bg-blue-500 text-white px-4 py-2 rounded mb-6">
            ＋ 授業を登録する
        </a>

        @if($entries->isEmpty())
            <p class="bg-white p-6 rounded">
                まだ授業が登録されていません
            </p>
        @else
            <table class="w-full border-collapse bg-white shadow">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="border p-3">曜日</th>
                        <th class="border p-3">時限</th>
                        <th class="border p-3">授業名</th>
                        <th class="border p-3">教室</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td class="border p-3 text-center">
                                {{ ['月', '火', '水', '木', '金'][$entry->day - 1] }}
                            </td>
                            <td class="border p-3 text-center">
                                {{ $entry->period }}限
                            </td>
                            <td class="border p-3">
                                {{ $entry->name }}
                            </td>
                            <td class="border p-3 text-center">
                                {{ $entry->room }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>

</body>
</html>
