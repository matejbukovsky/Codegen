<?php declare(strict_types=1);

namespace Codegen\Templates;

use Codegen\Exceptions\CodegenOverwriteMethodException;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
abstract class AbstractAnnotation
{

	/**
	 * @var string
	 */
	public $target;

	public static function getTemplateClass(): string
	{
		throw new CodegenOverwriteMethodException(sprintf('Method "getTemplateClass" of %s has to be overwriten.', static::class));
	}

}