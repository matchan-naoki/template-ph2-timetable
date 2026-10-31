{{ $studentName }} さん

履修している授業について、新しいお知らせがあります。

━━━━━━━━━━━━━━━━━━━━

【授業名】
{{ $announcement->course->name }}

【タイトル】
{{ $announcement->title }}

【本文】
{{ $announcement->body }}

【投稿者】
{{ $announcement->user?->name ?? '削除済みユーザー' }}

【投稿日】
{{ $announcement->created_at->format('Y年m月d日 H:i') }}

━━━━━━━━━━━━━━━━━━━━

授業の詳細ページからも確認できます。