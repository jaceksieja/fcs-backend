<?php

namespace AppBundle\Entity;

use AppBundle\Request\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository
{
    public function findByCriteria(Criteria $criteria)
    {
        $builder = $this->createQueryBuilder('t');

        $orderBy = $criteria->getOrderBy();
        if (null !== $orderBy) {
            foreach ($orderBy as $field => $order) {
                $builder->addOrderBy('t.' . $field, $order);
            }
        }

        $builder->setMaxResults($criteria->getCount());
        $offset = ($criteria->getPage() - 1) * $criteria->getCount();
        $builder->setFirstResult($offset);
        $builder->andWhere('t.deletedAt IS NULL');

        $query = $builder->getQuery();

        return $query->execute();
    }

    public function countByCriteria(Criteria $criteria)
    {
        $builder = $this->createQueryBuilder('t');
        $builder->select('count(t.id)');
        $builder->andWhere('t.deletedAt IS NULL');


        $query = $builder->getQuery();
        return $query->getSingleScalarResult();
    }

    public function add($entity)
    {
        $this->getEntityManager()->persist($entity);
    }
}
