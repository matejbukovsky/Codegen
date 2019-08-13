<?php declare(strict_types=1);

namespace Codegen;
use Codegen\Templates\AbstractAnnotation;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
class FileLoader
{

	/**
	 * Return all files in path recursively. Keys of file paths are class names
	 *
	 * @param $path
	 * @param string|NULL $excludePath
	 * @return string[]
	 */
	public function getFilesWithNamespace($path, string $excludePath = NULL): array
	{
		$files = [];
		$foundFiles = $this->getFilesFromPath($path);
		foreach ($foundFiles as $file) {
			$realPath = realpath($file);
			if ($excludePath && strpos($realPath, realpath($excludePath)) === 0) {
				continue;
			}
			$namespace = $this->getNamespace($realPath);
			$class = $this->getClassName($realPath);
			$files[$namespace . '\\' . $class] = $realPath;
		}

		return $files;
	}

	/**
	 * @param $paths
	 * @return string[]
	 */
	public function getFilesFromPaths(array $paths): array
	{
		$filesByPath = [];
		foreach ($paths as $path) {
			$filesByPath = array_merge($filesByPath, $this->getFilesWithNamespace($path));
		}

		return array_flip($filesByPath);
	}

	/**
	 * @param array $paths
	 * @return string[]
	 */
	public function getTemplateAnnotations(array $paths): array
	{
		$files = [];
		foreach ($paths as $path) {
			$foundFiles = $this->getFilesFromPath($path);
			foreach ($foundFiles as $file) {
				$realPath = realpath($file);
				$namespace = $this->getNamespace($realPath);
				$class = $this->getClassName($realPath);
				$classPath = $namespace . '\\' . $class;
				if (is_subclass_of($classPath, AbstractAnnotation::class)) {
					$files[$class] = $classPath;
				}
			}
		}

		return $files;
	}

	/**
	 * Return all PHP files in path recursively.
	 *
	 * @param $path
	 * @return string[]
	 */
	public function getFilesFromPath(string $path, ?string $extension = 'php'): array {
		$files = [];

		if (is_file($path)) {
			$file = new \SplFileInfo($path);
			if ($realPath = $this->getFileRealPath($file)) {
				$files[] = $realPath;
			}
		} else {
			$rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
			foreach ($rii as $file) {
				if (!$file->isDir() && $realPath = $this->getFileRealPath($file, $extension)) {
					$files[] = $realPath;
				}
			}
		}

		return $files;
	}

	private function getFileRealPath(\SplFileInfo $file, ?string $extension = 'php'): ?string
	{
		$includeOrExclude = 'include';
		if (substr($extension, 0, 1) === '!') {
			$extension = ltrim($extension, '!');
			$includeOrExclude = 'exclude';
		}

		$realpath = realpath($file->getPathname());
		if ($realpath) {
			if (
				($includeOrExclude === 'include' && substr($realpath, -4) === '.' . $extension) ||
				($includeOrExclude === 'exclude' && substr($realpath, -4) !== '.' . $extension)) {
				return $realpath;
			}
		}

		return NULL;
	}

	/**
	 * @param array $files
	 * @param array $acceptNamespaces
	 * @param array $excludeNamespaces
	 * @return array
	 */
	public function filterFilesByNamespace(array $files, array $acceptNamespaces, array $excludeNamespaces): array
	{
		$filteredFiles = [];
		foreach ($files as $namespace => $file) {
			// if namespace not found
			if (!$namespace) {
				continue;
			}

			// remove class from full namespace
			$parts = explode('\\', $namespace);
			end($parts);
			$baseNamespace = implode('\\', $parts);

			foreach ($acceptNamespaces as $accepted) {
				if (strpos($baseNamespace, $accepted) === 0) {
					if ($excludeNamespaces) {
						foreach ($excludeNamespaces as $excluded) {
							$skipFile = FALSE;
							if (strpos($baseNamespace, $excluded) === 0) {
								$skipFile = TRUE;
							}
							if (!$skipFile) {
								$filteredFiles[$file] = $namespace;
							}
						}
					} else {
						$filteredFiles[$file] = $namespace;
					}
				}
			}
		}

		return array_unique($filteredFiles);
	}

	/**
	 * Parse namespace value from file.
	 *
	 * @param string $filePath Real file path
	 * @return string
	 */
	private function getNamespace(string $filePath): string
	{
		$lines = file($filePath);
		$matches = preg_grep('/^namespace .*;$/', $lines);
		if (!$matches) {
			return '';
		}
		$namespaceLine = array_shift($matches);
		$match = array();
		preg_match('/^namespace (.*);$/', $namespaceLine, $match);
		$fullNamespace = array_pop($match);

		return $fullNamespace;
	}

	/**
	 * @param string $filePath Real file path
	 * @return string
	 */
	private function getClassName(string $filePath): string
	{
		$directoriesAndFilename = explode('/', $filePath);
		$filePath = array_pop($directoriesAndFilename);
		$nameAndExtension = explode('.', $filePath);
		$className = array_shift($nameAndExtension);

		return $className;
	}

}