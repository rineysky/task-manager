<?php

declare(strict_types=1);

namespace App\Classes\Helpers\Task;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TaskCreator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @param \DateTime $startDate
     * @param \DateTime $dueDate
     * @param string $title
     * @param string $description
     * @param string $taskStatusHandle
     *
     * @return Task
     */
    public function create(
        User $user,
        \DateTime $startDate,
        \DateTime $dueDate,
        string $title,
        string $description,
        string $taskStatusHandle = TaskStatus::PENDING
    ): Task {
        $task = new Task();
        $task->setStatus($this->fetchTaskStatus($taskStatusHandle));
        $task->setUser($user);
        $task->setStartDate($startDate);
        $task->setDueDate($dueDate);
        $task->setTitle($title);
        $task->setDescription($description);

        return $task;
    }

    /**
     * @param string $taskStatusHandle
     *
     * @return TaskStatus
     */
    private function fetchTaskStatus(string $taskStatusHandle): TaskStatus
    {
        return $this->em
            ->getRepository(TaskStatus::class)
            ->findOneBy([
                'handle' => $taskStatusHandle,
            ]);
    }
}
