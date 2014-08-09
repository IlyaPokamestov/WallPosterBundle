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

namespace WallPosterBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaptchaType extends AbstractType
{
	/** {@inheritdoc} */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('value','text',array('label' => 'captcha_value'));
	}

	/** {@inheritdoc} */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
			->setDefaults(
				array(
					'data_class' => 'WallPosterBundle\Model\Captcha',
				)
			);
	}

	public function getName()
	{
		return 'wall_poster_captcha';
	}
} 