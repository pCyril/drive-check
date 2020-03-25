<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User findOneByUsernameCanonical(string $username)
 */
class UserRepository extends EntityRepository
{
    /**
     * @param string $role
     * @return mixed
     */
    public function findByRole(string $role)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->andWhere($qb->expr()->like('u.roles', ':role'))->setParameter('role', "%" . $role . "%");

        return $qb->getQuery()->getResult();
    }
    /**
     * @param string $role
     * @return mixed
     */
    public function getByRoleQuery(string $role)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->andWhere($qb->expr()->like('u.roles', ':role'))->setParameter('role', "%" . $role . "%");

        return $qb->getQuery();
    }
}
