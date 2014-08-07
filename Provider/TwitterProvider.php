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

class TwitterProvider extends Provider
{
	protected $twitterOauth;

	public function __construct($apiKey ,$apiSecret, $accessToken, $accessSecret)
	{
		$this->twitterOauth = new \TwitterOAuth($apiKey ,$apiSecret, $accessToken, $accessSecret);
		//TODO:Check errors
		$this->twitterOauth->get('account/verify_credentials');
	}

	public function publish(Post $post)
	{
		if($this->twitterOauth)
		{
			$this->twitterOauth->post('statuses/update', array('status' => $post->getMessage()));
		}
		return false;
	}

	protected function prepareAttachments(Post $post)
	{
		$attachments = array();
		//TODO: Prepare images and links
		return $attachments;
	}

} 