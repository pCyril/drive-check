<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ProductAlertRepository extends EntityRepository
{
    public function getProductAlertsByUserQuery($user)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->join('p.store', 's')
            ->andWhere('p.user = :user')->setParameter('user', $user);

        return $qb->getQuery();
    }

    public function getStores(){
        $qb = $this->createQueryBuilder('a')
            ->groupBy('a.store, a.storeId');

        return $qb->getQuery();
    }
}
