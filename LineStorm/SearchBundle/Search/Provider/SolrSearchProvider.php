<?php

namespace LineStorm\SearchBundle\Search\Provider;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use FS\SolrBundle\Solr;
use LineStorm\CmsBundle\Model\ModelManager;
use LineStorm\SearchBundle\Model\FullText;
use LineStorm\SearchBundle\Search\AbstractSearchProvider;
use LineStorm\SearchBundle\Search\Exception\EntityNotSupportedException;
use LineStorm\SearchBundle\Search\Exception\FullTextIndexMissingException;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

/**
 * Class SolrSearchProvider
 *
 * @package LineStorm\SearchBundle\Search\Provider
 */
abstract class SolrSearchProvider extends AbstractSearchProvider implements SearchProviderInterface
{

    /**
     * @var Solr
     */
    private $solr;

    /**
     * @param Solr $solr
     */
    public function setSolr(Solr $solr)
    {
        $this->solr = $solr;
    }


    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'solr';
    }

    /**
     * @inheritdoc
     */
    abstract public function getRoute($entity);

    /**
     * @inheritdoc
     */
    public function queryBuilder(QueryBuilder $qb, $alias)
    {
    }


    /**
     * @inheritdoc
     */
    public function search($query, $hydration=Query::HYDRATE_OBJECT)
    {
        $repo  = $this->modelManager->get($this->getModel());

        $solrRepo = $this->solr->getRepository($repo->getClassName());

        $qb = $repo->createQueryBuilder('p');
        $qb->select('partial p.{id,title,blurb,slug}');
        $qb->join('p.category', 'c')->addSelect('c');
        $qb->join('p.tags', 'ta')->addSelect('ta');

        $result = $solrRepo->createFindBy(array('text' => $query), null, 20, $qb, Query::HYDRATE_ARRAY);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function index($entities = null)
    {
        $repo  = $this->modelManager->get($this->getModel());
        $solrRepo = $this->solr->getRepository($repo->getClassName());

        $entities = $repo->findAll();
        foreach($entities as $entity)
        {
            $solrRepo->update($entity);
        }
    }

    /**
     * @inheritdoc
     */
    public function remove($entity)
    {
        $repo  = $this->modelManager->get($this->getModel());
        $solrRepo = $this->solr->getRepository($repo->getClassName());

        $solrRepo->delete($entity);
    }

    /**
     * @inheritdoc
     */
    public function getCount($fromCache = true)
    {
        return 'unknown';
    }

} 
