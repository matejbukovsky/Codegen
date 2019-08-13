<?php declare(strict_types=1);

namespace Codegen\Templates\Setter;

use Codegen\Templates\AbstractAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Setter extends AbstractAnnotation
{

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $default;

	/**
	 * @var bool
	 */
	public $nullable;

	public static function getTemplateClass(): string
	{
		return SetterTemplate::class;
	}

}
