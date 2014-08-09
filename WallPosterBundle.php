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

namespace WallPosterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WallPosterBundle extends Bundle
{
	const VK_PROVIDER = 'vk';
	const FACEBOOK_PROVIDER = 'facebook';
	const TWITTER_PROVIDER = 'twitter';

	public static function getAvailableProviders()
	{
		return array(self::VK_PROVIDER, self::FACEBOOK_PROVIDER, self::TWITTER_PROVIDER);
	}
}