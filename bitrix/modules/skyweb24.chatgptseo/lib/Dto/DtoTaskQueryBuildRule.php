<?php

namespace Skyweb24\ChatgptSeo\Dto;

class DtoTaskQueryBuildRule
{
    public function __construct(
        public ?array $operations = [],
    ){

    }
}