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
use TwitterOAuth\Api;
use WallPosterBundle\Post\PostImage;

class TwitterProvider extends Provider
{
	const MAX_TWEET_LENGTH = 140;
	const LINK_TEXT_LENGTH = 20;

	/** @var Api */
	protected $twitterOauth;
	protected $currentMessageLength = 140;
	protected $configuration = null;

	public function __construct($apiKey ,$apiSecret, $accessToken, $accessSecret)
	{
		$this->twitterOauth = new Api($apiKey ,$apiSecret, $accessToken, $accessSecret);
		$this->twitterOauth->host = 'https://api.twitter.com/1.1/';
		//TODO:Check errors
		$this->twitterOauth->get('account/verify_credentials');
	}

	public function publish(Post $post)
	{
		if($this->twitterOauth)
		{
			$post = $this->preparePost($post);
			if($post->getImages())
			{
				$media = $post->getImages();
				/** @var PostImage $media */
				$media = array_shift($media);
				return $this->twitterOauth->post('statuses/update_with_media', array(
						'status' => $post->getMessage().' '.$post->getLink(),
						'media[]' => '@'.$media->getFilePath())
				);
			}
			else
			{
				return $this->twitterOauth->post('statuses/update', array('status' => $post->getMessage().' '.$post->getLink()));
			}
		}
		return false;
	}

	protected function getConfiguration()
	{
		if($this->configuration)
		{
			return $this->configuration;
		}
		$this->configuration = $this->twitterOauth->get('help/configuration');

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
		//TODO: HTTPS check
		$linkSize = $post->getLink() ? $this->getShortUrlLength() + 1 : 0;

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
			//TODO: HTTPS check
			$messageSize = strlen($post->getMessage()) + ($post->getLink() ? $this->getShortUrlLength() : 0);

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

	protected function prepareMessage(Post $post)
	{

	}

	protected function prepareAttachments(Post $post)
	{
		$attachments = array();
		//TODO: Prepare images and links
		return $attachments;
	}
}