<?php declare(strict_types=1);

namespace Codegen\Templates;

use Codegen\Exceptions\CodegenMethodConflictException;
use Codegen\Exceptions\CodegenMissingOptionException;
use Codegen\Exceptions\CodegenOverwriteMethodException;
use Nette\PhpGenerator\ClassType;


/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
abstract class AbstractTemplate
{

	/**
	 * @var ClassType
	 */
	private $classType;

	/**
	 * @var array
	 */
	private $annotations;

	public function init(ClassType $classType, AbstractAnnotation $annotations)
	{
		$this->classType = $classType;
		$this->annotations = $annotations;
	}

	public function generateCode(): ClassType
	{
		throw new CodegenOverwriteMethodException(sprintf('Method "addCode" of %s has to be overwriten.', this::class));
	}

	protected function getClassType(): ClassType
	{
		return $this->classType;
	}

	protected function getAnnotations(): AbstractAnnotation
	{
		return $this->annotations;
	}

	protected function checkRequiredParam(string $paramName): void
	{
		if (!array_key_exists($paramName, $this->annotations) || $this->annotations->$paramName === NULL) {
			throw new CodegenMissingOptionException(sprintf('Missing required parameter "%s" in %s::%s for template %s', $paramName, $this->getClassType()->getName(), $this->getPropertyName(), static::class));
		}
	}

	protected function checkMethodExistence(string $method): void
	{
		if (isset($this->classType->getMethods()[$method])) {
			throw new CodegenMethodConflictException(sprintf('Generated method (%s) already exists in %s.', $method, $this->getClassType()->getNamespace()->getName()));
		}
	}

	protected function getPropertyName(): string
	{
		return $this->getAnnotations()->target;
	}

}