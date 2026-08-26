<?php

namespace Skyweb24\ChatgptSeo\Helper;

use Bitrix\Main\License;
use Skyweb24\ChatgptSeo\Enum\EnumSettingApp;

class HelperIframeSource
{
    public static function get(string $url): string
    {
        $bitrixKey = (new License())->getKey();

        return sprintf(
            '%s?module_code=%s&license_hash=%s&url=%s',
            EnumSettingApp::HTTP_API_SERVER . "/statistics",
            EnumSettingApp::MODULE_CODE,
            HelperBitrixKey::toHash($bitrixKey),
            $url
        );
    }
}