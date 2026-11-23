<?php
/**
 * Капча на прямой заход в catalog/search (порт vilmed vm_catalog_gate).
 * Сайт: oftal-med.ru
 */

function s4CatalogGateSecret(): string
{
	static $secret = null;
	if ($secret !== null) {
		return $secret;
	}
	$secret = (string)\Bitrix\Main\Config\Option::get('main', 'captcha_password', '');
	if ($secret === '') {
		$secret = hash('sha256', 'oftal-med-catalog-gate|' . (string)\Bitrix\Main\Config\Option::get('main', 'server_name', 'oftal-med.ru'));
	}
	return $secret;
}

function s4CatalogGateCookieValid(): bool
{
	$raw = (string)($_COOKIE['omed_cg'] ?? '');
	$parts = explode('.', $raw, 2);
	if (count($parts) !== 2 || !ctype_digit($parts[0])) {
		return false;
	}
	if ((int)$parts[0] < time()) {
		return false;
	}
	$expected = hash_hmac('sha256', $parts[0], s4CatalogGateSecret());
	return hash_equals($expected, $parts[1]);
}

function s4CatalogGateSetCookie(): void
{
	$expires = time() + 14 * 86400;
	$value = $expires . '.' . hash_hmac('sha256', (string)$expires, s4CatalogGateSecret());
	$secure = false;
	try {
		$secure = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps();
	} catch (\Throwable $e) {
		$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
	}
	setcookie('omed_cg', $value, [
		'expires' => $expires,
		'path' => '/',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Lax',
	]);
	$_COOKIE['omed_cg'] = $value;
}

function s4CatalogGateIsSearchBot(string $ua): bool
{
	return (bool)preg_match(
		'/Googlebot|Google-InspectionTool|Storebot-Google|AdsBot-Google|Mediapartners-Google|YandexBot|YandexImages|YandexMobileBot|YandexWebmaster|YandexMarket|YandexMetrika|bingbot|BingPreview|Mail\.RU_Bot|Applebot|DuckDuckBot|Baiduspider|PetalBot|s4-warmup|s4-psi/i',
		$ua
	);
}

function s4CatalogGateSameSiteReferer(): bool
{
	$ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
	if ($ref === '') {
		return false;
	}
	$host = strtolower((string)parse_url($ref, PHP_URL_HOST));
	return in_array($host, ['oftal-med.ru','www.oftal-med.ru'], true);
}

function s4CatalogGateNeedsCaptcha(): bool
{
	$uri = (string)($_SERVER['REQUEST_URI'] ?? '');
	$path = (string)parse_url($uri, PHP_URL_PATH);
	$query = (string)parse_url($uri, PHP_URL_QUERY);

	// Прямой /search/ (в т.ч. ?q=...) — всегда дорогой
	if (preg_match('#^/search(?:/|$)#', $path)) {
		return true;
	}

	// /catalog или /product с «хвостами»
	if (!preg_match('#^/(?:catalog|product)(?:/|$)#', $path)) {
		return false;
	}
	if ($query === '') {
		return false;
	}

	$params = [];
	parse_str($query, $params);
	if (
		isset($params['sort'])
		|| isset($params['view'])
		|| isset($params['q'])
		|| isset($params['section_id'])
		|| isset($params['PAGE_ELEMENT_COUNT'])
		|| isset($params['TEMPLATE_THEME'])
		|| isset($params['sphrase_id'])
	) {
		return true;
	}
	foreach ($params as $name => $value) {
		if (stripos((string)$name, 'PAGEN') === 0) {
			return true;
		}
	}
	return false;
}

