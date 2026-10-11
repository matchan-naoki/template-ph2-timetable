-- W2 POSSE課題：index.html の区画7〜11と同じ結果を返す SQL を書く
-- 書いた SQL は db コンテナの psql（docker compose exec db psql -U posse -d timetable）で実行し、
-- 表示が index.html の区画と一致することを確かめてから書くこと
-- 曜日（day）は数字のまま返してよい

-- 区画7：すべての授業と教室の情報（科目コードの順）

SELECT
c.code AS "科目コード",
c.name AS "授業名",
c.room AS "教室",
r.building AS "建物",
r.capacity AS "定員"
FROM courses AS c
LEFT JOIN rooms AS r
ON c.room = r.code
ORDER BY c.code;

-- 区画8：曜日ごとの授業数（曜日の順）

SELECT c.day, COUNT(*)
FROM courses AS c
GROUP BY day
ORDER BY day;

-- 区画9：建物ごとの授業数と合計単位（建物名の順）

SELECT
r.building AS "建物",
COUNT(*) AS "授業数",
SUM(c.credits) AS "合計単位"
FROM courses AS c
INNER JOIN rooms AS r
ON c.room = r.code
GROUP BY r.building
ORDER BY r.building;

-- 区画10：授業を3つ以上担当している教員（授業数の多い順、同数なら名前の順）

SELECT c.teacher AS "担当教員",
COUNT(*) AS "授業数"
FROM courses AS c
GROUP BY c.teacher
HAVING COUNT(*) >= 3
ORDER BY COUNT(*) DESC;

-- 区画11：授業が1つも入っていない教室（教室コードの順）

SELECT
r.code AS "教室",
r.building AS "建物",
r.capacity AS "定員"
FROM rooms AS r
LEFT JOIN courses AS c
ON r.code = c.room
WHERE c.id IS NULL
ORDER BY r.code;
