Tri Graph Indexing
==================

TriGraphs are simplist, but one the slowest of all the search providers. The tri graphs' only real advantage is that it
has no dependencies on system type or configuration. Simply, They will work everywhere.

How it works
------------
To get a tri graph, the content is broken down into words, then split into strings of length 3. If the the word, or
remainder of a word is less than 3, it will be discarded.

When a query is made, the search term is similarly split up into strings of length 3 into an array of length N. The base
entity is then joined to the index table N times, filtering down each time on a the string of 3 length.

Using Tri Graphs
----------------

Lets say we are creating a search provider for the post entity (see `LineStorm\CmsBundle\Search\PostTriGraphSearchProvider`
for the actual implementation). Our model name is called `post` in the LineStormCMS Module Manager.

###The Model
First, we need a model for the Tri Graph data to be stored in. Create a new entity that extends
`LineStorm\SearchBundle\Model\TriGraph`:

```php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LineStorm\SearchBundle\Model\TriGraph;

/**
 * @ORM\Table(
 *      name="search_trigraph_post",
 *      indexes={@ORM\Index(name="triplet_idx", columns={"triplet"})},
 *      uniqueConstraints={@ORM\UniqueConstraint(name="search_idx", columns={"triplet", "entity_id"})}
 * )
 * @ORM\Entity
 */
class SearchTriGraphPost extends TriGraph
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    protected $id;

    /**
     * @var BlogPost
     *
     * @ORM\ManyToOne(targetEntity="Post")
     */
    protected $entity;

}
```

Then, add it the entity into out CMS Model Manager:
```yml

line_storm_cms:
  entity_classes:
    ...

    # Search Provider
    search_trigraph_entity: Acme\DemoBundle\Entity\SearchTriGraphPost
```

###The Provider
To use the Tri Graph provider, you will need to extend the `LineStorm\SearchBundle\Search\Provider\TriGraphSearchProvider`
class and implement some methods:

* `getModel` should return the model name (i.e. post) as string
* `getTriGraphModel` should return the trigraph table entity name (search_trigraph_entity above) as string
* `queryBuilder` accepts the query build and alias used for queries. You can modify it to add extra data into responses.
  For example, here we also want to include all the tags and categories of the post
* `getRoute` accepts and entity (AS ARRAY OR OBJECT!) and turns it into a URL

```php
<?php

namespace LineStorm\PostBundle\Search;

use Andy\PortfolioBundle\Entity\BlogPost;
use Doctrine\ORM\QueryBuilder;
use LineStorm\SearchBundle\Search\Provider\TriGraphSearchProvider;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

class PostTriGraphSearchProvider extends TriGraphSearchProvider implements SearchProviderInterface
{

    public function getModel()
    {
        return 'post';
    }

    public function getTriGraphModel()
    {
        return 'search_trigraph_post';
    }

    public function queryBuilder(QueryBuilder $qb, $alias)
    {
        $qb->addSelect('c')
            ->join($alias.'.category', 'c');

        $qb->addSelect('ta')
            ->join($alias.'.tags', 'ta');
    }

    public function getRoute($entity)
    {
        if($entity instanceof BlogPost)
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
