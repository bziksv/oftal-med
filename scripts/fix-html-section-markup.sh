#!/bin/sh
# Идемпотентно чинит разметку в описаниях разделов (локальная MySQL).
# 4957 BIO Welch Allyn: <b> вокруг <p> → <p><b>…</b></p>
# 4936 test-poloski: лишний </div>
set -e
cd "$(dirname "$0")/.."

if [ -x /opt/homebrew/opt/mysql@8.0/bin/mysql ]; then
  MYSQL=/opt/homebrew/opt/mysql@8.0/bin/mysql
else
  MYSQL=$(command -v mysql)
fi

"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(
  DESCRIPTION,
  ' <b>\r\n<p>\r\n\t Перечень основных характеристик продукта включает:\r\n</p>\r\n </b>',
  '\r\n<p><b>Перечень основных характеристик продукта включает:</b></p>'
), TIMESTAMP_X = NOW()
WHERE ID = 4957
  AND DESCRIPTION LIKE '%<b>%Перечень основных характеристик продукта включает:%';

UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(
  DESCRIPTION,
  '\t</ul>\r\n\t</div>\r\n\t</div>\r\n</div>',
  '\t</ul>\r\n\t</div>\r\n</div>'
), TIMESTAMP_X = NOW()
WHERE ID = 4936
  AND DESCRIPTION LIKE '%width: 508px%</ul>%';
SQL

"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_element SET DETAIL_TEXT = REPLACE(REPLACE(DETAIL_TEXT,
  '<h4> <b>Офтальмоскопы, выпускаемые сейчас,&nbsp;</b>выделяются: </h4>',
  '<h3> <b>Офтальмоскопы, выпускаемые сейчас,&nbsp;</b>выделяются: </h3>'),
  '<h4><b>Офтальмоскопы, выпускаемые сейчас,&nbsp;</b>выделяются:</h4>',
  '<h3><b>Офтальмоскопы, выпускаемые сейчас,&nbsp;</b>выделяются:</h3>'),
  TIMESTAMP_X = NOW()
WHERE ID = 17711 AND DETAIL_TEXT LIKE '%<h4>%выпускаемые сейчас%';

UPDATE b_iblock_element SET DETAIL_TEXT = REPLACE(DETAIL_TEXT,
  '<h4>Преимущества использования Омега 500</h4>',
  '<h3>Преимущества использования Омега 500</h3>'),
  TIMESTAMP_X = NOW()
WHERE ID = 17438 AND DETAIL_TEXT LIKE '%<h4>Преимущества использования Омега 500</h4>%';

UPDATE b_iblock_element SET DETAIL_TEXT = REPLACE(DETAIL_TEXT,
  '<h4>Простота использования и обслуживания</h4>',
  '<h3>Простота использования и обслуживания</h3>'),
  TIMESTAMP_X = NOW()
WHERE ID = 25725 AND DETAIL_TEXT LIKE '%<h4>Простота использования и обслуживания</h4>%';

UPDATE b_iblock_element SET DETAIL_TEXT = REPLACE(DETAIL_TEXT,
  '<h5>Заключение</h5>',
  '<h3>Заключение</h3>'),
  TIMESTAMP_X = NOW()
WHERE ID IN (25801, 25851) AND DETAIL_TEXT LIKE '%<h5>Заключение</h5>%';
SQL

"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_element SET DETAIL_TEXT = REPLACE(
  REPLACE(DETAIL_TEXT, '<h2>\r\nОбзор офтальмологических комбайнов&nbsp;</h2>', '<h2>Роль комбайна в кабинете офтальмолога</h2>'),
  '<h2>Обзор офтальмологических комбайнов</h2>',
  '<h2>Роль комбайна в кабинете офтальмолога</h2>'
), TIMESTAMP_X = NOW()
WHERE ID = 17723 AND DETAIL_TEXT LIKE '%<h2>%Обзор офтальмологических комбайнов%';

UPDATE b_iblock_section SET DESCRIPTION = REPLACE(DESCRIPTION, '<h2>Эластотонометры</h2>', '<h2>Набор Филатова-Кальфа для эластотонометрии</h2>'), TIMESTAMP_X = NOW()
WHERE ID = 5190 AND DESCRIPTION LIKE '%<h2>Эластотонометры</h2>%';

UPDATE b_iblock_section SET DESCRIPTION = REPLACE(DESCRIPTION, '<h2>Авторефкератометры Взор</h2>', '<h2>Авторефкератометр ВЗОР 9000</h2>'), TIMESTAMP_X = NOW()
WHERE ID = 5208 AND DESCRIPTION LIKE '%<h2>Авторефкератометры Взор</h2>%';

UPDATE b_iblock_section SET DESCRIPTION = REPLACE(DESCRIPTION, '<h2>Линзметры</h2>', '<h2>Измерение преломляющей силы линз</h2>'), TIMESTAMP_X = NOW()
WHERE ID = 5210 AND DESCRIPTION LIKE '%<h2>Линзметры</h2>%';

UPDATE b_iblock_section SET DESCRIPTION = REPLACE(DESCRIPTION, '<h2>Офтальмоскопы ручные, карманные</h2>', '<h2>Карманные модели для осмотра глазного дна</h2>'), TIMESTAMP_X = NOW()
WHERE ID = 5213 AND DESCRIPTION LIKE '%<h2>Офтальмоскопы ручные, карманные</h2>%';
SQL

"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_iproperty
SET TEMPLATE = 'Как выбрать офтальмоскоп: советы и критерии'
WHERE ID = 342 AND CODE = 'ELEMENT_META_TITLE' AND ENTITY_ID = 17742
  AND TEMPLATE = 'Как выбрать офтальмоскоп';

UPDATE b_iblock_element_iprop
SET VALUE = 'Как выбрать офтальмоскоп: советы и критерии'
WHERE ELEMENT_ID = 17742 AND IPROP_ID = 342
  AND VALUE = 'Как выбрать офтальмоскоп';
SQL

echo "Section HTML markup updated (4957, 4936)"
echo "Heading hierarchy updated (17711, 17438, 25725, 25801, 25851)"
echo "Duplicate H1/H2 uniqueized (17723, 5190, 5208, 5210, 5213)"
echo "Short browser titles lengthened (17742)"
