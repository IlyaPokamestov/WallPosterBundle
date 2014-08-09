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

namespace WallPosterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WallPosterBundle\Form\Type\CaptchaType;
use WallPosterBundle\Handler\CaptchaHandler;
use WallPosterBundle\Model\Captcha;

class CaptchaController extends Controller
{
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function validateAction(Request $request)
	{
		$message = false;
		$parameters = array();
		if($this->has('wall_poster.captcha_handler'))
		{
			/** @var CaptchaHandler $captchaHandler */
			$captchaHandler = $this->get('wall_poster.captcha_handler');
			if($captchaHandler->isCaptchaRequire())
			{
				$captcha = new Captcha();
				$captcha->setImage($captchaHandler->getCaptchaImage())
					->setValue($captchaHandler->getCaptchaValue());
				$form = $this->createForm(new CaptchaType(), $captcha);

				$form->handleRequest($request);

				if($form->isValid())
				{
					/** @var Captcha $captcha */
					$captcha = $form->getData();
					$captchaHandler->setCaptchaValue($captcha->getValue());
					$parameters['success'] = true;
				}

				$parameters['form'] = $form->createView();
				$parameters['captcha'] = $captcha;
			}
		}
		else
		{
			$message = 'wall-poster.handler_not_found';
		}

		$parameters['message'] = $message;

		return $this->render('WallPosterBundle:Captcha:validate.html.twig', $parameters);
	}
}