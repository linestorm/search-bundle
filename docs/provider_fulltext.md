FullText Indexing
==================

FullText utilises MySQL's inbuilt FULLTEXT index and is relativly quick. Note that if you are using InnoDB on MySQL <
5.6 then a MyISAM index table needs to be created instead as FULLTEXT is not supported.

Using Full Text
---------------

Lets say we are creating a search provider for the post entity (see `LineStorm\CmsBundle\Search\PostFullTExtSearchProvider`
for the actual implementation). Our model name is called `post` in the LineStormCMS Module Manager.

###The Model
First, we need a model for the FullText data to be stored in. Create a new entity that extends
`LineStorm\SearchBundle\Model\FullText`:

```php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LineStorm\SearchBundle\Model\FullText;

/**
 * @ORM\Table(name="search_full_text_blog_post", indexes={@ORM\Index(name="text_idx", columns={"text"})} )
 * @ORM\Entity
 */
class SearchFullTextPost extends FullText
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

NOTE: As previosly mentioned, if you are using InnoDB on MySQL < 5.6, you will need to add "options={"engine"="MyISAM"}"
to your @ORM\Table definition

Then, add it the entity into out CMS Model Manager:
```yml

line_storm_cms:
  entity_classes:
    ...

    # Search Provider
    search_fulltext_entity: Acme\DemoBundle\Entity\SearcFullTextPost
```

###The Provider
To use the Tri Graph provider, you will need to extend the `LineStorm\SearchBundle\Search\Provider\TriGraphSearchProvider`
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
use LineStorm\SearchBundle\Search\Provider\FullTextSearchProvider;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

class PostFullTextSearchProvider extends FullTextSearchProvider implements SearchProviderInterface
{

    public function getModel()
    {
        return 'post';
    }

    public function getIndexModel()
    {
        return 'search_fulltext_post';
    }

    public function queryBuilder(QueryBuilder $qb, $alias)
    {
        $qb->addSelect('c')
            ->join($alias.'.category', 'c')
            ->addGroupBy('c.id');

        $qb->addSelect('ta')
            ->join($alias.'.tags', 'ta')
            ->addGroupBy('ta.id');
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
