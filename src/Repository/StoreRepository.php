<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class StoreRepository extends EntityRepository
{
    public function getStores(){
        $qb = $this->createQueryBuilder('s');

        return $qb->getQuery();
    }
}
