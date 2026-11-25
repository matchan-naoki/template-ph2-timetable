<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>教室予約</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">


<!-- 自分の予約一覧 -->
<div class="max-w-5xl mx-auto mt-8 mb-8 bg-white p-6 rounded-lg shadow">

    <h2 class="text-2xl font-bold mb-5">
        📅 自分の予約一覧
    </h2>

    @if($myReservations->isEmpty())

        <p class="text-gray-500 bg-gray-50 p-4 rounded-lg">
            まだ予約した教室はありません。
        </p>

    @else

        <div class="space-y-4">

            @foreach($myReservations as $reservation)

                <div class="border rounded-lg p-4 flex flex-wrap justify-between items-center gap-4">

                    <div>
                        <h3 class="text-xl font-bold text-blue-700">
                            🏫 {{ $reservation->room_code }}
                        </h3>

                        <p class="text-gray-700 mt-2">
                            📅 {{ $reservation->date->format('Y年m月d日') }}
                            （{{ ['日', '月', '火', '水', '木', '金', '土'][$reservation->date->dayOfWeek] }}）
                            ・{{ $reservation->period }}限
                        </p>

                        <p class="text-gray-500 text-sm mt-1">
                            📍 {{ $reservation->room->building ?? '建物未登録' }}
                            ・{{ $reservation->room->floor ?? '不明' }}階
                        </p>
                    </div>


<div class="flex flex-col items-end gap-3">

    <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-bold">
        ✅ 予約確定
    </span>

    <form action="{{ route('room-reservations.destroy', $reservation->id) }}"
          method="POST">
        @csrf
        @method('DELETE')

        <button type="submit"
                onclick="return confirm('この教室の予約をキャンセルしますか？')"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-bold">
            予約をキャンセル
        </button>
    </form>

</div>


                </div>

            @endforeach

        </div>

    @endif

</div>


<div class="max-w-5xl mx-auto px-4 py-8">

    <!-- タイトル -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                🏫 教室予約
            </h1>
            <p class="text-gray-500 mt-2">
                空いている教室を検索して予約できます。
            </p>
        </div>

        <a href="/entries"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            時間割に戻る
        </a>
    </div>

    <!-- 成功メッセージ -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- エラーメッセージ -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-lg mb-5">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 検索フォーム -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">

        <h2 class="text-xl font-bold mb-4">
            🔍 空き教室を検索
        </h2>

        <form action="{{ route('room-reservations.index') }}"
              method="GET"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <div>
                <label class="block font-semibold mb-2">
                    利用日
                </label>

                <input type="date"
                       name="date"
                       value="{{ $date }}"
                       min="{{ now()->toDateString() }}"
                       required
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    時限
                </label>

                <select name="period"
                        class="w-full border rounded-lg px-3 py-2">
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}"
                            {{ $period == $i ? 'selected' : '' }}>
                            {{ $i }}限
                        </option>
                    @endfor
                </select>
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded-lg">
                検索する
            </button>

        </form>
    </div>

    <!-- 検索結果 -->
    <div class="bg-white rounded-xl shadow p-6">

        <h2 class="text-xl font-bold mb-2">
            📋 空いている教室
        </h2>

        <p class="text-gray-500 mb-5">
            {{ $date }} ／ {{ $period }}限
        </p>

        @if($rooms->isEmpty())

            <div class="text-center py-12 text-gray-500">
                この日時に空いている教室はありません。
            </div>

        @else

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                @foreach($rooms as $room)

                    <div class="border rounded-xl p-5 hover:shadow-md transition">

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-xl font-bold">
                                    {{ $room->code }}
                                </h3>

                                <p class="text-gray-500 mt-1">
                                    {{ $room->building }}・{{ $room->floor }}階
                                </p>
                            </div>

                            <span class="bg-green-100 text-green-700 text-sm font-bold px-3 py-1 rounded-full">
                                空き
                            </span>
                        </div>

                        <p class="text-gray-600 mb-4">
                            👥 定員：{{ $room->capacity }}人
                        </p>

                        <!-- 予約フォーム -->
                        <form action="{{ route('room-reservations.store') }}"
                              method="POST">

                            @csrf

                            <input type="hidden"
                                   name="room_code"
                                   value="{{ $room->code }}">

                            <input type="hidden"
                                   name="date"
                                   value="{{ $date }}">

                            <input type="hidden"
                                   name="period"
                                   value="{{ $period }}">

                            <button type="submit"
                                    onclick="return confirm('この教室を予約しますか？')"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                                この教室を予約する
                            </button>

                        </form>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>

</body>
</html>
