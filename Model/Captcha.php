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

namespace WallPosterBundle\Model;

class Captcha
{
	protected $value;
	protected $image;

	public function getImage()
	{
		return $this->image;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setImage($image)
	{
		$this->image = $image;
		return $this;
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
}