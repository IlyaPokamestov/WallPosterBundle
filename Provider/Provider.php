<?php
/**
 * Created by PhpStorm.
 * User: dario_swain
 * Date: 8/4/14
 * Time: 4:14 PM
 */

namespace Justy\Bundle\WallPosterBundle\Provider;


use Justy\Bundle\WallPosterBundle\Post\Post;

abstract class Provider
{
    const TYPE_VK = 'vk';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_TWITTER = 'twitter';

    protected $type;

    protected function getType()
    {
        return $this->type;
    }

    protected function request($url, array $post = array())
    {
        $curlInstance = curl_init();

        curl_setopt($curlInstance, CURLOPT_URL, $url);
        curl_setopt($curlInstance, CURLOPT_RETURNTRANSFER, TRUE);

        if($post && is_array($post))
        {
            curl_setopt($curlInstance, CURLOPT_POST,1);
            curl_setopt($curlInstance, CURLOPT_POSTFIELDS, $post);
        }

        $response = curl_exec($curlInstance);
        curl_close($curlInstance);

        if (!$response)
        {
            throw new \Exception(curl_error($curlInstance), curl_errno($curlInstance));
        }

        return $response;
    }

    abstract public function publish(Post $post);
} 