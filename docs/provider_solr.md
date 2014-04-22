Solr Indexing
==================

Solr requires the Java based [Apache Solr](https://lucene.apache.org/solr/) application and the
[andythorne/solr-bundle](https://github.com/andythorne/SolrBundle)(a fork of
[Florian Semm's SolrBundle](https://github.com/floriansemm/SolrBundle)).

Using Solr
---------------

Lets say we are creating a search provider for the post entity (see `LineStorm\CmsBundle\Search\PostSolrSearchProvider`
for the actual implementation). Our model name is called `post` in the LineStormCMS Module Manager.

###The Model
First, we need to adapt our Post model to be indexed by the SolrBundle. We do this by defining @Solr annotations on the
Post entity:

```php
<?php

namespace Acme\DemoBundle\Entity;

/**
 * @Solr\Document
 * @Solr\MetaFields(fields={{"name"="text"}})
 *
 * @ORM\Entity
 */
class Post extends Post
{
    /**
     * @Solr\Id

     * ...
     */
    protected $id;

    /**
     * @Solr\Field
     *
     * ...
     */
    protected $title;

    /**
     * @Solr\EntityField(properties={"name"})
     *
     * ...
     */
    protected $category;
```

Continue adding solr fields to all the fields you want indexed. In Solr, all these fields should be copied to the `text`
field in schema.xml.

###The Provider
To use the Solr provider, you will need to extend the `LineStorm\SearchBundle\Search\Provider\SolrSearchProvider`
class and implement some methods:

* `getModel` should return the model name (i.e. post) as string
* `getTriGraphModel` should return the fulltext table entity name (search_fulltext_entity above) as string
* `queryBuilder` accepts the query build and alias used for queries. You can modify it to add extra data into responses.
  For example, here we also want to include all the tags and categories of the post. Remember to add an addGroupBy call
  for 1-n and n-n mappings if you need the data selected.
* `getRoute` accepts and entity (AS ARRAY OR OBJECT!) and turns it into a URL

```php
<?php

namespace Acme\DemoBundle\Search;

use Acme\DemoBundle\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use LineStorm\SearchBundle\Search\Provider\SolrSearchProvider;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

/**
 * Class PostTriGraphSearchProvider
 *
 * @package LineStorm\PostBundle\Search
 */
class PostSolrSearchProvider extends SolrSearchProvider implements SearchProviderInterface
{

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function queryBuilder(QueryBuilder $qb, $alias)
    {
        $qb->addSelect('c')
            ->join($alias.'.category', 'c')
            ->addGroupBy('c.id');

        $qb->addSelect('ta')
            ->join($alias.'.tags', 'ta')
            ->addGroupBy('ta.id');
    }

    /**
     * @inheritdoc
     */
    public function getRoute($entity)
    {
        if($entity instanceof Post)
        {
            return array(
                'linestorm_cms_post',
                array(
                    'category' => $entity->getCategory()->getName(),
                    'id'       => $entity->getId(),
                    'slug'     => $entity->getSlug(),
                )
            );
        }
        elseif(is_array($entity))
        {
            return array(
                'linestorm_cms_post',
                array(
                    'category' => $entity['category']['name'],
                    'id'       => $entity['id'],
                    'slug'     => $entity['slug'],
                )
            );
        }
    }

}
```

###Run the Index
Finally run `php app/config -e=dev linestorm:search:index` to run an index of the database
