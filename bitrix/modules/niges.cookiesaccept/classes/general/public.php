<?php
class CNigesCookiesAcceptPublic
{
	protected static $deferredHtml = '';

	/**
	 * Render the cookie notice during epilog, but keep the markup for
	 * insertion before </body>. OnEpilog runs after the template footer
	 * has already closed the document.
	 */
	public static function OnEpilog()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$html = ob_get_clean();
		if (!is_string($html) || trim($html) === '') {
			return;
		}

		self::$deferredHtml = $html;
		AddEventHandler('main', 'OnEndBufferContent', array('CNigesCookiesAcceptPublic', 'OnEndBufferContent'));
	}

	public static function OnEndBufferContent(&$content)
	{
		if (self::$deferredHtml === '' || !is_string($content)) {
			return;
		}

		$pos = strripos($content, '</body>');
		if ($pos === false) {
			return;
		}

		$content = substr($content, 0, $pos).self::$deferredHtml.substr($content, $pos);
		self::$deferredHtml = '';
	}
}
