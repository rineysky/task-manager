<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use App\Entity\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @param UserInterface $user
     * @param \DateTime|null $startDate
     * @param \DateTime|null $dueDate
     * @param bool $activeOnly
     *
     * @return Task[]
     */
    public function getActiveByUserAndDates(
        UserInterface $user,
        ?\DateTime $startDate,
        ?\DateTime $dueDate,
        bool $activeOnly = true
    ): array {
        $qb = $this->createQueryBuilder('t');
        $expr = $qb->expr();

        $qb->where($expr->eq('t.user', ':user'))
            ->setParameter('user', $user);

        if (null !== $startDate && null !== $dueDate) {
            $qb->andWhere(
                $expr->orX(
                    $expr->andX(
                        $expr->lte('t.startDate', ':startDate'),
                        $expr->gte('t.dueDate', ':startDate')
                    ),
                    $expr->andX(
                        $expr->lte('t.startDate', ':dueDate'),
                        $expr->gte('t.dueDate', ':dueDate')
                    ),
                    $expr->andX(
                        $expr->gte('t.startDate', ':startDate'),
                        $expr->lte('t.dueDate', ':dueDate')
                    )
                ))
                ->setParameter('startDate', $startDate)
                ->setParameter('dueDate', $dueDate);
        } elseif (null !== $startDate && null === $dueDate) {
            $qb->andWhere($expr->gte('t.dueDate', ':startDate'))
                ->setParameter('startDate', $startDate);
        } elseif (null === $startDate && null !== $dueDate) {
            $qb->andWhere($expr->lte('t.startDate', ':dueDate'))
                ->setParameter('dueDate', $dueDate);
        }

        if ($activeOnly) {
            $qb->innerJoin('t.status', 'ts')
                ->andWhere($expr->eq('ts.handle', ':taskStatus'))
                ->setParameter('taskStatus', TaskStatus::PENDING);
        }

        return $qb->orderBy('t.startDate')
            ->getQuery()
            ->getResult();
    }
}
