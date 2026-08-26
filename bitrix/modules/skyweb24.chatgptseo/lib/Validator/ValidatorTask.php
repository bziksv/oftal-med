<?php

namespace Skyweb24\ChatgptSeo\Validator;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use InvalidArgumentException;
use Skyweb24\ChatgptSeo\Enum\EnumElementType;
use Skyweb24\ChatgptSeo\Enum\EnumOperationType;
use Skyweb24\ChatgptSeo\Repository\RepositoryTask;

class ValidatorTask
{
    public function inWork(int $taskId): bool
    {
        $row = ServiceLocator::getInstance()->get(RepositoryTask::class)->getById($taskId);

        if (
            $row->operation_type == null ||
            $row->element_type == null ||
            $row->operations == null
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @throws ArgumentException
     */
    public function isValidForAdd(array $data): bool
    {
        try {
            $this->validateUserId($data);
            $this->validateIblock($data["iblock_id"]);
            $this->validateEnum($data["operation_type"], EnumOperationType::class);
            $this->validateEnum($data["element_type"], EnumElementType::class);
            $this->validateOperations($data);
        } catch (InvalidArgumentException $e) {
            throw new ArgumentException($e->getMessage());
        }

        return true;
    }

    private function validateIblock($iblockId): void
    {
        if (!\CIBlock::GetByID($iblockId)->fetch()) {
            throw new InvalidArgumentException("Invalid iblock_id: $iblockId");
        }
    }

    private function validateUserId(array $data): void
    {
        if (empty($data['user_id'])) {
            throw new InvalidArgumentException("Missing or empty user_id");
        }

        if (!is_numeric($data['user_id']) || intval($data['user_id']) != $data['user_id']) {
            throw new InvalidArgumentException("user_id must be a numeric value: " . $data['user_id']);
        }

        if (!\CUser::GetByID($data['user_id'])->fetch()) {
            throw new InvalidArgumentException("User not found: " . $data['user_id']);
        }
    }

    private function validateEnum($value, $enumClass): void
    {
        if  (!is_string($value)) {
            throw new InvalidArgumentException("Value is not string type: $value");
        }

        if (!in_array($value, array_column($enumClass::cases(), 'value'))) {
            throw new InvalidArgumentException("Invalid value for enum $enumClass: $value");
        }
    }

    private function validateOperations(array $data): void
    {
        foreach ($data['operations'] as $operation) {
            $this->validateOperationFields($operation, $data["operation_type"]);
        }
    }

    private function validateOperationFields(array $operation, string $operationType): void
    {
        if (empty($operation['output_fields'])) {
            throw new InvalidArgumentException("Missing output_fields in operation");
        }

        switch ($operationType) {
            case EnumOperationType::CREATE->value:
                if (empty($operation['entities'])) {
                    throw new InvalidArgumentException("Missing entities in operation");
                }
                if (empty($operation['length'])) {
                    throw new InvalidArgumentException("Missing length in operation");
                }
                break;

            case EnumOperationType::REWRITE->value:
            case EnumOperationType::TRANSLATE->value:
                if (empty($operation['input_fields'])) {
                    throw new InvalidArgumentException("Missing input_fields in operation: ");
                }
                if ($operationType == EnumOperationType::TRANSLATE->value && empty($operation['languages'])) {
                    throw new InvalidArgumentException("Missing languages in operation");
                }
                break;
        }
    }
}