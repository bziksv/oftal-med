<?php

namespace Skyweb24\ChatgptSeo\Helper;

class HelperBitrixKey
{
    public static function toHash(string $key): string
    {
        return md5("BITRIX" . $key . "LICENCE");
    }
}