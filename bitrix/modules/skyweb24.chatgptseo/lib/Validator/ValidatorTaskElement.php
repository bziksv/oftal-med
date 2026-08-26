<?php

namespace Skyweb24\ChatgptSeo\Validator;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use InvalidArgumentException;
use Skyweb24\ChatgptSeo\Dto\DtoTask;
use Skyweb24\ChatgptSeo\Model\ModelTaskElementsTable;
use Skyweb24\ChatgptSeo\Repository\RepositoryIblockElement;
use Skyweb24\ChatgptSeo\Repository\RepositoryTask;

class ValidatorTaskElement
{
    public function addTask(int $taskId, int $elementId): bool
    {
        $row = ModelTaskElementsTable::getList([
            'select' => ['*'],
            'filter' => [
                'task_id'    => $taskId,
                'element_id' => $elementId,
            ],
        ])->fetch();

        if ($row) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws ArgumentException
     */
    public function isValidForGetById(int $taskId): bool
    {
        try {
            $this->validateTaskId($taskId);
        } catch (InvalidArgumentException $e) {
            throw new ArgumentException($e->getMessage());
        }

        return true;
    }

    /**
     * @throws ArgumentException
     */
    public function isValidForAdd(int $taskId, int $elementId): bool
    {
        try {
            $dtoTask = $this->validateTaskId($taskId);
            $this->validateElementId($elementId, $dtoTask);
        } catch (InvalidArgumentException $e) {
            throw new ArgumentException($e->getMessage());
        }

        return true;
    }

    private function validateTaskId(int $taskId): DtoTask
    {
        /** @var RepositoryTask $repositoryTask */
        $repositoryTask = ServiceLocator::getInstance()->get(RepositoryTask::class);
        if (!$dtoTask = $repositoryTask->getById($taskId)) {
            throw new InvalidArgumentException("Task is not found: " . $taskId);
        }

        return $dtoTask;
    }

    private function validateElementId(int $elementId, DtoTask $dtoTask): void
    {
        /** @var RepositoryIblockElement $repositoryIblockElement */
        $repositoryIblockElement = ServiceLocator::getInstance()->get(RepositoryIblockElement::class);
        if (!$dtoIblockElement = $repositoryIblockElement->getById($elementId)) {
            throw new InvalidArgumentException("Element is not found: " . $elementId);
        }

        if ($dtoTask->iblock_id != $dtoIblockElement->iblockId) {
            throw new InvalidArgumentException("This element is in other iblock: " . $elementId);
        }

        if ($this->addTask($dtoTask->id, $elementId)) {
            throw new InvalidArgumentException("This element is already in task: " . $elementId);
        }
    }
}