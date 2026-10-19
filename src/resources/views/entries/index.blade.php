
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

<div class="flex justify-between items-center mb-6">

    <!-- 授業登録ボタン -->
    <a href="/entries/create"
       class="inline-block bg-blue-500 text-white px-4 py-2 rounded">
        ＋ 授業を登録する
    </a>

    <!-- 全削除ボタン -->
    @if($entries->isNotEmpty())
        <form action="/entries" method="POST">
            @csrf
            @method('DELETE')

            <button type="submit"
                    onclick="return confirm('本当にすべての授業を削除しますか？この操作は取り消せません。')"
                    class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded">
                全削除
            </button>
        </form>
    @endif

</div>


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
                        <th class="border p-3">操作</th>
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
                                {{ $entry->room ?? '教室未定' }}
                            </td>

                            <td class="border p-3 text-center">
                                <a href="/entries/{{ $entry->id }}/edit"
                                   class="inline-block bg-blue-500 text-white px-3 py-1 rounded">
                                    編集
                                </a>

                                <form action="/entries/{{ $entry->id }}"
                                      method="POST"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('この授業を削除しますか？')"
                                            class="bg-red-500 text-white px-3 py-1 rounded">
                                        削除
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        
    </div>

</body>
</html>

