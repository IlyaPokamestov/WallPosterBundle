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

namespace WallPosterBundle\Handler;


use Symfony\Component\Filesystem\Filesystem;

class CaptchaHandler
{
	const CONFIGURATION_FILE_NAME = 'captcha.json';

	protected $cacheDirectory;
	protected $filesystem;
	protected $configuration;

	public function __construct($cacheDirectory)
	{
		$this->cacheDirectory = $cacheDirectory;
		$this->filesystem = new Filesystem();

		if(!$this->filesystem->exists($this->cacheDirectory))
		{
			$this->filesystem->mkdir($this->cacheDirectory);
		}

		if(!$this->filesystem->exists($this->cacheDirectory.self::CONFIGURATION_FILE_NAME))
		{
			$this->resetCaptchaError();
		}
		else
		{
			$this->configuration = json_decode(file_get_contents($this->cacheDirectory.self::CONFIGURATION_FILE_NAME));
		}
	}

	public function isCaptchaRequire()
	{
		return isset($this->configuration->error) && $this->configuration->error == true;
	}

	public function getCaptchaImage()
	{
		return isset($this->configuration->captcha_img) ? $this->configuration->captcha_img : false;
	}

	public function getCaptchaSid()
	{
		return isset($this->configuration->captcha_sid) ? $this->configuration->captcha_sid : false;
	}

	public function getCaptchaValue()
	{
		return isset($this->configuration->captcha_value) ? $this->configuration->captcha_value : false;
	}

	public function setCaptchaValue($value)
	{
		return $this->writeInConfigFile(
			array(
				'error' => true,
				'captcha_img' => $this->configuration->captcha_img,
				'captcha_sid' => $this->configuration->captcha_sid,
				'captcha_value' => $value,
			)
		);
	}

	public function createCaptcha($captchaImg, $captchaSid)
	{
		return $this->writeInConfigFile(
			array(
				'error' => true,
				'captcha_img' => $captchaImg,
				'captcha_sid' => $captchaSid,
				'captcha_value' => false,
			)
		);
	}

	public function resetCaptchaError()
	{
		if($this->writeInConfigFile(
			array(
				'error' => false,
				'captcha_img' => false,
				'captcha_sid' => false,
				'captcha_value' => false,
			)
		))
		{
			$this->configuration = json_decode(file_get_contents($this->cacheDirectory.self::CONFIGURATION_FILE_NAME));
			return true;
		}
		return false;
	}

	protected function writeInConfigFile($parameters)
	{
		return file_put_contents($this->cacheDirectory.self::CONFIGURATION_FILE_NAME, json_encode($parameters));
	}
}