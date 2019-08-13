<?php declare(strict_types=1);

namespace Codegen;

use Codegen\Exceptions\CodegenCopyFileException;
use Codegen\Exceptions\CodegenOutputFolderException;
use Nette\Reflection\ClassType;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
class FileWriter
{

	/**
	 * @var ColoredText
	 */
	private $coloredText;

	/**
	 * @var string[]
	 */
	private $acceptedNamespaces;

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var bool
	 */
	private $printGenerated;

	/**
	 * FileWriter constructor.
	 * @param ColoredText $coloredText
	 * @param string[] $acceptedNamespaces
	 * @param string $outputDir
	 */
	public function __construct(ColoredText $coloredText, CliOptionsProvider $config)
	{
		$this->coloredText = $coloredText;
		$this->acceptedNamespaces = $config->getAcceptedNamespaces();
		$this->outputDir = $config->getOutputDir();
		$this->printGenerated = $config->printGenerated() && !$config->printProgressBar();
		$this->printCopied = $config->printAllProcessed() && !$config->printProgressBar();
	}

	public function writeToFile(string $content, ClassType $classRef)
	{
		if (!$this->outputDir) {
			throw new CodegenOutputFolderException(sprintf('Output folder has to be specified: %s', gettype($this->outputDir)));
		}

		$pathParts = explode('\\', $classRef->getName());
		$fileName =  end($pathParts) . '.php';
		$filePath =  $this->getFilePath($classRef);
		$fullPath = $filePath . DIRECTORY_SEPARATOR . $fileName;

		if (file_exists($fullPath)) {
			unlink($fullPath);
		}

		// create file folder if not exists
		if (!file_exists($filePath)) {
			mkdir($filePath, 0777, TRUE);
		}

		file_put_contents($fullPath, $content);
		if ($this->printGenerated) {
			$this->coloredText->printColoredString(sprintf('File %s - %s', $classRef->getFileName(), $this->coloredText->getColoredString('generated', 'green')), 'brown');
		}
	}

	public function copyByReflection(ClassType $classRef)
	{
		if (!$this->outputDir) {
			throw new CodegenOutputFolderException(sprintf('Output folder has to be specified: %s', gettype($this->outputDir)));
		}

		$fileName =  $this->getFileName($classRef);
		$dirPath =  $this->getFilePath($classRef);
		$newFilePath = $dirPath . DIRECTORY_SEPARATOR . $fileName;

		// remove file before new will be generated
		if (file_exists($newFilePath)) {
			unlink($newFilePath);
		}

		// create file folder if not exists
		if (!file_exists($dirPath)) {
			mkdir($dirPath, 0777, TRUE);
		}

		if (!copy($classRef->getFileName(), $newFilePath)) {
			throw new CodegenCopyFileException(sprintf('File %s can not be copied to %s', $classRef->getFileName(), $dirPath));
		}
		if ($this->printCopied) {
			$this->coloredText->printColoredString(sprintf('File %s - %s', $classRef->getFileName(), $this->coloredText->getColoredString('copied', 'blue')), 'brown');
		}
	}

	public function copyFile(string $filePath, string $originBasePath, string $newBasePath): void
	{
		$newPath = str_replace($originBasePath, $newBasePath, $filePath);
		$dirPath =  dirname($newPath);

		// remove file before new will be generated
		if (file_exists($newPath)) {
			unlink($newPath);
		}

		// create file folder if not exists
		if (!file_exists($dirPath)) {
			mkdir($dirPath, 0777, TRUE);
		}

		if (!copy($filePath, $newPath)) {
			throw new CodegenCopyFileException(sprintf('File %s can not be copied to %s', $filePath, $dirPath));
		}
		if ($this->printCopied) {
			$this->coloredText->printColoredString(sprintf('File %s - %s', $filePath, $this->coloredText->getColoredString('copied', 'blue')), 'brown');
		}
	}

	private function getFilePath(ClassType $classRef)
	{
		$filePath = NULL;
		if ($this->acceptedNamespaces) {
			foreach ($this->acceptedNamespaces as $accept) {
				if (strpos($classRef->getNamespaceName(), $accept) === 0) {
					$filePath = ltrim(str_replace('\\', DIRECTORY_SEPARATOR, str_replace($accept, '', $classRef->getNamespaceName())), DIRECTORY_SEPARATOR);
				}
			}
		} else {
			$filePath = ltrim(str_replace('\\', DIRECTORY_SEPARATOR, $classRef->getNamespaceName()), DIRECTORY_SEPARATOR);
		}

		$outputFilePath = $this->outputDir;
		if ($filePath) {
			$outputFilePath .= DIRECTORY_SEPARATOR . $filePath;
		}

		return $outputFilePath;
	}

	private function getFileName(ClassType $classRef)
	{
		$pathParts = explode('/', $classRef->getFileName());
		return end($pathParts);
	}

}