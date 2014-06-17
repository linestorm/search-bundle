<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LineStorm\SearchBundle\Model\FullText;
use LineStorm\SearchBundle\Search\AbstractSearchProvider;
use LineStorm\SearchBundle\Search\Exception\EntityNotSupportedException;
use LineStorm\SearchBundle\Search\Exception\FullTextIndexMissingException;
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

    abstract public function getIndexModel();

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

        $alias      = 't';
        $repo       = $this->modelManager->get($this->getModel());
        $indexRepo  = $this->modelManager->get($this->getIndexModel());
        $indexClass = $indexRepo->getClassName();
        $qb         = $repo->createQueryBuilder($alias);

        $this->queryBuilder($qb, $alias);

        $search = implode(' ', $sqlParts);
        $qb
           ->join($indexClass, "idx", Join::WITH, "t = idx.entity")
           ->andWhere("MATCH_AGAINST(idx.text, :query 'IN BOOLEAN MODE') > 0.0")
           ->addGroupBy('t.id')
           ->setParameter('query', $search);

        $query  = $qb->getQuery();
        $result = $query->setMaxResults(20)->getResult($hydration);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function index($entities = null)
    {
        $em        = $this->modelManager->getManager();
        $repo      = $this->modelManager->get($this->getModel());
        $indexRepo = $this->modelManager->get($this->getIndexModel());

        // first, we need to check this is actually fulltext table
        $rsm = new Query\ResultSetMappingBuilder($em);
        $rsm->addScalarResult('Index_type', 'type');
        $rsm->addScalarResult('Key_name', 'key');

        $meta = $em->getClassMetadata($indexRepo->getClassName());
        $indexName = 'search_text_index';
        $indexCheck = $em->createNativeQuery("SHOW INDEX FROM {$meta->getTableName()} WHERE KEY_NAME = '{$indexName}' AND Index_type='FULLTEXT'", $rsm);
        $q = $indexCheck->execute();

        if(!count($q))
        {
            throw new FullTextIndexMissingException($meta->getTableName(), $indexName, array('text'));
        }

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

            $deleteQb = $indexRepo->createQueryBuilder('t');
            $deleteQb->delete()->where("t.entity = :entity");
            $deleteQb->setParameter(':entity', $entity);

            $deleteQb->getQuery()->execute();

            /** @var FullText $indexEnitiy */
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
                                $indexEnitiy = $this->modelManager->create($this->getIndexModel());
                                $indexEnitiy->setEntity($entity);
                                $indexEnitiy->setText($subEntity->{"get{$subField}"}());
                                $em->persist($indexEnitiy);
                            }
                        }
                        else
                        {
                            $indexEnitiy = $this->modelManager->create($this->getIndexModel());
                            $indexEnitiy->setEntity($entity);
                            $indexEnitiy->setText($subEntities->{"get{$subField}"}());
                            $em->persist($indexEnitiy);
                        }
                    }
                }
                else
                {
                    $indexEnitiy = $this->modelManager->create($this->getIndexModel());
                    $indexEnitiy->setEntity($entity);
                    $indexEnitiy->setText($entity->{"get{$field}"}());
                    $em->persist($indexEnitiy);
                }
            }

            $em->flush();

        }
    }

    /**
     * @inheritdoc
     */
    public function remove($entity)
    {
        $em      = $this->modelManager->getManager();
        $triRepo = $this->modelManager->get($this->getIndexModel());

        /** @var FullText $truple */
        $deleteQb = $triRepo->createQueryBuilder('t');
        $deleteQb->delete()->where("t.entity = :entity");
        $deleteQb->setParameter(':entity', $entity);

        $deleteQb->getQuery()->execute();
    }

    /**
     * @inheritdoc
     */
    public function getCount($fromCache = true)
    {
        if($this->rowCount === null || !$fromCache)
        {
            $repo = $this->modelManager->get($this->getIndexModel());

            $qb             = $repo->createQueryBuilder('i')->select('count(i.id) as row_count');
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
                    $sqlParts[] = "+{$clean}";
                }
            }
        }

        return $sqlParts;
    }

} 
