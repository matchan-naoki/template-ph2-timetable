-- W1 POSSE課題：index.html の各区画と同じ結果を返す SELECT 文を書く
-- 書いた SQL は教材サイトの「SQL練習場」で実行し、表示が index.html の区画と一致することを確かめてから書くこと
-- 曜日（day）は数字のまま返してよい

-- 区画1：月曜日の授業（時限の早い順）

SELECT PERIOD,NAME,teacher,room
FROM courses
WHERE day = '1'
ORDER BY period ASC;

-- 区画2：必修科目（科目コードの順）

SELECT name,credits
FROM courses
WHERE  required ='true'
ORDER BY code ASC;

-- 区画3：3限以降の2単位の授業（曜日→時限の順）

SELECT day,PERIOD,NAME
FROM courses
WHERE period >= 3
ORDER BY DAY,PERIOD ASC;

-- 区画4：演習の授業（科目コードの順）

SELECT name,teacher,room
FROM courses
WHERE NAME LIKE '%演習'
ORDER BY code ASC;

-- 区画5：J棟の授業（金曜以外。曜日→時限の順）

SELECT day,PERIOD,name,room
FROM courses
WHERE room LIKE 'J%'
AND DAY < 5
ORDER BY day,PERIOD ASC;

-- 区画6：担当教員が未定の授業（科目コードの順）

SELECT code,name
FROM courses
WHERE teacher IS NULL;