<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskStatusFixture extends Fixture
{
    public const PENDING_STATUS_REFERENCE = 'PENDING';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $taskStatuses = $this->createTaskStatuses($manager);

        $manager->flush();

        $this->addReference(self::PENDING_STATUS_REFERENCE, $taskStatuses[TaskStatus::PENDING]);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return TaskStatus[]
     */
    private function createTaskStatuses(ObjectManager $manager): array
    {
        $taskStatuses = [];

        foreach (TaskStatus::ALL_STATUSES as $taskStatusHandle) {
            $taskStatus = $this->createTaskStatus($taskStatusHandle);
            $manager->persist($taskStatus);

            $taskStatuses[$taskStatusHandle] = $taskStatus;
        }

        return $taskStatuses;
    }

    /**
     * @param string $handle
     *
     * @return TaskStatus
     */
    private function createTaskStatus(string $handle): TaskStatus
    {
        return new TaskStatus($handle, \ucfirst(\strtolower($handle)));
    }
}
