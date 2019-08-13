<?php declare(strict_types=1);

namespace Codegen;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
class AutoLoader
{

	/**
	 * [namespace => realPath]
	 *
	 * @var string[]
	 */
	private $classes;

	public function __construct()
	{
		spl_autoload_register([$this, 'tryLoad']);
		$this->classes = [];
	}

	public function addClasses(array $classes): self
	{
		$this->classes = array_merge($this->classes, $classes);

		return $this;
	}

	public function tryLoad(string $className): bool
	{
		if (isset($this->classes[$className])) {
			require_once($this->classes[$className]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return string[]
	 */
	public function getClasses(): array
	{
		return $this->classes;
	}

}