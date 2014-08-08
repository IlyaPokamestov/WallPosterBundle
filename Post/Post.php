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

namespace WallPosterBundle\Post;

class Post
{
	const MAX_TWEET_LENGTH = 140;
	const SPACES_LENGTH = 2;

	/** @var  string */
	protected $message;
	/** @var  PostLink */
	protected $link;
	/** @var array */
	protected $images = array();
	/** @var array */
	protected $tags = array();
	/** @var  PostLocation */
	protected $location;

	/** @var array */
	protected $publishInformation = array();

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = trim($message);
		return $this;
	}

	/** @return string */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	public function createLink($url)
	{
		$this->link = new PostLink($url);
		return $this;
	}

	/**
	 * @param PostLink $link
	 * @return $this
	 */
	public function setLink(PostLink $link)
	{
		$this->link = $link;
		return $this;
	}

	/** @return PostLink */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param string $pathToFile
	 * @param string $browserUrl
	 * @return $this
	 */
	public function createImage($pathToFile, $browserUrl)
	{
		$this->images[] = new PostImage($pathToFile,$browserUrl);
		return $this;
	}

	/**
	 * @param PostImage $image
	 * @return $this
	 */
	public function addImage(PostImage $image)
	{
		$this->images[] = $image;
		return $this;
	}

	/** @return array */
	public function getImages()
	{
		return $this->images;
	}

	/**
	 * @param array $images
	 * @return $this
	 */
	public function setImages($images)
	{
		$this->images = $images;
		return $this;
	}

	/**
	 * @param string $tag
	 * @return $this
	 */
	public function addTag($tag)
	{
		$this->tags[] = $tag;
		return $this;
	}

	/** @return array */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * @param $latitude
	 * @param $longitude
	 * @return $this
	 */
	public function createLocation($latitude, $longitude)
	{
		$this->location = new PostLocation($latitude, $longitude);
		return $this;
	}

	/**
	 * @param PostLocation $location
	 * @return $this
	 */
	public function setLocation(PostLocation $location)
	{
		$this->location = $location;
		return $this;
	}

	/** @return PostLocation */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * @param string $provider
	 * @param /stdClass $information
	 * @param bool $error
	 * @return $this
	 */
	public function addPublishInformation($provider, $information, $error = false)
	{
		$this->publishInformation[$provider] = array('information' => $information, 'error' => $error);
		return $this;
	}

	/**
	 * @param bool $startWithNewLine
	 * @param bool $htmlEntityDecode
	 * @return string
	 */
	public function getSocialTagsText($startWithNewLine = false, $htmlEntityDecode = false)
	{
		if($this->tags)
		{
			if($startWithNewLine)
			{
				$text = "\n\n";
			}
			else
			{
				$text = '';
			}

			foreach ($this->tags as $tag)
			{
				$text .= ' #' . str_replace(' ', '_', $tag);
			}

			$text = trim($text);
			if($htmlEntityDecode)
			{
				$text = html_entity_decode($text);
			}

			return $text;
		}
		return false;
	}

	/**
	 * @param int $httpUrlLength
	 * @param int $httpsUrlLength
	 * @param int $maxMedia
	 * @param int $charactersPerMedia
	 * @return string
	 */
	public function getTwitterMessage($httpUrlLength = 22, $httpsUrlLength = 23, $maxMedia = 1, $charactersPerMedia = 23)
	{
		$charactersPerLink = $httpUrlLength;
		if($this->getLink() && $this->getLink()->isHttps())
		{
			$charactersPerLink = $httpsUrlLength;
		}

		$messageSize = strlen($this->getMessage());

		$linkSize = 0;
		if($this->getLink() && $this->getLink()->getUrl())
		{
			$linkSize = $charactersPerLink + self::SPACES_LENGTH;
		}

		$tagsText = $this->getSocialTagsText();
		$tagsSize = 0;
		if($tagsText)
		{
			$tagsSize = strlen($tagsText) + self::SPACES_LENGTH;
		}

		$message = $this->getMessage();
		if(($messageSize + $linkSize + $tagsSize) >= self::MAX_TWEET_LENGTH)
		{
			$messageOffset = (($messageSize + $linkSize + $tagsSize) - self::MAX_TWEET_LENGTH);
			if($this->getImages())
			{
				$messageOffset += $charactersPerMedia + 1;
			}
			$message = trim(mb_substr($message,0,strlen($message - ($messageOffset + 3)))).'...';
		}

		/** Prepare images for twitter */
		if($this->getImages())
		{
			$messageSize = strlen($message) + $linkSize;
			$images = array_slice($this->getImages(), 0, $maxMedia);

			if(count($images) > 1)
			{
				$imagesToPublish = array();
				foreach($images as $image)
				{
					if(($messageSize + ($imagesToPublish + 1) * $charactersPerMedia) <= self::MAX_TWEET_LENGTH)
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

			//TODO: Rewrite images in post - not good
			$this->setImages($imagesToPublish);
		}

		if($this->getLink() && $this->getLink()->getUrl())
		{
			$message .= ' '.$this->getLink()->getUrl();
		}
		if($tagsText)
		{
			$message .= ' '.$tagsText;
		}

		return $message;
	}
}