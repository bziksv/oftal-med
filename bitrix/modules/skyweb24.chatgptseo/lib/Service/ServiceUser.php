<?php

namespace Skyweb24\ChatgptSeo\Service;

use Skyweb24\ChatgptSeo\Repository\RepositoryTask;
use Skyweb24\ChatgptSeo\Repository\RepositoryUser;

class ServiceUser
{
    public function __construct(
        protected RepositoryUser $repositoryUser,
        protected RepositoryTask $repositoryTask,
    )
    {
    }

    public function getUniqueUserNameList(): array
    {
        $uniqueUserIdList = $this->repositoryTask->getUniqueUserList();

        return $this->modifyUserNameList(
            $this->repositoryUser->getUserNamesByIdList($uniqueUserIdList)
        );
    }

    protected function modifyUserNameList(array $userNameList): array
    {
        foreach ($userNameList as $id => $userName) {
            $result[] = [
                'id'   => (int)$id,
                'name' => $userName,
            ];
        }
        return $result ?? [];
    }
}