<?php

namespace Skyweb24\Chatgptseo\Controller\Index;

use Bitrix\Main\DI\ServiceLocator;
use InvalidArgumentException;
use Skyweb24\ChatgptSeo\Aggregator\AggregatorTaskAdvanced;
use Skyweb24\ChatgptSeo\Enum\EnumStatus;
use Skyweb24\ChatgptSeo\Repository\RepositoryTask;
use Skyweb24\ChatgptSeo\Repository\RepositoryTaskElement;
use Skyweb24\ChatgptSeo\Service\Task\ServiceTaskUpdate;
use Skyweb24\ChatgptSeo\Service\TaskElement\ServiceTaskElementUpdate;
use Skyweb24\ChatgptSeo\Validator\ValidatorTask;

class ControllerTaskStart
{
    public function index(int $taskId): bool
    {
        if ((new ValidatorTask())->inWork($taskId)) {
            $dtoTaskAdvanced = $this->getTaskAdvanced($taskId);

            if (
                $dtoTaskAdvanced->getStatusId() == EnumStatus::PROGRESS ||
                $dtoTaskAdvanced->getStatusId() == EnumStatus::DONE
            ) {
                return false;
            }

            $dtoTask = $this->getTaskById($taskId);

            if (empty($dtoTask)) {
                $this->redirectToTaskList();
            }

            $this->updateTaskElementsStatus($dtoTask->id);
            $this->updateTaskStatus($dtoTask->id);
        }

        $this->redirectToTaskEdit($taskId);
        return true;
    }

    public function run(int $taskId): bool
    {
        $dtoTask = $this->getTaskById($taskId);

        if (empty($dtoTask)) {
            throw new InvalidArgumentException("Task is not found: $taskId");
        }

        if ((new ValidatorTask())->inWork($taskId)) {
            $dtoTaskAdvanced = $this->getTaskAdvanced($taskId);

            if ($dtoTaskAdvanced->getStatusId() != EnumStatus::DRAFT) {
                throw new InvalidArgumentException("Task is not draft: $taskId");
            }

            $this->updateTaskElementsStatus($dtoTask->id);
            $this->updateTaskStatus($dtoTask->id);

            return true;
        }

        throw new InvalidArgumentException("Incorrect information of task: $taskId");
    }

    private function getTaskAdvanced(int $taskId)
    {
        return ServiceLocator::getInstance()
            ->get(AggregatorTaskAdvanced::class)
            ->getById($taskId);
    }

    private function getTaskById(int $taskId)
    {
        return ServiceLocator::getInstance()
            ->get(RepositoryTask::class)
            ->getById($taskId);
    }

    private function updateTaskElementsStatus(int $taskId): void
    {
        $dtoTaskElementList = ServiceLocator::getInstance()
            ->get(RepositoryTaskElement::class)
            ->getAllByTaskId($taskId);

        foreach ($dtoTaskElementList as $dtoTaskElement) {
            ServiceLocator::getInstance()
                ->get(ServiceTaskElementUpdate::class)
                ->update($dtoTaskElement->id, ["status_id" => EnumStatus::READY_TO_WORK]);
        }
    }

    private function updateTaskStatus(int $taskId): void
    {
        ServiceLocator::getInstance()
            ->get(ServiceTaskUpdate::class)
            ->update($taskId, ["status_id" => EnumStatus::READY_TO_WORK]);
    }

    private function redirectToTaskList(): void
    {
        LocalRedirect('/bitrix/admin/skyweb24_chatgptseo_task_list.php');
    }

    private function redirectToTaskEdit(int $taskId): void
    {
        LocalRedirect('/bitrix/admin/skyweb24_chatgptseo_task_edit.php?id=' . $taskId);
    }
}
