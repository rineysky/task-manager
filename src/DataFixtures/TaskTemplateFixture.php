<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TaskTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskTemplateFixture extends Fixture
{
    public const FIRST_TASK_REFERENCE = 'FIRST_TASK';
    public const SECOND_TASK_REFERENCE = 'SECOND_TASK';
    public const THIRD_TASK_REFERENCE = 'THIRD_TASK';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $firstTask = $this->createTaskTemplate('The first task', 'The first task description');
        $secondTask = $this->createTaskTemplate('The second task', 'The second task description');
        $thirdTask = $this->createTaskTemplate('The third task', 'The third task description');

        $manager->persist($firstTask);
        $manager->persist($secondTask);
        $manager->persist($thirdTask);
        $manager->flush();

        $this->addReference(self::FIRST_TASK_REFERENCE, $firstTask);
        $this->addReference(self::SECOND_TASK_REFERENCE, $secondTask);
        $this->addReference(self::THIRD_TASK_REFERENCE, $thirdTask);
    }

    /**
     * @param string $title
     * @param string $description
     *
     * @return TaskTemplate
     */
    private function createTaskTemplate(string $title, string $description): TaskTemplate
    {
        $taskTemplate = new TaskTemplate();
        $taskTemplate->setTitle($title);
        $taskTemplate->setDescription($description);

        return $taskTemplate;
    }
}
