#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MYSQL=(/opt/homebrew/opt/mysql@8.0/bin/mysql --socket=/tmp/mysql.sock -u oftal_med_local -poftal_med_local oftal_med_ru_db)

echo "Updating Bitrix consent agreement..."
"${MYSQL[@]}" --default-character-set=utf8mb4 <<'SQL'
UPDATE b_consent_agreement
SET
    NAME = 'Согласие на обработку персональных данных',
    LABEL_TEXT = 'Я даю согласие на обработку персональных данных в соответствии с нашей Политикой обработки персональных данных.',
    USE_URL = 'Y',
    URL = '/upload/soglasie-na-obrabotku-personalnyh-dannyh-oftalmed.jpg',
    IS_AGREEMENT_TEXT_HTML = 'N'
WHERE ID = 1;
SQL

echo "Searching DB for old legal image links..."
OLD_PATTERNS=(
  'upload/politics.jpg'
  'upload/politika-ispolzovanija-cookies-oftalmed.jpg'
  'upload/rules-recommendation.jpg'
  'politika-ispolzovanija-cookies-oftalmed.jpg'
  'politics.jpg'
  'rules-recommendation.jpg'
  'политикой конфиденциальности'
  'политика конфиденциальности'
)

for pattern in "${OLD_PATTERNS[@]}"; do
  count=$("${MYSQL[@]}" -N -e "
    SELECT SUM(cnt) FROM (
      SELECT COUNT(*) cnt FROM b_option WHERE VALUE LIKE '%${pattern}%'
      UNION ALL SELECT COUNT(*) FROM b_option_site WHERE VALUE LIKE '%${pattern}%'
      UNION ALL SELECT COUNT(*) FROM b_consent_agreement WHERE AGREEMENT_TEXT LIKE '%${pattern}%' OR LABEL_TEXT LIKE '%${pattern}%' OR URL LIKE '%${pattern}%'
      UNION ALL SELECT COUNT(*) FROM b_iblock_element WHERE DETAIL_TEXT LIKE '%${pattern}%' OR PREVIEW_TEXT LIKE '%${pattern}%'
    ) t;
  " 2>/dev/null || echo 0)
  if [[ "${count:-0}" != "0" ]]; then
    echo "  found ${count} row(s) with: ${pattern}"
  fi
done

echo "Done."
