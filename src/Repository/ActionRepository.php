<?php

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class ActionRepository extends EntityRepository
{
    public function getActionByUserQuery($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a')
            ->andWhere('a.user = :user')->setParameter('user', $user);

        return $qb->getQuery();
    }

    public function getStores(){
        $qb = $this->createQueryBuilder('a')
            ->groupBy('a.store, a.storeId');

        return $qb->getQuery();
    }
}
