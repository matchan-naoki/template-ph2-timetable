
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">

        <h1 class="text-2xl font-bold text-center mb-6">
            新規登録
        </h1>

        <form action="/register" method="POST">
            @csrf

            <label class="block mb-2">ユーザー名</label>
            <input type="text" name="name"
                value="{{ old('name') }}"
                class="border rounded w-full p-3 mb-4" required>

            <label class="block mb-2">メールアドレス</label>
            <input type="email" name="email"
                value="{{ old('email') }}"
                class="border rounded w-full p-3 mb-4" required>

            <label class="block mb-2">パスワード</label>
            <input type="password" name="password"
                minlength="8"
                class="border rounded w-full p-3 mb-4" required>

            <label class="block mb-2">パスワード（確認用）</label>
            <input type="password" name="password_confirmation"
                minlength="8"
                class="border rounded w-full p-3 mb-4" required>

            @if ($errors->any())
                <div class="text-red-500 mb-4">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <button type="submit"
                class="bg-blue-500 text-white w-full py-3 rounded hover:bg-blue-600">
                新規登録
            </button>
        </form>

        <p class="text-center mt-5 text-sm">
            すでにアカウントをお持ちの方は
            <a href="/login" class="text-blue-500 underline">
                ログイン
            </a>
        </p>
    </div>

</body>
</html>
