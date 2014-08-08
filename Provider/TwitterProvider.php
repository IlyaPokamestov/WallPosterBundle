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

class TwitterProvider extends Provider
{
	const MAX_TWEET_LENGTH = 140;

	/** @var \Twitter */
	protected $twitterOauth;
	protected $configuration = null;

	public function __construct($apiKey ,$apiSecret, $accessToken, $accessSecret)
	{
		$this->twitterOauth = new \Twitter($apiKey ,$apiSecret, $accessToken, $accessSecret);
		if (!$this->twitterOauth->authenticate()) {
			//TODO:throw Exception???
		}
	}

	public function publish(Post $post)
	{
		if($this->twitterOauth)
		{
			$post = $this->preparePost($post);
			$media = null;
			if($post->getImages())
			{
				$images = $post->getImages();
				/** @var PostImage $media */
				$media = array_shift($images);
				$media = $media->getFilePath();
			}
			return $this->twitterOauth->send($post->getMessage().' '.$post->getLink()->getUrl(), $media);
		}
		return false;
	}

	protected function getConfiguration()
	{
		if($this->configuration)
		{
			return $this->configuration;
		}
		$this->configuration = $this->twitterOauth->request('help/configuration','GET');

		if(isset($this->configuration->errors))
		{
			//TODO: throw exceptions
		}

		return $this->configuration;
	}

	//TODO: Add tags
	protected function preparePost(Post $post)
	{
		$messageSize = strlen($post->getMessage());
		$linkSize = $post->getLink()->getUrl() ? $this->getShortUrlLength($post->getLink()->isHttps()) + 1 : 0;

		if(($messageSize + $linkSize) >= self::MAX_TWEET_LENGTH)
		{
			$messageOffset = (($messageSize + $linkSize) - self::MAX_TWEET_LENGTH);
			if($post->getImages())
			{
				$messageOffset += $this->getCharactersPerMedia();
			}
			$post->setMessage(trim(mb_substr($post->getMessage(),0,strlen($post->getMessage() - ($messageOffset + 3)))).'...');
		}

		return $this->preparePostMedia($post);
	}

	protected function preparePostMedia(Post $post)
	{
		if($post->getImages())
		{
			$messageSize = strlen($post->getMessage()) + ($post->getLink()->getUrl() ? $this->getShortUrlLength($post->getLink()->isHttps()) : 0);

			$images = array_slice($post->getImages(),0,$this->getMaxMediaToUpload());
			if(count($images) > 1)
			{
				$imagesToPublish = array();
				foreach($images as $image)
				{
					if(($messageSize + ($imagesToPublish + 1) * $this->getCharactersPerMedia()) <= self::MAX_TWEET_LENGTH)
					{
						$imagesToPublish[] = $image;
					}
					else
					{
						break;
					}
				}
			}
			else
			{
				$imagesToPublish = array(array_shift($images));
			}

			$post->setImages($imagesToPublish);
		}
		return $post;
	}

	protected function getShortUrlLength($https = false)
	{
		if($https)
		{
			return $this->getConfiguration()->short_url_length_https;
		}
		return $this->getConfiguration()->short_url_length;
	}

	protected function getMaxMediaToUpload()
	{
		return $this->getConfiguration()->max_media_per_upload;
	}

	protected function getCharactersPerMedia()
	{
		return $this->getConfiguration()->characters_reserved_per_media;
	}
}