<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use LineStorm\SearchBundle\Search\AbstractSearchProvider;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

/**
 * Class MyIsamFullTextSearchProvider
 *
 * @package LineStorm\SearchBundle\Search\Provider
 */
abstract class FullTextSearchProvider extends AbstractSearchProvider implements SearchProviderInterface
{
    private $rowCount;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'full_text';
    }

    /**
     * @inheritdoc
     */
    abstract public function getRoute($entity);

    /**
     * @inheritdoc
     */
    public function queryBuilder(QueryBuilder $qb, $alias)
    {}


    /**
     * @inheritdoc
     */
    public function search($query, $hydration = Query::HYDRATE_OBJECT)
    {
        $alias         = 't';
        $repo          = $this->modelManager->get($this->getName());
        $qb            = $repo->createQueryBuilder($alias);

        $this->queryBuilder($qb, $alias);

        $i = 0;
        foreach($this->getIndexFields() as $field => $properties)
        {
            if(is_array($properties))
            {
                $idx = "i{$i}";
                $qb->leftJoin($alias.'.'.$field, $idx)->addSelect($idx);
                ++$i;
                foreach($properties as $subField)
                {
                    $qb->orWhere("{$idx}.{$subField} LIKE :query");
                }
            }
            else
            {
                $qb->orWhere("{$alias}.{$field} LIKE :query");
            }
        }

        $query  = $qb->getQuery()->setParameter('query', "%{$query}%");

        $result = $query->setMaxResults(20)->getResult($hydration);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function index($entities = null)
    {
        $em      = $this->modelManager->getManager();
        $repo    = $this->modelManager->get($this->getName());
        $triRepo = $this->modelManager->get($this->getTriGraph());

        $className = $repo->getClassName();
        if($entities instanceof $className)
        {
            $entities = array($entities);
        }
        elseif($entities === null)
        {
            $entities = $repo->findAll();
        }
        else
        {
            throw new EntityNotSupportedException($entities);
        }

        foreach($entities as $entity)
        {
            $em->beginTransaction();

            /** @var TriGraph $truple */
            $deleteQb = $triRepo->createQueryBuilder('t');
            $deleteQb->delete()->where("t.entity = :entity");
            $deleteQb->setParameter(':entity', $entity);

            $deleteQb->getQuery()->execute();

            foreach($this->getIndexFields() as $field => $properties)
            {
                if(is_array($properties))
                {
                    foreach($properties as $subField)
                    {
                        $subEntities = $entity->{"get{$field}"}();
                        if($subEntities instanceof Collection)
                        {
                            foreach($subEntities as $subEntity)
                            {
                                $this->createTupleEntities($em, $entity, $subEntity->{"get{$subField}"}());
                            }
                        }
                        else
                        {
                            $this->createTupleEntities($em, $entity, $subEntities->{"get{$subField}"}());
                        }
                    }
                }
                else
                {
                    $this->createTupleEntities($em, $entity, $entity->{"get{$field}"}());
                }
            }

            $em->commit();
            $em->flush();

        }
    }

    /**
     * @inheritdoc
     */
    public function remove($entity)
    {
        $em      = $this->modelManager->getManager();
        $triRepo = $this->modelManager->get($this->getTriGraph());
        $inverse = $this->getInverseSide();

        $em->beginTransaction();

        /** @var TriGraph $truple */
        $deleteQb = $triRepo->createQueryBuilder('t');
        $deleteQb->delete()->where("t.{$inverse} = :inv");
        $deleteQb->setParameter(':inv', $entity);

        $deleteQb->getQuery()->execute();

        $em->commit();
    }

    /**
     * @inheritdoc
     */
    public function getCount($fromCache = true)
    {
        if($this->rowCount === null || !$fromCache)
        {
            $repo = $this->modelManager->get($this->getTriGraph());

            $qb             = $repo->createQueryBuilder('tri')->select('count(tri.tuple) as row_count');
            $this->rowCount = (int)$qb->getQuery()->getSingleScalarResult();
        }

        return $this->rowCount;
    }

} 
