<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$legalConfig = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';
$legalUrls = $legalConfig['images'];
?>
<noindex><span class="legal-form-consent">Я даю <a target="_blank" rel="nofollow noopener" href="<?= htmlspecialcharsbx($legalUrls['consent']) ?>">согласие</a> на обработку персональных данных в соответствии с нашей <a target="_blank" rel="nofollow noopener" href="<?= htmlspecialcharsbx($legalUrls['personal_data']) ?>">Политикой обработки персональных данных</a>.</span></noindex>
