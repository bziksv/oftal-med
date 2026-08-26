<?php

namespace Skyweb24\ChatgptSeo\Service\Api\Module\Dto;

class DtoClientInfo
{
    public function __construct(
        public ?array $modelOptions = [],
        public bool   $isDemo = true,
    )
    {
    }

}