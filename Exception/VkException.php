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

namespace WallPosterBundle\Exception;


class VkException extends \Exception
{
    protected $captchaSid;
    protected $captchaImg;

    public function __construct($message = "", $code = 0, \Exception $previous = null, $captchaSid = 0, $captchaImg = "")
    {
        parent::__construct($message, $code, $previous);
        $this->captchaSid = $captchaSid;
        $this->captchaImg = $captchaImg;
    }

    public function getCaptchaSid()
    {
        return $this->captchaSid;
    }

    public function getCaptchaImg()
    {
        return $this->captchaImg;
    }
}