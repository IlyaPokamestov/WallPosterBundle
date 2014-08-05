<?php
/**
 * Created by PhpStorm.
 * User: dario_swain
 * Date: 8/4/14
 * Time: 4:14 PM
 */

namespace Justy\Bundle\WallPosterBundle\Provider;


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
        $attachments = false;
        if($post->getImages())
        {
            $uploadServer = $this->makeApiRequest('photos.getWallUploadServer', array('group_id' => $this->groupId));
            $uploadURL = $uploadServer->upload_url;

            $imageIds = array();
            foreach($post->getImages() as $imageUrl)
            {
                $uploadedPhoto = $this->request($uploadURL,array('photo' => '@'.$imageUrl));
                $savedImage = $this->makeApiRequest('photos.saveWallPhoto', array(
                    'group_id' => $this->groupId,
                    'photo' => $uploadedPhoto->photo,
                    'server' => $uploadedPhoto->server,
                    'hash' => $uploadedPhoto->hash,
                ));
                $imageIds[] = $savedImage->id;
            }

            $attachments = implode(',',$imageIds);
        }

        $attachments .= $attachments ? ','.$post->getLink() : $post->getLink();

        $groupPost = $this->makeApiRequest('wall.post',array(
                'owner_id' => -$this->groupId,
                'from_group' => 1,
                'message' => $post->getMessage().$post->getSocialTagsText(),
                'attachments' => $attachments,
        ));

        return $groupPost->post_id;
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

        if (isset($result->response))
        {
            return $result->response;
        }

        return $result;
    }
} 