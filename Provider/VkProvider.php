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

use Justy\Bundle\WallPosterBundle\Exception\VkException;
use Justy\Bundle\WallPosterBundle\Post\Post;

class VkProvider extends Provider
{
    # https://oauth.vk.com/authorize?client_id=APP_ID&scope=groups,wall,offline,photos&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.21&response_type=token

    const API_URL = 'https://api.vk.com/method/';

    protected $accessToken;
    protected $apiVersion;
    protected $lang;
    protected $groupId;

    public function __construct($accessToken, $groupId, $apiVersion, $lang)
    {
        $this->accessToken = $accessToken;
        $this->apiVersion = $apiVersion;
        $this->lang = $lang;
        $this->groupId = (int)$groupId;
        $this->type = self::TYPE_VK;
    }

    public function publish(Post $post)
    {
        //TODO: Check captcha error in cache before make requests

        try
        {
            //TODO: Fire event before send wall.post request

            $groupPost = $this->makeApiRequest('wall.post',array(
                'owner_id' => -$this->groupId,
                'from_group' => 1,
                'message' => $post->getMessage().$post->getSocialTagsText(),
                'attachments' => $this->prepareAttachments($post),
            ));

            //TODO: Fire event after send wall.post request

            return $groupPost->post_id;
        }
        catch(VkException $ex)
        {
            var_dump($ex);
            //TODO: Notify about exception
            //TODO:If error is captcha required then make cache
        }

        return false;
    }

    protected function prepareAttachments(Post $post)
    {
        $attachments = false;
        if($post->getImages())
        {
            $uploadServer = $this->makeApiRequest('photos.getWallUploadServer', array('group_id' => $this->groupId));
            $uploadURL = $uploadServer->upload_url;

            $images = array();
            foreach($post->getImages() as $imageUrl)
            {
                $uploadedPhoto = json_decode($this->request($uploadURL,array('photo' => '@'.$imageUrl)));
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
            $attachments .= $attachments ? ','.$post->getLink() : $post->getLink();
        }

        return $attachments;
    }

    protected function makeApiRequest($method, array $get = array(), array $post = array())
    {
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