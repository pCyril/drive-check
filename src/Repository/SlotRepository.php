<?php

namespace App\Repository;

use App\Entity\Slot;
use App\Entity\Store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Slot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Slot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Slot[]    findAll()
 * @method Slot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slot::class);
        
    }

    public function getSlotsLastTwentyFourHours(Store $store){
        $lastTwentyFourHours = (new \DateTime())->modify('-1 day');
        $qb = $this->createQueryBuilder('s')
            ->where('s.createdAt > :lastTwentyFourHours')
            ->andWhere('s.store = :store')
            ->setParameters(['lastTwentyFourHours' => $lastTwentyFourHours, 'store' => $store]);

        return $qb->getQuery()->getResult();
    }
}
