WallPosterBundle
================

The **WallPosterBundle** bundle allows you to post you site news in you social groups, pages or timelines.

Installation
------------

Add this bundle to your `composer.json` file:

    {
        "require": {
            "dario_swain/wall-poster-bundle": "dev-master"
        }
    }


You should browse
[`dario_swain/wall-poster-bundle`](https://packagist.org/packages/dario_swain/wall-poster-bundle)
page to choose a stable version to use, avoid the `@stable` meta constraint.

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new new WallPosterBundle\WallPosterBundle(),
        );
    }

Import the routing definition in `routing.yml`:

    # app/config/routing.yml
    WallPosterBundle:
        resource: "@WallPosterBundle/Resources/routing/routing.yml"

This route (/wall-poster/captcha) used for enter captcha value if
[`vk.com`](http://vk.com/)
block your api requests

Enable the bundle's configuration in `app/config/config.yml`:

    # app/config/config.yml
    wall_poster:
        vk:
            access_token: VK_STANDALONE_APPLICATION_ACCESS_TOKEN
            group_id: VK_GROUP_ID
        facebook:
            access_token: FACEBOOK_ACCESS_TOKEN
            app_id: FACEBOOK_APPLICATION_ID
            app_secret: FACEBOOK_APPLICATION_SECRET
            page: FACEBOOK_PAGE_ID
        twitter:
            api_key: TWITTER_APP_KEY
            api_secret: TWITTER_APP_SECRET
            access_token: TWITTER_ACCESS_TOKEN
            access_secret:  TWITTER_ACCESS_TOKEN_SECRET

Usage
-----

You can publish your posts in social networks, for use this you can use special wall-poster services.

### Post

Create `WallPosterBundle\Post\Post` for publisher

``` php
<?php

namespace Your\Namespace;

use WallPosterBundle\Post\Post;

class YourController extends Controller
{
    public function updateAction()
    {
        /** Create you Post instance **/
        $post = new Post();
        /** Add image to post, you can provide absolute path for your local file and browser url to file **/
        $post->createImage('/var/www/images/test.jpg','http://your_site.com/images/test.jpg')
        /** Add link to post **/
            ->createLink('http://your_site.com/about')
        /** Add social tags **/
            ->addTag('about')
            ->addTag('your_site')
            ->addTag('follow_me')
        /** Add message to your post **/
            ->setMessage('Hello world!');
    }
}
```

After creation Post instance you can publish it with social network providers.

### Social network providers

The bundle provide an `wall_poster.vk`, `wall_poster.facebook` and `wall_poster.twitter` services, after Post creation:


``` php
<?php

namespace Your\Namespace;

use WallPosterBundle\Post\Post;

class YourController extends Controller
{
    public function updateAction()
    {
        /** Create you Post instance **/
        $post = new Post();

        /** ... **/

        $provider = $this->get('wall_poster.vk');

        try
        {
            $post = $provider->publish($post);
        }
        catch(Exception $ex)
        {
            //Handle errors
        }

    }
}
```