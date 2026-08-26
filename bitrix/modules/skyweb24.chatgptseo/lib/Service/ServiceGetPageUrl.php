<?php

namespace Skyweb24\ChatgptSeo\Service;

class ServiceGetPageUrl
{
    public function getUrl(string $currentUri): string
    {
        return sprintf(
            '%s://%s%s',
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['SERVER_NAME'],
            $currentUri,
        );
    }
}