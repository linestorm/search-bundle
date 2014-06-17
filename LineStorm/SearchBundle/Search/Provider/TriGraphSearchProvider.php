<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LineStorm\SearchBundle\Model\TriGraph;
use LineStorm\SearchBundle\Search\AbstractSearchProvider;
use LineStorm\SearchBundle\Search\Exception\EntityNotSupportedException;

/**
 * Class TriGraphSearchProvider
 *
 * @package LineStorm\SearchBundle\Search\Provider
 */
abstract class TriGraphSearchProvider extends AbstractSearchProvider
{
    private $rowCount;

    /**
     * @inheritdoc
     */
    final public function getType()
    {
        return 'tri_graph';
    }

    /**
     * @inheritdoc
     */
    abstract public function getRoute($entity);

    /**
     * @inheritdoc
     */
    abstract public function getTriGraphModel();

    /**
     * @inheritdoc
     */
    public function queryBuilder(QueryBuilder $qb, $alias)
    {
    }


    /**
     * @inheritdoc
     */
    public function search($query, $hydration = Query::HYDRATE_OBJECT)
    {
        $sqlParts = $this->parseText($query);

        $alias         = 't';
        $repo          = $this->modelManager->get($this->getModel());
        $triGraphClass = $this->modelManager->get($this->getTriGraphModel())->getClassName();
        $qb            = $repo->createQueryBuilder($alias);

        $this->queryBuilder($qb, $alias);

        foreach($sqlParts as $i => $sqlPart)
        {
            $qb->join($triGraphClass, "tri{$i}", Join::WITH, "t = tri{$i}.entity AND tri{$i}.triplet = '{$sqlPart}'");
        }

        $query  = $qb->getQuery();
        $result = $query->setMaxResults(20)->getResult($hydration);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function index($entities = null)
    {
        $em      = $this->modelManager->getManager();
        $repo    = $this->modelManager->get($this->getModel());
        $triRepo = $this->modelManager->get($this->getTriGraphModel());

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
            /** @var TriGraph $truple */
            $deleteQb = $triRepo->createQueryBuilder('t');
            $deleteQb->delete()->where("t.entity = :entity");
            $deleteQb->setParameter(':entity', $entity);

            $deleteQb->getQuery()->execute();
            $triplets = array();

            foreach($this->entityMappings as $field => $properties)
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
                                $triplets = array_merge($triplets, $this->parseText($subEntity->{"get{$subField}"}()));
                            }
                        }
                        else
                        {
                            $triplets = array_merge($triplets, $this->parseText($subEntities->{"get{$subField}"}()));
                        }
                    }
                }
                else
                {
                    $triplets = array_merge($triplets, $this->parseText($entity->{"get{$field}"}()));
                }
            }

            $triplets = array_unique($triplets);

            foreach($triplets as $triplet)
            {
                $tripletEntity = $this->modelManager->create($this->getTriGraphModel());
                $tripletEntity->setTriplet($triplet);
                $tripletEntity->setEntity($entity);
                $em->persist($tripletEntity);
                $em->flush($tripletEntity);
            }


        }
    }

    /**
     * @inheritdoc
     */
    public function remove($entity)
    {
        $em      = $this->modelManager->getManager();
        $triRepo = $this->modelManager->get($this->getTriGraphModel());

        $em->beginTransaction();

        /** @var TriGraph $truple */
        $deleteQb = $triRepo->createQueryBuilder('t');
        $deleteQb->delete()->where("t.entity = :entity");
        $deleteQb->setParameter(':entity', $entity);

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
            $repo = $this->modelManager->get($this->getTriGraphModel());

            $qb             = $repo->createQueryBuilder('tri')->select('count(tri.triplet) as row_count');
            $this->rowCount = (int)$qb->getQuery()->getSingleScalarResult();
        }

        return $this->rowCount;
    }

    /**
     * Parse a text body into triplets
     *
     * @param $text
     *
     * @return array
     */
    protected function parseText($text)
    {
        $parts    = explode(' ', strtolower($text));
        $sqlParts = array();
        foreach($parts as $part)
        {
            if(strlen($part))
            {
                $clean = preg_replace('/[^\w\d]/', '', $part);
                if(strlen($clean))
                {
                    $splits = str_split($clean, 3);
                    foreach($splits as $split)
                    {
                        if(strlen($split) === 3)
                        {
                            $sqlParts[] = $split;
                        }
                    }
                }
            }
        }

        return $sqlParts;
    }
} 
