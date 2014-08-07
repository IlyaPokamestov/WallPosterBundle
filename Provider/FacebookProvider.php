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

namespace Justy\Bundle\WallPosterBundle\Provider;

use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphObject;
use Justy\Bundle\WallPosterBundle\Post\Post;

class FacebookProvider extends Provider
{
	protected $facebookSession;
	protected $page;

	public function __construct($page ,$appId, $appSecret, $accessToken = '')
	{
		$this->page = $page;

		FacebookSession::setDefaultApplication($appId,$appSecret);

		if($accessToken)
		{
			$this->facebookSession = new FacebookSession($accessToken);
		}
		else
		{
			$this->facebookSession= FacebookSession::newAppSession();
		}
	}

	public function publish(Post $post)
	{
		if($this->facebookSession)
		{
			//TODO: Handle errors
			$facebookRequest = new FacebookRequest($this->facebookSession,'POST','/'.$this->page.'/feed',$this->prepareAttachments($post));
			/** @var GraphObject $graphObject */
			try
			{
				$graphObject = $facebookRequest->execute()->getGraphObject();
				return $graphObject->getProperty('id');
			}
			catch(\Exception $ex)
			{
				//TODO:Notify about exception
				return false;
			}
		}
		return false;
	}

	//TODO: If image available, and link not found then post only image with title message
	protected function prepareAttachments(Post $post)
	{
		$attachments = array();
		$attachments['message'] = $post->getMessage();
		if($post->getLink())
		{
			$attachments['link'] = $post->getLink();
		}
		//TODO: Image web path required
		if($post->getImages())
		{
			$images = $post->getImages();
			$attachments['picture'] = array_shift($images);
		}
		return $attachments;
	}

} 