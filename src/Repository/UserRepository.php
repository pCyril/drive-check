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
    public function getByRoleQuery(string $role)
    {
        if ($role === 'ROLE_USER') {
            $qb = $this->createQueryBuilder('u');
            $qb->select('u')
                ->andWhere($qb->expr()->notLike('u.roles', ':role'))->setParameter('role', "%ROLE_ADMIN%");
        } else {
            $qb = $this->createQueryBuilder('u');
            $qb->select('u')
                ->andWhere($qb->expr()->like('u.roles', ':role'))->setParameter('role', "%ROLE_ADMIN%");
        }

        return $qb->getQuery();
    }
}
