<?php

namespace Skyweb24\ChatgptSeo\Service;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Skyweb24\ChatGptSeo\Core\Helper\HelperSystem;
use Skyweb24\ChatgptSeo\Enum\EnumSettingApp;
use Skyweb24\ChatgptSeo\Helper\HelperBitrixKey;
use Skyweb24\ChatgptSeo\Repository\RepositorySetting;

class ServiceModuleActivate
{
    const HTTP_API_SERVER_BUS = EnumSettingApp::HTTP_API_SERVER_BUS . "/api";

    public function __construct(
        protected HttpClient        $httpClient,
        protected RepositorySetting $repositorySetting,
        protected string            $bitrixKey,
        protected string            $moduleCode
    )
    {
    }

    public function activate(): bool
    {
        if ($this->repositorySetting->getValue(EnumSettingApp::MODULE_IS_DEMO) !== "N") {
            if ($this->needToActivateModule()) {
                return $this->activateModuleOnApi();
            }

            return false;
        }

        return false;
    }

    private function needToActivateModule(): bool
    {
        if ($this->moduleIsDemo()) {
            return false;
        }

        if ($this->moduleIsDemoOnBus()) {
            return false;
        }

        return true;
    }

    private function moduleIsDemoOnBus(): bool
    {
        $response = $this->httpClient->post(
            self::HTTP_API_SERVER_BUS . "/client/isDemo/",
            Json::encode([
                "client_license_hash" => HelperBitrixKey::toHash($this->bitrixKey),
                "module_code"         => $this->moduleCode,
            ])
        );

        return Json::decode($response)['result'] ?? true;
    }

    private function activateModuleOnApi(): bool
    {
        $response = $this->httpClient->post(
            EnumSettingApp::HTTP_API_SERVER . "/api/client/activate/",
            Json::encode([
                "clientKeyHash" => HelperBitrixKey::toHash($this->bitrixKey),
            ])
        );

        $moduleIsActivated = Json::decode($response)['data'] ?? false;

        if ($moduleIsActivated) {
            $this->repositorySetting->setValue(EnumSettingApp::MODULE_IS_DEMO, "N");
            return true;
        }
        return false;
    }

    private function moduleIsDemo(): bool
    {
        return HelperSystem::moduleIsDemo($this->moduleCode);
    }
}
