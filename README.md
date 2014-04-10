Post Module for LineStorm Blog Bundle
========================================

Post Module for the LineStorm BlogBundle. It comes bundled with the Tag, Article and Gallery components. It also requires
the `linestorm/media-bundle`.

Installation
============
This module will provide functionality to post blog type content to the LineStorm CMS.

1. Download bundle using composer
2. Enable the Bundle
3. Configure the Bundle
4. Installing Assets

Step 1: Download bundle using composer
--------------------------------------

Add `linestorm/post-bundle` to your `composer.json` file, or download it by running the command:

```bash
$ php composer.phar require linestorm/post-bundle
```

Composer will install the bundle to your project's vendor/sp directory.

Step 2: Enable the bundle
-------------------------

Enable the post and media bundles in the `app/AppKernel.php`:

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new LineStorm\BlogPostBundle\BlogPostBundle(),
        new LineStorm\MediaBundle\MediaBundle(),
    );
}
```

Step 3: Configure the Bundle
----------------------------

Add the class entity definitions in the line_storm_cms namespace and the media namespace
inside the `app/config/config.yml` file:

```yml
line_storm_cms:
  ...
  entity_classes:
    ...
    post:                 Acme\DemoBundle\Entity\BlogPost
    post_article:         Acme\DemoBundle\Entity\BlogPostArticle
    post_gallery:         Acme\DemoBundle\Entity\BlogPostGallery
    post_gallery_image:   Acme\DemoBundle\Entity\BlogPostGalleryImage
    tag:                  Acme\DemoBundle\Entity\BlogTag
    category:             Acme\DemoBundle\Entity\BlogCategory
    user:                 Acme\DemoBundle\Entity\User
    user_group:           Acme\DemoBundle\Entity\Group

line_storm_media:
  default_provider: local_storeage
```


Step 4: Installing Assets
-------------------------

If you use bower, add the dependencies within bower.json. If you do not, you will need to add them manually into
web/vendor.

Documentation
=============

See [index.md](src/LineStorm/BlogPostBundle/Resources/doc/index.md)
