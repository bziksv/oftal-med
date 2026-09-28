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

"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_iproperty
SET TEMPLATE = 'Обзор фундус-камеры ZEISS CLARUS 500: как аппарат снимает глазное дно и чем полезен клинике.'
WHERE ID = 375 AND CODE = 'ELEMENT_META_DESCRIPTION' AND ENTITY_ID = 24387
  AND TEMPLATE LIKE 'магазин оборудования для офтальмолога%';

UPDATE b_iblock_element_iprop
SET VALUE = 'Обзор фундус-камеры ZEISS CLARUS 500: как аппарат снимает глазное дно и чем полезен клинике.'
WHERE ELEMENT_ID = 24387 AND IPROP_ID = 375
  AND VALUE LIKE 'магазин оборудования для офтальмолога%';
SQL

echo "Short news description lengthened (24387)"

# Кириллическая «с» в #ссс ломает CSS-цвет. Списки: h2/div/p/ul не могут быть прямыми детьми ul.
"$MYSQL" -h 127.0.0.1 -u oftal_med_local -poftal_med_local --default-character-set=utf8mb4 oftal_med_ru_db <<'SQL'
UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(DESCRIPTION, '#ссс', '#ccc'), TIMESTAMP_X = NOW()
WHERE DESCRIPTION LIKE '%#ссс%';

UPDATE b_iblock_element
SET DETAIL_TEXT = REPLACE(DETAIL_TEXT, '#ссс', '#ccc'), TIMESTAMP_X = NOW()
WHERE DETAIL_TEXT LIKE '%#ссс%';

UPDATE b_iblock_element
SET PREVIEW_TEXT = REPLACE(PREVIEW_TEXT, '#ссс', '#ccc'), TIMESTAMP_X = NOW()
WHERE PREVIEW_TEXT LIKE '%#ссс%';

UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(
  DESCRIPTION,
  '<ul style="padding-left: 30px;">\r\n\t<h2>Выбор оснащения в oftal-med.ru:</h2>\r\n',
  '<h2>Выбор оснащения в oftal-med.ru:</h2>\r\n<ul style="padding-left: 30px;">\r\n'
), TIMESTAMP_X = NOW()
WHERE ID = 4876
  AND DESCRIPTION LIKE '%<ul style="padding-left: 30px;">%<h2>Выбор оснащения в oftal-med.ru:</h2>%';

UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(REPLACE(REPLACE(
  DESCRIPTION,
  '<ul style="padding-left: 30px;">\r\n\t<div style="display: flex; justify-content: space-between; flex-wrap: wrap;">\r\n\t\t<div style="width: 570px;">\r\n\t\t\t<li>Прямые',
  '<ul style="padding-left: 30px;">\r\n\t<li>\r\n\t<div style="display: flex; justify-content: space-between; flex-wrap: wrap;">\r\n\t\t<div style="width: 570px;">\r\n\t\t\tПрямые'
),
  'прибора.</li>\r\n\t\t</div>',
  'прибора.\r\n\t\t</div>'
),
  '\t</div>\r\n\t<li>Непрямые',
  '\t</div>\r\n\t</li>\r\n\t<li>Непрямые'
), TIMESTAMP_X = NOW()
WHERE ID = 4940
  AND DESCRIPTION LIKE '%<div style="width: 570px;">%<li>Прямые%';

UPDATE b_iblock_element
SET DETAIL_TEXT = REPLACE(
  DETAIL_TEXT,
  '<ul type="disc">\r\n\t<li>раскладка необходимых офтальмологических приборов,</li>\r\n\t<p>\r\n\t</p>\r\n\t<ul type="disc">\r\n\t\t<li>сокращение времени обследования и лечения пациента,</li>\r\n\t</ul>\r\n\t<p>\r\n\t</p>\r\n\t<ul type="disc">\r\n\t\t<li>повышает точность диагностики,</li>\r\n\t</ul>\r\n\t<p>\r\n\t</p>\r\n\t<ul type="disc">\r\n\t\t<li>комфорт для пациента и врача,</li>\r\n\t</ul>\r\n\t<p>\r\n\t</p>\r\n\t<ul type="disc">\r\n\t\t<li>эргономичность и компактность.</li>\r\n\t</ul>\r\n</ul>',
  '<ul type="disc">\r\n\t<li>раскладка необходимых офтальмологических приборов,</li>\r\n\t<li>сокращение времени обследования и лечения пациента,</li>\r\n\t<li>повышает точность диагностики,</li>\r\n\t<li>комфорт для пациента и врача,</li>\r\n\t<li>эргономичность и компактность.</li>\r\n</ul>'
), TIMESTAMP_X = NOW()
WHERE ID = 17723
  AND DETAIL_TEXT LIKE '%<p>%<ul type="disc">%<ul type="disc">%';

UPDATE b_iblock_section
SET DESCRIPTION = REPLACE(REPLACE(
  DESCRIPTION,
  '<ul>\r\n\t<ul>',
  '<ul>'
),
  '\t</ul>\r\n</ul>',
  '</ul>'
), TIMESTAMP_X = NOW()
WHERE ID IN (4882, 4905)
  AND DESCRIPTION LIKE '%<ul>\r\n\t<ul>%';
SQL

echo "Validator markup updated (color #ccc, lists 4876, 4940, 17723, 4882, 4905)"

# У картинок в текстах разделов и новостей должен быть атрибут alt.
python3 - <<'PY'
import binascii, html, re, subprocess
mysql = ["/opt/homebrew/opt/mysql@8.0/bin/mysql", "-h", "127.0.0.1", "-u", "oftal_med_local", "-poftal_med_local",
         "--default-character-set=utf8mb4", "--batch", "--raw", "-N", "oftal_med_ru_db"]

def q(sql):
    return subprocess.check_output(mysql + ["-e", sql])

def add_alt(text):
    def repl(m):
        tag = m.group(0)
        if re.search(r"\balt\s*=", tag, re.I):
            return tag
        title = re.search(r"""\btitle\s*=\s*(['"])(.*?)\1""", tag, re.I)
        alt = html.escape(title.group(2).strip(), quote=True) if title else ""
        return re.sub(r"(?i)^<img\b", f'<img alt="{alt}"', tag, count=1)
    return re.sub(r"(?i)<img\b[^>]*>", repl, text)

for table, col, where in (
    ("b_iblock_section", "DESCRIPTION", "DESCRIPTION LIKE '%<img%'"),
    ("b_iblock_element", "DETAIL_TEXT", "DETAIL_TEXT LIKE '%<img%'"),
    ("b_iblock_element", "PREVIEW_TEXT", "PREVIEW_TEXT LIKE '%<img%'"),
):
    for line in q(f"SELECT ID, HEX({col}) FROM {table} WHERE {where}").decode().splitlines():
        if "\t" not in line:
            continue
        eid, hx = line.split("\t", 1)
        text = binascii.unhexlify(hx.strip()).decode("utf-8")
        new = add_alt(text)
        if new == text:
            continue
        q(f"UPDATE {table} SET {col}=UNHEX('{new.encode().hex()}'), TIMESTAMP_X=NOW() WHERE ID={int(eid)}")
PY
echo "Missing img alt attributes filled"
