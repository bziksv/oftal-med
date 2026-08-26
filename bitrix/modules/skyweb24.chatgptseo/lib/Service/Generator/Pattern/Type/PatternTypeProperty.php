<?php

namespace Skyweb24\ChatgptSeo\Service\Generator\Pattern\Type;

use Skyweb24\ChatgptSeo\Dto\DtoIblockElement;
use Skyweb24\ChatgptSeo\Dto\DtoIblockElementAdvanced;
use Skyweb24\ChatgptSeo\Enum\EnumIncorrectCode;
use Skyweb24\ChatgptSeo\Repository\RepositoryIblockElement;

class PatternTypeProperty extends PatternTypeAbstract
{
    public function __construct(
        protected RepositoryIblockElement $repositoryIblockElement,
    )
    {
    }

    public function getValue(string $pattern, DtoIblockElementAdvanced $dto): false|string
    {
        $propertyCode = $this->findCodeInPattern($pattern);
        if (is_array($propertyCode)) {
            $elementId = $dto->dtoIblockElement->propertyList[$propertyCode[0]]['VALUE'];
            $value = $this->getElementPropertyValue($this->formatElementIdList($elementId), $propertyCode[1]);
        } else {
            $value = $dto->dtoIblockElement->propertyList[$propertyCode]['VALUE'];
        }

        return $this->processValue($value);
    }

    private function processValue($value): false|string
    {
        if ($value === false) {
            return false;
        }

        return is_array($value) ? $this->combineValues($value) : (string)$value;
    }

    private function combineValues(array $value): string
    {
        return implode(', ', array_filter($value));
    }

    private function getElementPropertyValue(array $elementIdList, string $fieldName): string|false
    {

        foreach ($elementIdList as $elementId) {
            $dtoElement = $this->repositoryIblockElement->getById($elementId);
            $value[] = match ($fieldName) {
                EnumIncorrectCode::NAME->name => $dtoElement->name,
                EnumIncorrectCode::PREVIEW_TEXT->name => $dtoElement->previewText,
                EnumIncorrectCode::DETAIL_TEXT->name => $dtoElement->detailText,
                default => $this->findPropertyValueByCode($fieldName, $dtoElement),
            };
        }

        return $this->combineValues($value ?? []);
    }

    private function formatElementIdList(string|array $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }

    private function findPropertyValueByCode(string $fieldName, DtoIblockElement $dtoElement): string|false
    {
        foreach ($dtoElement->propertyList as $code => $property) {
            if ($code === $fieldName) {
                if (!empty($property["VALUE"])) {
                    return is_array($property["VALUE"]) ? $this->combineValues($property["VALUE"]) : (string)$property["VALUE"];
                }
                return false;
            }
        }
        return false;
    }
}
