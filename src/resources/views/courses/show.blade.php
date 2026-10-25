<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->name }} | 授業詳細</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-3xl mx-auto">

        <!-- 戻るボタン -->
        <a href="/entries/courses" class="text-blue-600 hover:underline">
            ← 授業一覧に戻る
        </a>

        <!-- 授業情報 -->
        <div class="bg-white p-6 rounded-lg shadow mt-6 mb-6">

            <h1 class="text-3xl font-bold mb-4">
                {{ $course->name }}
            </h1>

            <p>授業コード：{{ $course->code }}</p>
            <p>担当教員：{{ $course->teacher }}</p>
            <p>教室：{{ $course->room ?? '未定' }}</p>

        </div>

        <!-- レビュー投稿 -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">

            <h2 class="text-2xl font-bold mb-4">
                ⭐ レビューを書く
            </h2>

            <!-- 投稿成功メッセージ -->
            @if(session('success'))
                <p class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </p>
            @endif

            <!-- エラーメッセージ -->
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('reviews.store', $course->id) }}" method="POST">

                @csrf

                <!-- 評価 -->
                <label class="block font-bold mb-2">
                    評価
                </label>

                <select name="rating"
                        class="w-full border rounded p-3 mb-4"
                        required>

                    <option value="">評価を選択してください</option>

                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>
                            {{ $i }} ★
                        </option>
                    @endfor

                </select>

                <!-- コメント -->
                <label class="block font-bold mb-2">
                    コメント
                </label>

                <textarea name="comment"
                          rows="4"
                          maxlength="1000"
                          class="w-full border rounded p-3 mb-4"
                          placeholder="授業の感想を書いてください"
                          required>{{ old('comment') }}</textarea>

                <!-- 投稿ボタン -->
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg">
                    レビューを投稿する
                </button>

            </form>

        </div>

        <!-- レビュー一覧 -->
        <div class="bg-white p-6 rounded-lg shadow">

            <h2 class="text-2xl font-bold mb-4">
                📝 みんなのレビュー（{{ $reviews->count() }}件）
            </h2>

            @forelse($reviews as $review)

                <div class="border-b py-4">

                    <div class="flex justify-between items-center mb-2">

                        <p class="font-bold">
                            {{ $review->user->name ?? '不明なユーザー' }}
                        </p>

                        <p class="text-yellow-500">
                            {{ str_repeat('★', $review->rating) }}
                            {{ str_repeat('☆', 5 - $review->rating) }}
                        </p>

                    </div>

                    <p class="text-gray-700 whitespace-pre-wrap">
                        {{ $review->comment }}
                    </p>

                    <p class="text-gray-400 text-sm mt-2">
                        投稿日：{{ $review->created_at->format('Y/m/d H:i') }}
                    </p>

                    <!-- 自分のレビューだけ削除ボタンを表示 -->
@if($review->user_id === auth()->id())

    <form action="{{ route('reviews.destroy', $review->id) }}"
          method="POST"
          class="mt-3 text-right">

        @csrf
        @method('DELETE')

        <button type="submit"
                onclick="return confirm('このレビューを削除しますか？')"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">

            削除

        </button>

    </form>

@endif

                </div>

            @empty

                <p class="text-gray-500">
                    まだレビューがありません。最初のレビューを投稿してみましょう！
                </p>

            @endforelse

        </div>

    </div>

</body>
</html>