<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">

        <h1 class="text-2xl font-bold text-center mb-6">
            ログイン
        </h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block mb-2">メールアドレス</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full border rounded p-3"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2">パスワード</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border rounded p-3"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded hover:bg-blue-600"
            >
                ログイン
            </button>
        </form>

        <p class="text-center mt-5 text-sm">
            アカウントを持っていない方は
            <a href="/register" class="text-blue-500 underline">
                新規登録
            </a>
        </p>

    </div>

</body>
</html>