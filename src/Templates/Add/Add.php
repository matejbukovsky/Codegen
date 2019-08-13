<?php declare(strict_types=1);

namespace Codegen\Templates\Add;

use Codegen\Templates\AbstractAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Add extends AbstractAnnotation
{

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var bool
	 */
	public $allowKey;

	/**
	 * @var bool
	 */
	public $keyType;

	public static function getTemplateClass(): string
	{
		return AddTemplate::class;
	}

}
