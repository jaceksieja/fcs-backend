<?php

namespace AppBundle\Entity;

use AppBundle\Request\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
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

        $query = $builder->getQuery();

        return $query->execute();
    }

    public function countByCriteria(Criteria $criteria)
    {
        $builder = $this->createQueryBuilder('t');
        $builder->select('count(t.id)');

        $query = $builder->getQuery();
        return $query->getSingleScalarResult();
    }

    public function add($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    private function applyFilters(QueryBuilder $builder, Criteria $criteria)
    {
        foreach ($criteria->getFilters() as $field => $value) {
            $builder->andWhere('t.'.$field.' = :'.$field);
            $builder->setParameter($field, $value);
        }
    }

    public function findNearest()
    {
        $criteria = new Criteria(['active' => true], null, null, null);

        return $this->findOneByCriteria($criteria);
    }

    public function findOneByCriteria(Criteria $criteria)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('t');
        $this->applyFilters($builder, $criteria);

        $query = $builder->getQuery();

        $results = $query->execute();
        if ($results) {
            return $results[0];
        }
    }

    public function activate($order)
    {
        $entityManager = $this->getEntityManager();

        $dql = 'UPDATE AppBundle:Order o SET o.active = 0';
        $deactivate = $entityManager->createQuery($dql);

        $dql = 'UPDATE AppBundle:Order o SET o.active = 1 WHERE o.id = :order';
        $activate = $entityManager->createQuery($dql);
        $activate->setParameter('order', $order);

        $entityManager->getConnection()->beginTransaction();

        try {
            $deactivate->execute();
            $activate->execute();
            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }
    }
}
