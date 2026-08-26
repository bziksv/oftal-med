<?php

namespace Skyweb24\ChatgptSeo\Service\Generator\Pattern\Type;

use Skyweb24\ChatgptSeo\Dto\DtoIblockElementAdvanced;
use Skyweb24\ChatgptSeo\Enum\EnumIncorrectType;

abstract class PatternTypeAbstract
{
    abstract public function getValue(string $pattern, DtoIblockElementAdvanced $dto): string|false;

    protected function findCodeInPattern(string $pattern): string|false|array
    {
        $patternTypeList = implode('|', array_column(EnumIncorrectType::cases(), "value"));
        preg_match_all('/\{(' . $patternTypeList . ')\.([A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)*)}/', $pattern, $matches);

        if (!empty($matches[2][0])) {
            if (!$parts = $this->extractPropertyCodeAndField($matches[2][0])) {
                return false;
            }

            if (count($parts) > 1) {
                return $parts;
            }
            return $matches[2][0];
        }

        return false;
    }

    private function extractPropertyCodeAndField(string $code): bool|array
    {
        return explode(".", $code);
    }
}