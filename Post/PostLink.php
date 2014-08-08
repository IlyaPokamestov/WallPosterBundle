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


class PostLink
{
	protected $url;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function isHttps()
	{
		return strpos(mb_strtolower($this->url),'https://') !== false;
	}
} 