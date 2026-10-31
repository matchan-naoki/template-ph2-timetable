<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせの投稿確認</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 30px;
        }

        .item {
            margin-bottom: 25px;
        }

        .label {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .content {
            padding: 15px;
            background: #f5f6fa;
            border-radius: 8px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .back {
            background: #ddd;
        }

        .submit {
            background: #2563eb;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>📢 お知らせの投稿確認</h1>

    <div class="item">
        <div class="label">授業名</div>
        <div class="content">{{ $course->name }}</div>
    </div>

    <div class="item">
        <div class="label">タイトル</div>
        <div class="content">{{ $title }}</div>
    </div>

    <div class="item">
        <div class="label">本文</div>
        <div class="content">{{ $body }}</div>
    </div>

    <div class="buttons">

        {{-- 入力画面に戻る --}}
        <form action="{{ route('courses.show', $course) }}" method="GET">
            <button type="submit" class="back">戻る</button>
        </form>

{{-- 投稿を確定する --}}
<form
    action="{{ route('announcements.store', $course) }}"
    method="POST"
    onsubmit="
        const button = this.querySelector('button[type=submit]');
        if (button.disabled) return false;
        button.disabled = true;
        button.textContent = '投稿中...';
    "
>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <button type="submit" class="submit">
        投稿を確定
    </button>
</form>

    </div>

</div>

</body>
</html>