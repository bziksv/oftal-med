# oftal-med.ru — документация проекта

Bitrix-интернет-магазин офтальмологического оборудования и приборов (поставки медтехники по РФ).

Шаблон: **medical-templates**. Компоненты: **nbrains**, **niges**.  
Модули: **prime.alerts** (`local/`), **prime.cleaner**, **prime.roistatbitrixcms**, **prime.updateprice**, **artrix.imageoptimizer**, **arturgolubev.***, **isaev.seotemplate**, **kda.exportexcel**, **delight.webpconverter**, **imyie.orderminprice**, **asd.iblock**, **niges.cookiesaccept**, **sng.secure**, **dev2fun.imagecompress**, **ismagin.filecleaner**.

## Репозиторий и окружения

| | |
|---|---|
| GitHub | https://github.com/bziksv/oftal-med |
| **Git root** | `oftal-med.ru/` (= корень сайта на prod) |
| Prod IP | `45.90.35.63` (SSH: `almamed`) |
| Prod path | `/var/www/oftal-med.ru/data/www/oftal-med.ru` |
| Домен | https://oftal-med.ru |
| Локально | http://127.0.0.1:8101/ |

Родительская папка `oftal-med/` — обёртка Mac: дамп БД (`oftal_med_ru_db.sql`), `.cursorignore`, корневой `README.md`.

## Структура

```
oftal-med/                         # workspace (Mac)
├── oftal_med_ru_db.sql            # дамп БД (не в git)
├── .cursorignore
├── README.md
└── oftal-med.ru/                  # git root = корень сайта
    ├── .local/                    # nginx/php-fpm (Mac, soft)
    ├── docs/                      # документация
    ├── scripts/                   # dev
    ├── bitrix/
    │   ├── modules/               # ядро + сторонние/кастомные
    │   ├── components/{nbrains,niges}/
    │   ├── templates/medical-templates/
    │   └── php_interface/         # dbconn, init.php
    ├── local/modules/prime.alerts/
    ├── catalog/ personal/ about/ …
    └── upload/                    # медиа (не в git)
```

## База данных

| Параметр | Prod (из дампа/конфига) | Локально |
|----------|-------------------------|----------|
| Host | `localhost` | `127.0.0.1` |
| Database | `oftal_med_ru_db` | `oftal_med_ru_db` |
| User | `oftal_med_ru_usr` | `oftal_med_local` |
| Password | (prod) | `oftal_med_local` |

Дамп: MariaDB 10.3 → импорт в Homebrew MySQL (`/tmp/mysql.sock` / Homebrew socket).

Инфоблок каталога: `IBLOCK_CATALOG = 33` (`bitrix/php_interface/init.php`).

Сайт в БД: `LID=s1`, `SERVER_NAME=oftal-med.ru`.

## Локальная разработка (Mac, soft)

Порты: **8101** (nginx), **9101** (php-fpm). MySQL 3306 (Homebrew).

```bash
cd oftal-med.ru
cp .local/db.env.example .local/db.env   # один раз
./scripts/setup-local-db.sh --background # один раз, щадящий импорт
./scripts/start-dev.sh
./scripts/stop-dev.sh
```

Soft-режим:

- php-fpm `ondemand`, max **2** workers
- `memory_limit` 512M, opcache 64M
- импорт дампа в фоне (`--background`), без параллельной нагрузки

`apply-local-db-config.sh` пишет `dbconn.local.php` и правит `.settings.php` под локальные креды; prod-копии кладёт в `.local/backup/`.

### Занятые порты (соседние проекты)

| Порт | Проект |
|------|--------|
| 8080 | almamed |
| 8082 | vilmed |
| 8084 | polimer |
| 8085 | lormag |
| 8086 | metplus-vrn |
| 8087 | oftalmag |
| 8088 | metprof-vrn |
| 8090 | medplakaty |
| 8093 | dckljaksa |
| 8094 | fasad36 |
| 8095 | gnkmed |
| 8096 | gorexpert |
| 8097 | kawe |
| 8098 | lorshop |
| 8099 | proclimate36 |
| 8100 | medplakaty (alt) |
| **8101** | **oftal-med** |
| **9101** | **oftal-med php-fpm** |

## Разделы сайта

| Путь | Назначение |
|------|------------|
| `/` | Главная (слайдер, категории, новости) |
| `/catalog/` | Каталог |
| `/personal/` | ЛК, корзина `/personal/cart/`, заказ `/personal/order/` |
| `/about/` `/dostavka/` `/oplata/` `/kontakty/` | Инфо |
| `/klientam/` `/vacancies/` | Клиентам / вакансии |
| `/news/` `/articles/` | Контент |
| `/auth/` `/login/` | Авторизация |
| `/bitrix/admin/` | Админка Bitrix |

## Кастом / заметки

- `hand1CtoSite.php` — обмен с 1С
- `prime.alerts` — политика email/алертов (`local/modules/`)
- В `dbconn.php` на prod жёстко `SERVER_PORT=443` — локально перекрывается `dbconn.local.php`
- Композит/html_pages при старте soft-стенда отключается (`.enabled` → `.enabled.local-off`)
- В `index.php` ключи массива без кавычек (`$arSection[ID]`) — на PHP 8 fatal; для локалки нужны кавычки

## Git — что в репозитории

**В git:** код сайта, `bitrix/modules/`, шаблоны, компоненты, `scripts/`, `docs/`, `.local/*.example`.

**Не в git:** `upload/`, кэш Bitrix, секреты (`.settings.php`, `dbconn.php`, `license_key.php`, `.htaccess`), дампы `*.sql`.

## Деплой на prod

**Git после правок — всегда.** **Prod — только по явной просьбе.**

| | |
|---|---|
| SSH / host | `almamed` → `45.90.35.63` |
| Path | `/var/www/oftal-med.ru/data/www/oftal-med.ru` |
| Remote | https://github.com/bziksv/oftal-med |

```bash
cd oftal-med.ru
git add … && git commit -m "…" && git push origin main

# только когда пользователь просит выкатить на сервер
```

**Запрещено:** автодеплой на prod, правки на prod без commit, `scp` файлов кода.

## Проверка

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://oftal-med.ru/
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8101/
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8101/catalog/
```

## Совместимость PHP 8.3

В `index.php` (блок популярных категорий) ключи массива были без кавычек (`$arSection[ID]`) — на PHP 8 это fatal. Для локалки исправлено на `$arSection['ID']` и т.п.
