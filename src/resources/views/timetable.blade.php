<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>時間割 | POSSE大学</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">

    <h1 class="text-3xl font-bold mb-8">時間割</h1>

    <!-- 区画1：月曜日の授業 -->
    <h2 class="text-xl font-bold mb-4">月曜日の授業</h2>

    <table class="w-full border-collapse border mb-8 bg-white">
        <thead>
            <tr>
                <th class="border p-2">時限</th>
                <th class="border p-2">授業名</th>
                <th class="border p-2">担当教員</th>
                <th class="border p-2">教室</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($mondayCourses as $course)
                <tr>
                    <td class="border p-2">{{ $course->period }}</td>
                    <td class="border p-2">{{ $course->name }}</td>
                    <td class="border p-2">{{ $course->teacher }}</td>
                    <td class="border p-2">{{ $course->room }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 区画2：必修科目 -->
    <h2 class="text-xl font-bold mb-4">必修科目</h2>

    <table class="w-full border-collapse border bg-white">
        <thead>
            <tr>
                <th class="border p-2">授業名</th>
                <th class="border p-2">単位数</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($requiredCourses as $course)
                <tr>
                    <td class="border p-2">{{ $course->name }}</td>
                    <td class="border p-2">{{ $course->credits }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>