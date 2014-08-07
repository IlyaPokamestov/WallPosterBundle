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


class PostLocation
{
	protected $latitude;
	protected $longitude;

	public function __construct($latitude, $longitude)
	{
		if(!$latitude || !$longitude)
			throw new \Exception('Wrong location parameters.');

		$this->longitude = $longitude;
		$this->latitude = $latitude;
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}
} 