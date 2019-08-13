<?php declare(strict_types=1);

namespace Codegen\Templates\Getter;

use Codegen\Templates\AbstractAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Getter extends AbstractAnnotation
{

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var bool
	 */
	public $nullable;

	public static function getTemplateClass(): string
	{
		return GetterTemplate::class;
	}

}
