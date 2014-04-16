<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query;
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

    protected $inverseField;

    /**
     * @inheritdoc
     */
    public function getType()
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
    abstract public function getTriGraph();

    /**
     * @inheritdoc
     */
    public function queryBuilder(QueryBuilder $qb, $alias)
    {
    }

    /**
     * Get meta info for the trigraph
     */
    protected function getInverseSide()
    {
        if(!$this->inverseField)
        {
            $em            = $this->modelManager->getManager();
            $triGraphClass = $this->modelManager->get($this->getTriGraph())->getClassName();
            $meta          = $em->getClassMetadata($triGraphClass);

            $mappings = $meta->getAssociationMappings();
            foreach($mappings as $name => $mapping)
            {
                $this->inverseField = $name;
                break;
            }
        }

        return $this->inverseField;
    }


    /**
     * @inheritdoc
     */
    public function search($query, $hydration = Query::HYDRATE_OBJECT)
    {
        $sqlParts = $this->parseText($query);

        $alias         = 't';
        $repo          = $this->modelManager->get($this->getName());
        $triGraphClass = $this->modelManager->get($this->getTriGraph())->getClassName();
        $qb            = $repo->createQueryBuilder($alias);
        $inverse       = $this->getInverseSide();

        $this->queryBuilder($qb, $alias);

        foreach($sqlParts as $i => $sqlPart)
        {
            $qb->join($triGraphClass, "tri{$i}", Join::WITH, "t = tri{$i}.{$inverse} AND tri{$i}.tuple = '{$sqlPart}'");
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
        $repo    = $this->modelManager->get($this->getName());
        $triRepo = $this->modelManager->get($this->getTriGraph());
        $inverse = $this->getInverseSide();

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
            $deleteQb->delete()->where("t.{$inverse} = :inv");
            $deleteQb->setParameter(':inv', $entity);

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

    /**
     * Parse a text body into tuples
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

    /**
     * Create a tuple entity for a base entity
     *
     * @param EntityManager $em
     * @param object        $baseEntity
     * @param string        $text
     */
    private function createTupleEntities(EntityManager $em, $baseEntity, $text)
    {
        $tuples = $this->parseText($text);
        foreach($tuples as $tuple)
        {
            $tupleEntity = $this->modelManager->create($this->getTriGraph());
            $tupleEntity->setTuple($tuple);
            $tupleEntity->{"set{$this->getName()}"}($baseEntity);
            $em->merge($tupleEntity);
        }
    }
} 
