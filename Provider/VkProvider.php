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

use WallPosterBundle\Exception\VkException;
use WallPosterBundle\Handler\CaptchaHandler;
use WallPosterBundle\Post\Post;
use WallPosterBundle\Post\PostImage;
use WallPosterBundle\WallPosterBundle;

class VkProvider extends Provider
{
    # https://oauth.vk.com/authorize?client_id=APP_ID&scope=groups,wall,offline,photos&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.21&response_type=token

    const API_URL = 'https://api.vk.com/method/';

	/** @var  string */
    protected $accessToken;
	/** @var  string */
    protected $apiVersion;
	/** @var  string */
    protected $lang;
	/** @var  string */
    protected $groupId;
	/** @var  CaptchaHandler */
	protected $captchaHandler;

	/**
	 * @param string $accessToken
	 * @param string $groupId
	 * @param string $apiVersion
	 * @param string $lang
	 * @param CaptchaHandler $captchaHandler
	 */
	public function __construct($accessToken, $groupId, $apiVersion, $lang, CaptchaHandler $captchaHandler)
    {
        $this->accessToken = $accessToken;
        $this->apiVersion = $apiVersion;
        $this->lang = $lang;
        $this->groupId = (int)$groupId;
		$this->captchaHandler = $captchaHandler;
    }

	/** {@inheritdoc} */
    public function publish(Post $post)
    {
		if($this->captchaHandler->isCaptchaRequire() && !$this->captchaHandler->getCaptchaValue())
		{
			return false;
		}

		//TODO: Fire event before send wall.post request

		$groupPost = $this->makeApiRequest('wall.post',array(
			'owner_id' => -$this->groupId,
			'from_group' => 1,
			'message' => $post->getMessage().$post->getSocialTagsText(),
			'attachments' => $this->prepareAttachments($post),
		));

		//TODO: Fire event after send wall.post request
		$post->addPublishInformation(WallPosterBundle::VK_PROVIDER, $groupPost);

		return $post;
    }

	/**
	 * @param Post $post
	 * @return bool|string
	 */
	protected function prepareAttachments(Post $post)
    {
        $attachments = false;
        if($post->getImages())
        {
            $uploadServer = $this->makeApiRequest('photos.getWallUploadServer', array('group_id' => $this->groupId));
            $uploadURL = $uploadServer->upload_url;

            $images = array();
			/** @var PostImage $image */
            foreach($post->getImages() as $image)
            {
                $uploadedPhoto = json_decode($this->request($uploadURL,array('photo' => '@'.$image->getFilePath())));
                $savedImage = $this->makeApiRequest('photos.saveWallPhoto', array(
                    'group_id' => $this->groupId,
                    'photo' => $uploadedPhoto->photo,
                    'server' => $uploadedPhoto->server,
                    'hash' => $uploadedPhoto->hash,
                ));
                $images[] = 'photo'.$savedImage[0]->owner_id.'_'.$savedImage[0]->id;
            }

            $attachments = implode(',',$images);
        }

        if($post->getLink())
        {
            $attachments .= $attachments ? ','.$post->getLink()->getUrl() : $post->getLink()->getUrl();
        }

        return $attachments;
    }

	/**
	 * @param string $method
	 * @param array $get
	 * @param array $post
	 * @return \stdClass
	 * @throws VkException
	 */
	public function makeApiRequest($method, array $get = array(), array $post = array())
    {
		if($this->captchaHandler->getCaptchaValue())
		{
			$get['captcha_sid'] = $this->captchaHandler->getCaptchaSid();
			$get['captcha_key'] = $this->captchaHandler->getCaptchaValue();
			$this->captchaHandler->resetCaptchaError();
		}

        $parameters = array();
        foreach ($get as $param => $value) {
            $query = $param . '=';
            if (is_array($value)) {
                $query .= urlencode(implode(',', $value));
            } else {
                $query .= urlencode($value);
            }

            $parameters[] = $query;
        }
        $parameters[] = 'access_token='.$this->accessToken;
        $parameters[] = 'v='.$this->apiVersion;
        $parameters[] = 'lang='.$this->lang;

        $query = implode('&', $parameters);

        $url = self::API_URL . $method . '?' . $query;
        $result = json_decode($this->request($url,$post));

        if(isset($result->error))
        {
            if($result->error->error_code == 14)
            {
				$this->captchaHandler->createCaptcha($result->error->captcha_img, $result->error->captcha_sid);
                throw new VkException($result->error->error_msg, $result->error->error_code, null, $result->error->captcha_sid, $result->error->captcha_img);
            }
            else
            {
                throw new VkException($result->error->error_msg, $result->error->error_code);
            }
        }

        if (isset($result->response))
        {
            return $result->response;
        }

        return $result;
    }
} 