<?php

namespace Skyweb24\ChatgptSeo\Service\TaskElement;

use Skyweb24\ChatgptSeo\Repository\RepositoryTask;
use Skyweb24\ChatgptSeo\Repository\RepositoryTaskElement;

class ServiceTaskElementCreate
{
    public function __construct(protected RepositoryTask        $repositoryTask,
                                protected RepositoryTaskElement $repositoryTaskElement,

    )
    {
    }

    public function create(array $data): bool
    {
        if ($this->repositoryTaskElement->add($data)->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }

    public function addNewElementsFromIBlock(array $elementsId): int
    {
        global $USER;
        $taskId = $this->repositoryTask->add(
            [
                'iblock_id' => \CIBlockElement::GetIBlockByID($elementsId[0]),
                'status_id' => '3',
                'user_id'   => $USER->GetID(),
            ])->getId();
        foreach ($elementsId as $elementId) {
            $this->repositoryTaskElement->add([
                'task_id'    => $taskId,
                'element_id' => $elementId,
                'status_id'  => '3',
            ]);
        }
        return $taskId;
    }

    public function addElementListByTaskId(array $elementIdList, int $taskId): bool
    {
        foreach ($elementIdList as $elementId) {
            if (!$this->repositoryTaskElement->add([
                'task_id'    => $taskId,
                'element_id' => $elementId,
                'status_id'  => '3',
            ])->isSuccess()) {
                return false;
            }
        }

        return true;
    }

    public function addElementIdByTaskId(int $elementId, int $taskId): bool|int
    {
        $response = $this->repositoryTaskElement->add([
            'task_id' => $taskId,
            'element_id' => $elementId,
            'status_id' => '3',
        ]);
        if (!$response->isSuccess()) {
            return false;
        }

        return $response->getId();
    }
}