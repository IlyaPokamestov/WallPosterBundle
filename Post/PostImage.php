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


class PostImage
{
	protected $pathToFile;
	protected $browserUrl;

	public function __construct($pathToFile, $browserUrl)
	{
		if(!$pathToFile || !$browserUrl)
			throw new \Exception('Image require both paths');

		$this->pathToFile = $pathToFile;
		$this->browserUrl = $browserUrl;
	}

	public function getFilePath()
	{
		return $this->pathToFile;
	}

	public function getBrowserUrl()
	{
		return $this->browserUrl;
	}
} 