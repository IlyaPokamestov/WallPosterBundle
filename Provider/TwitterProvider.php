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
use WallPosterBundle\Post\PostImage;
use WallPosterBundle\WallPosterBundle;

class TwitterProvider extends Provider
{
	/** @var \Twitter */
	protected $twitterOauth;
	/** @var \stdClass */
	protected $configuration = null;

	/**
	 * @param string $apiKey
	 * @param string $apiSecret
	 * @param string $accessToken
	 * @param string $accessSecret
	 */
	public function __construct($apiKey ,$apiSecret, $accessToken, $accessSecret)
	{
		$this->twitterOauth = new \Twitter($apiKey ,$apiSecret, $accessToken, $accessSecret);
	}

	/**
	 * @param Post $post
	 * @return Post
	 * @throws \TwitterException
	 */
	public function publish(Post $post)
	{
		if (!$this->twitterOauth->authenticate()) {
			throw new \TwitterException('Authentication failed to twitter application.');
		}

		$status = $post->getTwitterMessage(
			$this->getShortUrlLength(),
			$this->getShortUrlLength(true),
			$this->getMaxMediaToUpload(),
			$this->getCharactersPerMedia()
		);
		$media = null;
		if($post->getImages())
		{
			$images = $post->getImages();
			/** @var PostImage $media */
			$media = array_shift($images);
			$media = $media->getFilePath();
		}

		$postResponse = $this->twitterOauth->send($status, $media);

		if(isset($postResponse->errors))
		{
			throw new \TwitterException($postResponse->errors->message, $postResponse->errors->code);
		}

		$post->addPublishInformation(WallPosterBundle::TWITTER_PROVIDER, $postResponse);

		return $post;
	}

	/**
	 * @return \stdClass
	 * @throws \TwitterException
	 */
	protected function getConfiguration()
	{
		if($this->configuration)
		{
			return $this->configuration;
		}
		$this->configuration = $this->twitterOauth->request('help/configuration','GET');

		if(isset($this->configuration->errors))
		{
			throw new \TwitterException($this->configuration->errors->message, $this->configuration->errors->code);
		}

		return $this->configuration;
	}

	/**
	 * @param bool $https
	 * @return int
	 */
	protected function getShortUrlLength($https = false)
	{
		if($https)
		{
			return $this->getConfiguration()->short_url_length_https;
		}
		return $this->getConfiguration()->short_url_length;
	}

	/** @return int */
	protected function getMaxMediaToUpload()
	{
		return $this->getConfiguration()->max_media_per_upload;
	}

	/** @return int */
	protected function getCharactersPerMedia()
	{
		return $this->getConfiguration()->characters_reserved_per_media;
	}
}