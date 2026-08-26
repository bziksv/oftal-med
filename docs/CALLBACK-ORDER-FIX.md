# Фикс «Заказать звонок» + оформление заказа (Bitrix)

Универсальный runbook для проектов на шаблоне `medical-templates` с компонентом `nbrains:main.feedback`.

**Эталон:** репозиторий [kawe](https://github.com/bziksv/kawe), коммиты `8d80f9a`, `94cd289`, `b9778c9`, `c30524d`, `d51c475`, `da2b3fc`, `5382fc82`.

**oftal-med.ru:** реализовано в коммите `fb77343e3` (prod выкат через rsync).

---

## Контекст

На Bitrix-сайте с шаблоном `medical-templates` (или аналогом) и компонентом `nbrains:main.feedback` (шаблон `popup-callback`) форма «Заказать звонок» вела себя так:

- при нажатии «Отправить» попап закрывался или форма **очищалась без понятного результата**;
- в инкогнито форма **молча не отправлялась**;
- без галочки согласия — **нет явной ошибки**;
- на `/personal/order/make/` — **чёрный экран** или **бесконечный лоадер** без галочки согласия.

---

## Шаг 0 — разведка (обязательно перед правками)

1. Найти компонент обратной связи:
   - `bitrix/components/nbrains/main.feedback/component.php`
   - шаблон: `bitrix/templates/<TEMPLATE>/components/nbrains/main.feedback/popup-callback/template.php`
   - подключение в `footer.php` (или `header.php`)

2. Найти JS формы:
   - обычно `bitrix/templates/<TEMPLATE>/js/functions.js`

3. Найти оформление заказа:
   - `personal/order/make/index.php`
   - `bitrix/templates/<TEMPLATE>/components/bitrix/sale.order.ajax/order.ajax/template.php`
   - `.../order.ajax/order_ajax.js`

4. Проверить composite-кеш:
   - `bitrix/php_interface/init.php`
   - параметр `COMPOSITE_FRAME_MODE` у компонента callback в footer

5. Уточнить **имена полей формы** (`PROPERTY_CODE` в footer) — в kawe это `NAME`, `PHONE`, `MAIL`, `QUERY`. В другом проекте могут отличаться — подставить реальные.

---

## Блок A — форма «Заказать звонок»

### A1. Composite + пустой sessid

**Проблема:** HTML-кеш отдаёт форму с пустым `sessid` → «сессия истекла» / тихий отказ.

**Правки:**
- В `footer.php` у `IncludeComponent("nbrains:main.feedback", "popup-callback", ...)` добавить:
  ```php
  "COMPOSITE_FRAME_MODE" => "N",
  ```
- В `bitrix/php_interface/init.php` отключать composite при POST/success:
  ```php
  if (
      (!empty($_REQUEST['success']) && is_string($_REQUEST['success']))
      || (!empty($_POST['submit']) && $_SERVER['REQUEST_METHOD'] === 'POST')
  ) {
      if (!defined('BX_COMPOSITE_DISABLED')) {
          define('BX_COMPOSITE_DISABLED', true);
      }
  }
  ```

### A2. PARAMS_HASH не совпадает (ROI_VISIT)

**Проблема:** в `$arParams` передаётся `"ROI_VISIT" => $_COOKIE['roistat_visit']` — hash в кеше ≠ hash на сервере → POST отклоняется.

**Правки в `component.php`:**
```php
$hashParams = $arParams;
unset($hashParams['ROI_VISIT']);
$arResult["PARAMS_HASH"] = md5(serialize($hashParams).$this->GetTemplateName());
```

- Убрать `"ROI_VISIT"` из параметров компонента в `footer.php` / `404.php` (если есть).
- ROI по-прежнему можно слать в письме из `$_COOKIE` внутри component.php — это не мешает hash.
- При несовпадении hash показывать явную ошибку, а не молчать:
  ```php
  if(!isset($_POST["PARAMS_HASH"]) || $arResult["PARAMS_HASH"] !== $_POST["PARAMS_HASH"]) {
      $arResult["ERROR_MESSAGE"] = array("Ошибка отправки формы. Обновите страницу и попробуйте снова.");
  }
  ```

### A3. Серверная проверка согласия

**В `component.php` после проверки обязательных полей:**
```php
$consentField = '';
if($this->GetTemplateName() == 'popup-callback')
    $consentField = 'callback-consent';
elseif($this->GetTemplateName() == 'feedback')
    $consentField = 'feedback-consent';

if($consentField !== '' && empty($_POST[$consentField]))
    $arResult["ERROR_MESSAGE"][] = 'Необходимо дать согласие на обработку персональных данных';
```

**В шаблоне `popup-callback/template.php`** — чекбокс:
```html
<input type="checkbox" id="callback-consent" name="callback-consent">
```
+ блок `.mf-consent-error` (скрытый по умолчанию).

### A4. AJAX вместо редиректа (критично)

**Проблема:** при AJAX POST сервер делает `LocalRedirect` → jQuery получает 302 с пустым телом → форма «очищается» без feedback.

**В `component.php` после успешной отправки письма:**
```php
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if($isAjax) {
    $arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
} else {
    LocalRedirect($APPLICATION->GetCurPageParam("success=".$arResult["PARAMS_HASH"], Array("success")));
}
```

### A5. JS — главные баги и UX

**Файл:** `functions.js` (или аналог).

**Обязательно реализовать:**

1. **`ensureBitrixSessid($form)`** — если `sessid` пустой, подставить `BX.bitrix_sessid()`.

2. **Клиентская валидация до AJAX:**
   - все обязательные поля заполнены;
   - `#callback-consent` отмечен;
   - при ошибке — **не отправлять**, **не очищать** форму, показать красный блок `#callback-status` + `alertify.error`.

3. **AJAX submit с перехватом `submit`:**
   ```javascript
   $(document).on('submit', '#callback form', function(e) {
       e.preventDefault();
       // validate → submitCallbackFormAjax
   });
   ```

4. **КРИТИЧНО: включать поле `submit` в POST!**
   `$form.serialize()` **не** добавляет кнопку submit при программной отправке.
   Bitrix проверяет `$_POST["submit"] <> ''` — без него сервер **игнорирует POST** и возвращает пустую форму (именно это выглядит как «очистилась и непонятно ушло или нет»).
   ```javascript
   var postData = $form.serializeArray();
   postData.push({ name: $btn.attr('name') || 'submit', value: $btn.val() || 'Отправить' });
   data: $.param(postData),
   headers: { 'X-Requested-With': 'XMLHttpRequest' }
   ```

5. **Парсинг ответа через `DOMParser`**, искать `#callback` в HTML:
   - успех → `.mf-ok-text` → зелёный статус + `alertify.success`, попап **остаётся открытым**;
   - ошибка → `.errortext` → красный статус + `alertify.error`;
   - **убрать** fallback `document.write(html)` — он ломает страницу.

6. **`openCallbackPopup()`** — не вызывать `bPopup()` повторно, если `#callback` уже виден (bPopup toggle закрывает попап).

7. **Кнопка:** на время запроса `disabled` + текст «Отправка...».

8. **Стили** в `popup-callback/style.css`:
   `.callback-status`, `--error`, `--success`, `--loading`.

9. **Баг в `validateConsentForm`:** использовать параметр `formEl`, **не** несуществующую переменную `form`.

---

## Блок B — оформление заказа `/personal/order/make/`

### B1. Чёрный экран

**В `personal/order/make/index.php`:**
```php
'DISABLE_BASKET_REDIRECT' => 'Y',
```

### B2. Бесконечный лоадер без согласия

**Проблема:** встроенный Bitrix `USER_CONSENT` блокирует сохранение, но кастомная галочка не связана с ним.

**В `personal/order/make/index.php`:**
```php
'USER_CONSENT' => 'N',
'USER_CONSENT_IS_CHECKED' => 'N',
'USER_CONSENT_IS_LOADED' => 'N',
```

**В `order.ajax/template.php`** — своя галочка:
```html
<input type="checkbox" id="bx-soa-custom-consent" name="bx-soa-custom-consent">
<div class="bx-soa-consent-error" style="display:none;">...</div>
```

**В `order_ajax.js` в `clickOrderSaveAction`:**
```javascript
if (!this.isCustomConsentChecked()) {
    this.showCustomConsentError();
    this.endLoader(); // важно — снять лоадер!
    return BX.PreventDefault(event);
}
```
+ методы `getCustomConsentCheckbox`, `isCustomConsentChecked`, `showCustomConsentError`, `hideCustomConsentError`.

---

## Блок C — после деплоя

1. Закоммитить, задеплоить на prod.
2. Очистить кеш на сервере:
   ```bash
   rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/*
   rm -rf bitrix/html_pages/*   # если есть composite HTML
   ```
3. Проверить в **инкогнито** с жёстким обновлением (Ctrl+F5).

---

## Чеклист тестирования (все сценарии обязательны)

### «Заказать звонок»

| # | Действие | Ожидание |
|---|----------|----------|
| 1 | Открыть попап → сразу «Отправить» | Красная ошибка «Заполните поля...», форма **не** очищается, попап открыт |
| 2 | Заполнить поля, **без** галочки → «Отправить» | Ошибка про согласие, форма не очищается |
| 3 | Всё заполнено + галочка → «Отправить» | «Спасибо, ваше сообщение принято.» — зелёный блок + alertify, форма заменена текстом успеха, попап открыт |
| 4 | Инкогнито, повторить п.3 | То же самое |
| 5 | curl POST с `X-Requested-With: XMLHttpRequest` + cookie + sessid + PARAMS_HASH + submit | HTTP 200, в HTML есть `mf-ok-text`, **нет** 302 |

### Оформление заказа

| # | Действие | Ожидание |
|---|----------|----------|
| 1 | `/personal/order/make/` с товаром в корзине | Страница грузится, не чёрный экран |
| 2 | «Оформить» без галочки согласия | Ошибка у галочки, **нет** бесконечного лоадера |
| 3 | С галочкой | Заказ оформляется |

---

## Что адаптировать под каждый проект

- Пути шаблона (`medical-templates` → свой).
- `PROPERTY_CODE` / имена полей в JS-валидации.
- `EMAIL_TO`, `IBLOCK_ID`, `EVENT_MESSAGE_ID`.
- URL юридических документов в тексте согласия.
- SSH/host/deploy-скрипт проекта.
- **Не трогать** другие сайты на том же сервере, если не указано явно.

---

## Критерий «готово»

Пользователь **всегда** видит один из трёх исходов: ошибка валидации (форма на месте), ошибка сервера (текст ошибки), успех (явное сообщение). Никаких «форма очистилась и непонятно что произошло».
