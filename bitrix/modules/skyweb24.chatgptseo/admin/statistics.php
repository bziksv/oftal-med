<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Skyweb24\ChatGptSeo\Core\Informer;
use Skyweb24\ChatgptSeo\Enum\EnumSettingApp;
use Skyweb24\ChatgptSeo\Helper\HelperIframeSource;
use Skyweb24\ChatgptSeo\Repository\RepositorySetting;
use Skyweb24\ChatgptSeo\Service\ServiceGetPageUrl;
use Skyweb24\ChatgptSeo\Service\ServiceModuleActivate;
use Skyweb24\ChatgptSeo\Service\ServiceUser;

global $USER;
global $APPLICATION;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");



$APPLICATION->SetTitle(Loc::getMessage('SKYWEB24_CHATGPTSEO_STATISTICS_TITLE'));

$module_id = 'skyweb24.chatgptseo';

\Bitrix\Main\UI\Extension::load("ui.alerts");
Loader::IncludeModule($module_id);
Loader::includeModule("main");
CJSCore::Init([$module_id]);

ServiceLocator::getInstance()->get(ServiceModuleActivate::class)->activate();

if (ServiceLocator::getInstance()
        ->get(RepositorySetting::class)
        ->getValue(EnumSettingApp::PERSONAL_CHAT_GPT_KEY_ACTIVE) !== "Y"): ?>
    <?php $url = ServiceLocator::getInstance()->get(ServiceGetPageUrl::class)->getUrl($APPLICATION->GetCurUri());
    $uniqueUserNameList = (ServiceLocator::getInstance()->get(ServiceUser::class)->getUniqueUserNameList());
    $src = HelperIframeSource::get($url);
    Informer::createInfo($module_id);

    // Проверка прав доступа
    $rights = $APPLICATION->GetGroupRight($module_id);

    if (!($USER->IsAdmin()) && $rights < 'R')
    {
        echo Loc::getMessage('SKYWEB24_CHATGPTSEO_YOU_HAVE_NO_PERMISSION');
        return;
    }

    ?>
    <div class="loader"></div>
    <iframe id="statistics" src="<?=$src?>"></iframe>
    <script>
        const iframe = document.getElementById('statistics');
        const uniqueUserNameList = <?= CUtil::PhpToJSObject($uniqueUserNameList, false, true); ?>

        iframe.onload = function () {
            iframe.contentWindow.postMessage({
                key: 'skyweb24.iframe',
                data: uniqueUserNameList,
            }, '*');

            iframe.classList.add('loaded');

            const loader = document.querySelector('.loader');
            loader.classList.add('hidden')
        }
    </script>

<?php else: ?>
    <div class="message">
        <div class="ui-alert ui-alert-inline ui-alert-warning">
            <span class="ui-alert-message">
                <?= Loc::getMessage('SKYWEB24_CHATGPTSEO_STATISTICS_ERROR') ?>
            </span>
        </div>
    </div>
<?php endif;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