function s4CatalogGatePass(string $uri): void
{
	$safe = htmlspecialchars($uri, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: no-store');
		http_response_code(200);
	}
	echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">'
		. '<meta http-equiv="refresh" content="0;url=' . $safe . '">'
		. '<title>Открываем страницу</title></head><body>'
		. '<p>Код принят. <a href="' . $safe . '">Продолжить</a></p>'
		. '<script>location.replace(' . json_encode($uri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ');</script>'
		. '</body></html>';
	die();
}

function s4CatalogGateShow(string $error = ''): void
{
	global $APPLICATION;
	$sid = $APPLICATION->CaptchaGetCode();
	$uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
	$action = htmlspecialchars($uri, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$sidEsc = htmlspecialchars((string)$sid, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$errorHtml = $error !== ''
		? '<p class="s4-gate-error">' . htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
		: '';
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('X-Robots-Tag: noindex, nofollow');
		http_response_code(200);
	}
	echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">'
		. '<meta name="viewport" content="width=device-width, initial-scale=1">'
		. '<meta name="robots" content="noindex, nofollow">'
		. '<title>Подтвердите, что вы не робот</title>'
		. '<style>'
		. 'html,body{margin:0;height:100%;background:#f1f5f9;color:#1e293b;font:16px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}'
		. 'body{display:flex;align-items:center;justify-content:center;padding:24px;}'
		. '.s4-gate{width:100%;max-width:420px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 12px 40px rgba(15,23,42,.08);padding:28px 24px 24px;}'
		. '.s4-gate h1{margin:0 0 8px;font-size:22px;line-height:1.3;}'
		. '.s4-gate p{margin:0 0 16px;color:#475569;}'
		. '.s4-gate-error{color:#b91c1c;font-weight:600;}'
		. '.s4-gate img{display:block;margin:0 0 12px;border:1px solid #e2e8f0;border-radius:8px;}'
		. '.s4-gate label{display:block;margin:0 0 6px;font-size:14px;color:#475569;}'
		. '.s4-gate input[type=text]{width:100%;box-sizing:border-box;height:44px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:18px;letter-spacing:.08em;}'
		. '.s4-gate button{margin-top:14px;width:100%;height:46px;border:0;border-radius:8px;background:#0b3961;color:#fff;font-size:16px;font-weight:600;cursor:pointer;}'
		. '.s4-gate button:hover{background:#082a47;}'
		. '</style></head><body><div class="s4-gate">'
		. '<h1>Подтвердите, что вы не робот</h1>'
		. '<p>Страница поиска или каталога открыта напрямую. Введите код с картинки.</p>'
		. $errorHtml
		. '<form method="post" action="' . $action . '">'
		. '<input type="hidden" name="s4_catalog_gate" value="1">'
		. '<input type="hidden" name="captcha_sid" value="' . $sidEsc . '">'
		. '<img src="/bitrix/tools/captcha.php?captcha_sid=' . $sidEsc . '" width="180" height="40" alt="Код">'
		. '<label for="s4-captcha-word">Код с картинки</label>'
		. '<input id="s4-captcha-word" type="text" name="captcha_word" autocomplete="off" required autofocus>'
		. '<button type="submit">Продолжить</button>'
		. '</form></div></body></html>';
	die();
}

function s4CatalogGateRun(): void
{
	if (PHP_SAPI === 'cli') {
		return;
	}
	$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
	if (
		strpos($host, 'localhost') !== false
		|| strpos($host, '127.0.0.1') !== false
	) {
		return;
	}
	$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
	if ($method !== 'GET' && $method !== 'HEAD' && $method !== 'POST') {
		return;
	}
	if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
		return;
	}
	if (!s4CatalogGateNeedsCaptcha()) {
		return;
	}
	if (s4CatalogGateIsSearchBot((string)($_SERVER['HTTP_USER_AGENT'] ?? ''))) {
		return;
	}
	if (s4CatalogGateCookieValid()) {
		return;
	}
	if (s4CatalogGateSameSiteReferer()) {
		s4CatalogGateSetCookie();
		return;
	}

	if ($method === 'POST' && (string)($_POST['s4_catalog_gate'] ?? '') === '1') {
		global $APPLICATION;
		$word = (string)($_POST['captcha_word'] ?? '');
		$sid = (string)($_POST['captcha_sid'] ?? '');
		if ($word !== '' && $APPLICATION->CaptchaCheckCode($word, $sid)) {
			s4CatalogGateSetCookie();
			s4CatalogGatePass((string)($_SERVER['REQUEST_URI'] ?? '/'));
		}
		s4CatalogGateShow('Неверный код. Введите новый.');
	}

	if ($method === 'HEAD') {
		if (!headers_sent()) {
			header('Cache-Control: no-store');
			http_response_code(200);
		}
		die();
	}

	s4CatalogGateShow();
}

s4CatalogGateRun();
