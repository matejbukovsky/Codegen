<?php


namespace Codegen;


use Codegen\Templates\AbstractAnnotation;
use Codegen\Templates\AbstractTemplate;
use Doctrine\Common\Annotations\AnnotationReader;
use Nette\PhpGenerator\ClassType as GeneratorClassType;
use Nette\Reflection\ClassType;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
class TemplateApplicator
{

	/**
	 * @var string[]
	 */
	private $templates;

	/**
	 * @var AnnotationReader
	 */
	private $annotationReader;

	/**
	 * @var array
	 */
	private $templateClasses = [];

	/**
	 * @param string[] $templateDirs
	 */
	public function __construct(FileLoader $fileLoader, array $templateDirs)
	{
		$this->templates = $fileLoader->getTemplateAnnotations($templateDirs);
		$this->annotationReader = new AnnotationReader();
	}

	public function applyTemplates(ClassType $classReflection, GeneratorClassType $classGenerator): GeneratorClassType
	{
		foreach ($classReflection->getProperties() as $prop) {
			foreach ($this->templates as $templateName => $templateAnnotationClass) {
				/** @var AbstractAnnotation $propertyAnnotations */
				$propertyAnnotations = $this->annotationReader->getPropertyAnnotation($prop, $templateAnnotationClass);
				if ($propertyAnnotations) {
					$propertyAnnotations->target = $prop->getName();
					$template = $this->initTemplate($templateAnnotationClass, $classGenerator, $propertyAnnotations);
					$classGenerator = $template->generateCode();
				}
			}
		}

		return $classGenerator;
	}

	/**
	 * Create new instance if not exists yet.
	 *
	 * @return mixed
	 */
	private function initTemplate(string $templateAnnotationClass, GeneratorClassType $classTypeGen, AbstractAnnotation $annotations): AbstractTemplate
	{
		$templateClass = $templateAnnotationClass::getTemplateClass();

		if (!array_key_exists($templateClass, $this->templateClasses)) {
			$template = new $templateClass();
			$this->templateClasses[$templateClass] = $template;
		}

		$this->templateClasses[$templateClass]->init($classTypeGen, $annotations);
		return $this->templateClasses[$templateClass];
	}

}