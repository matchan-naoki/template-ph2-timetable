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
        <a href="/entries/courses"
           class="text-blue-600 hover:underline">
            ← 授業一覧に戻る
        </a>

        <!-- 投稿成功メッセージ -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mt-6 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- 授業情報 -->
        <div class="bg-white p-6 rounded-lg shadow mt-6 mb-6">

            <h1 class="text-3xl font-bold mb-4">
                {{ $course->name }}
            </h1>

            <p class="mb-2">
                授業コード：{{ $course->code }}
            </p>

            <p class="mb-2">
                担当教員：{{ $course->teacher }}
            </p>

            <p class="mb-2">
                教室：{{ $course->room ?? '未定' }}
            </p>

            <!-- タグ一覧 -->
            <div class="flex flex-wrap gap-2 mt-4">

                @foreach($course->tags as $tag)

                    <a href="{{ url('/entries/courses') }}?tag={{ $tag->id }}"
                       class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full hover:bg-blue-200">

                        🏷️ {{ $tag->name }}

                    </a>

                @endforeach

            </div>

        </div>

        <!-- 教務課のお知らせ投稿フォーム -->
        @if($isAdmin)

            <div class="bg-white p-6 rounded-lg shadow mb-6">

                <h2 class="text-2xl font-bold mb-4">
                    📢 お知らせを投稿する
                </h2>

                <p class="text-gray-500 mb-4">
                    この授業の履修が承認された学生にお知らせを送信します。
                </p>

                <!-- お知らせのエラーメッセージ -->
                @php
                    $announcementErrors = [];

                    foreach (['announcement', 'title', 'body', 'submission_token', 'token'] as $field) {
                        foreach ($errors->get($field) as $message) {
                            $announcementErrors[] = $message;
                        }
                    }
                @endphp

                @if(count($announcementErrors) > 0)

                    <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4">

                        @foreach($announcementErrors as $error)
                            <p>{{ $error }}</p>
                        @endforeach

                    </div>

                @endif

                <form action="{{ route('announcements.confirm', $course) }}"
                      method="POST">

                    @csrf

                    <!-- 二重投稿防止用トークン -->
                    <input type="hidden"
                           name="submission_token"
                           value="{{ old('submission_token', \Illuminate\Support\Str::random(40)) }}">

                    <!-- タイトル -->
                    <label class="block font-bold mb-2">
                        タイトル
                    </label>

                    <input type="text"
                           name="title"
                           maxlength="50"
                           value="{{ old('title') }}"
                           class="w-full border rounded p-3 mb-4"
                           placeholder="お知らせのタイトル"
                           required>

                    <!-- 本文 -->
                    <label class="block font-bold mb-2">
                        本文
                    </label>

                    <textarea name="body"
                              rows="5"
                              maxlength="500"
                              class="w-full border rounded p-3 mb-4"
                              placeholder="お知らせの内容"
                              required>{{ old('body') }}</textarea>

                    <!-- 投稿確認ボタン -->
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

                        投稿内容を確認する

                    </button>

                </form>

            </div>

        @endif

        <!-- お知らせ一覧 -->
        @if($canViewAnnouncements && $announcements->isNotEmpty())

            <div class="bg-white p-6 rounded-lg shadow mb-6">

                <h2 class="text-2xl font-bold mb-4">
                    📢 お知らせ一覧
                </h2>

                <div class="space-y-5">

                    @foreach($announcements as $announcement)

                        <div class="border-b pb-5">

                            <!-- 授業名 -->
                            <p class="text-sm text-blue-600 font-bold mb-2">
                                {{ $course->name }}
                            </p>

                            <!-- タイトル -->
                            <h3 class="text-lg font-bold mb-2">
                                {{ $announcement->title }}
                            </h3>

                            <!-- 本文 -->
                            <p class="text-gray-700 whitespace-pre-wrap break-words">{{ $announcement->body }}</p>

                            <!-- 投稿者 -->
                            <p class="text-sm text-gray-500 mt-3">
                                投稿者：
                                {{ $announcement->user?->name ?? '削除済みユーザー' }}
                            </p>

                            <!-- 投稿日 -->
                            <p class="text-sm text-gray-400 mt-1">
                                投稿日：
                                {{ $announcement->created_at->format('Y/m/d H:i') }}
                            </p>

                        </div>

                    @endforeach

                </div>

            </div>

        @endif

        <!-- 履修者一覧 -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">

            <h2 class="text-2xl font-bold mb-4">
                👥 履修者一覧（{{ $studentCount }}人）
            </h2>

            @if($students->isEmpty())

                <p class="text-gray-500">
                    まだ履修者はいません。
                </p>

            @else

                <div class="space-y-3">

                    @foreach($students as $student)

                        <div class="flex items-center gap-3 border-b pb-3">

                            <!-- アイコン -->
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">

                                {{ mb_substr($student->name, 0, 1) }}

                            </div>

                            <!-- 学生名 -->
                            <p class="font-bold">
                                {{ $student->name }}
                            </p>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

        <!-- レビュー投稿 -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">

            <h2 class="text-2xl font-bold mb-4">
                ⭐ レビューを書く
            </h2>

            <!-- レビューのエラーメッセージ -->
            @php
                $reviewErrors = [];

                foreach (['rating', 'comment', 'review'] as $field) {
                    foreach ($errors->get($field) as $message) {
                        $reviewErrors[] = $message;
                    }
                }
            @endphp

            @if(count($reviewErrors) > 0)

                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                    @foreach($reviewErrors as $error)
                        <p>{{ $error }}</p>
                    @endforeach

                </div>

            @endif

            <!-- レビュー投稿フォーム -->
            <form action="{{ route('reviews.store', $course->id) }}"
                  method="POST">

                @csrf

                <!-- 評価 -->
                <label class="block font-bold mb-2">
                    評価
                </label>

                <select name="rating"
                        class="w-full border rounded p-3 mb-4"
                        required>

                    <option value="">
                        評価を選択してください
                    </option>

                    @for($i = 5; $i >= 1; $i--)

                        <option value="{{ $i }}"
                                {{ old('rating') == $i ? 'selected' : '' }}>

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

                        <!-- 投稿者 -->
                        <p class="font-bold">
                            {{ $review->user->name ?? '不明なユーザー' }}
                        </p>

                        <!-- 星評価 -->
                        <p class="text-yellow-500">
                            {{ str_repeat('★', $review->rating) }}
                            {{ str_repeat('☆', 5 - $review->rating) }}
                        </p>

                    </div>

                    <!-- コメント -->
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $review->comment }}</p>

                    <!-- 投稿日 -->
                    <p class="text-gray-400 text-sm mt-2">
                        投稿日：{{ $review->created_at->format('Y/m/d H:i') }}
                    </p>

                    <!-- 自分のレビューだけ削除可能 -->
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