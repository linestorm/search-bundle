<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query;
use LineStorm\SearchBundle\Model\TriGraph;
use LineStorm\SearchBundle\Search\AbstractSearchProvider;

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
    public function getType()
    {
        return 'tri_graph';
    }

    /**
     * @inheritdoc
     */
    public function search($query, $hydration = Query::HYDRATE_OBJECT)
    {
        $sqlParts = $this->parseText($query);

        $repo          = $this->modelManager->get($this->getName());
        $triGraphClass = $this->modelManager->get($this->getTriGraph())->getClassName();
        $qb            = $repo->createQueryBuilder('t');

        foreach ($sqlParts as $i => $sqlPart)
        {
            $qb->join($triGraphClass, "tri{$i}", Join::WITH, "t = tri{$i}.post AND tri{$i}.tuple = '{$sqlPart}'");
        }

        $query  = $qb->getQuery();
        $result = $query->setMaxResults(20)->getResult($hydration);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getTriGraph()
    {
        return 'trigraph_' . $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $em      = $this->modelManager->getManager();
        $repo    = $this->modelManager->get($this->getName());
        $triRepo = $this->modelManager->get($this->getTriGraph());


        $entities = $repo->findAll();
        foreach ($entities as $entity)
        {
            $em->beginTransaction();

            /** @var TriGraph $truple */
            $deleteQb = $triRepo->createQueryBuilder('t');
            $deleteQb->delete()->where('t.post = :post');
            $deleteQb->setParameter(':post', $entity);

            $deleteQb->getQuery()->execute();

            foreach ($this->getIndexFields() as $field => $properties)
            {
                if (is_array($properties))
                {
                    foreach ($properties as $subField)
                    {
                        $subEntities = $entity->{"get{$field}"}();
                        if ($subEntities instanceof Collection)
                        {
                            foreach ($subEntities as $subEntity)
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
    public function getIndexFields()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getCount($fromCache = true)
    {
        if ($this->rowCount === null || !$fromCache)
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
        foreach ($parts as $part)
        {
            if (strlen($part))
            {
                $clean = preg_replace('/[^\w\d]/', '', $part);
                if (strlen($clean))
                {
                    $splits = str_split($clean, 3);
                    foreach ($splits as $split)
                    {
                        if (strlen($split) === 3)
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
        foreach ($tuples as $tuple)
        {
            $tupleEntity = $this->modelManager->create($this->getTriGraph());
            $tupleEntity->setTuple($tuple);
            $tupleEntity->{"set{$this->getName()}"}($baseEntity);
            $em->merge($tupleEntity);
        }
    }
} 
