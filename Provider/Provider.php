<?php

/**
 * This file is part of the Wall Poster bundle.
 *
 * (c) Ilya Pokamestov
 *
 * @author Ilya Pokamestov
 * @email dario_swain@yahoo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WallPosterBundle\Provider;

use WallPosterBundle\Post\Post;

abstract class Provider
{
	/**
	 * TODO: Replace to VkProvider?
	 *
	 * @param string $url
	 * @param array $post
	 * @return string
	 * @throws \Exception
	 */
	protected function request($url, array $post = array())
    {
        $curlInstance = curl_init($url);

        curl_setopt($curlInstance, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curlInstance, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlInstance, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curlInstance, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

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

	/**
	 * Public post to social network
	 *
	 * @param Post $post
	 * @return Post
	 */
	abstract public function publish(Post $post);
} 