<?php declare(strict_types=1);

namespace Codegen;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 */
class CliOptionsProvider
{

	/**
	 * @var string
	 */
	private $rootDir;

	/**
	 * @var string
	 */
	private $outputDir;

	/**
	 * @var array
	 */
	private $acceptedNamespaces;

	/**
	 * @var array
	 */
	private $excludedNamespaces;

	/**
	 * @var array
	 */
	private $sourcePaths;

	/**
	 * @var array
	 */
	private $templateDirs;

	/**
	 * @var string
	 */
	private $baseNamespace;

	/**
	 * @var bool
	 */
	private $composerDumpAutoload;

	/**
	 * @var bool
	 */
	private $verbose;

	/**
	 * @var bool
	 */
	private $help;

	/**
	 * @var bool
	 */
	private $progressbar;

	/**
	 * @var bool
	 */
	private $printGenerated;

	/**
	 * @var bool
	 */
	private $printAllProcessed;

	/**
	 * @var bool
	 */
	private $copyNonPhp;

	/**
	 * @var bool
	 */
	private $copyWithoutAnnotation;

	/**
	 * @var bool
	 */
	private $printConfig;

	public function __construct(string $rootDir)
	{
		// DEFAULTS
		$this->templateDirs = [__DIR__ . '/Templates'];
		$this->rootDir = $rootDir;
		$this->outputDir = './codegen-generated';
		$this->acceptedNamespaces = [];
		$this->excludedNamespaces = [];
		$this->sourcePaths = [];
		$this->baseNamespace = 'Codegen';
		$this->composerDumpAutoload = TRUE;
		$this->verbose = FALSE;
		$this->help = FALSE;
		$this->progressbar = FALSE;
		$this->printGenerated = TRUE;
		$this->printAllProcessed = FALSE;
		$this->copyWithoutAnnotation = FALSE;
		$this->copyNonPhp = FALSE;
		$this->printConfig = FALSE;

		$this->loadFromCli();

		if (!file_exists($this->outputDir)) {
			mkdir($this->outputDir, 0777, TRUE);
		}

		// PURIFY
		$this->outputDir = rtrim(realpath($this->outputDir), '/');
		$this->rootDir = rtrim(realpath($this->rootDir), '/');
		$this->templateDirs = array_unique($this->templateDirs);
		$this->acceptedNamespaces = array_unique($this->acceptedNamespaces);
		$this->excludedNamespaces = array_unique($this->excludedNamespaces);
		foreach ($this->sourcePaths as $key => $path) {
			if (file_exists($path)) {
				$this->sourcePaths[$key] = realpath($path);
			}
		}
	}

	private function loadFromCli()
	{
		// CLI OPTIONS
		$options = getopt('t:o:a:e:s:n:dvhpgl', [
			'template-dir:',
			'output-dir:',
			'accept-namespace:',
			'exclude-namespace:',
			'source-paths:',
			'base-namespace:',
			'composer-dump-autoload',
			'verbose',
			'help',
			'pg-bar',
			'print-generated',
			'list-all-processed'
		]);

		foreach ($options as $paramName => $value) {
			if ($paramName === 't' || $paramName === 'template-dir') {
				$this->templateDirs = array_merge($this->templateDirs, is_array($value) ? $value : [$value]);
			}
			if ($paramName === 'o' || $paramName === 'output-dir') {
				$this->outputDir = $value;
			}
			if ($paramName === 'a' || $paramName === 'accept-namespace') {
				$this->acceptedNamespaces = is_array($value) ? $value : [$value];
			}
			if ($paramName === 'e' || $paramName === 'exclude-namespace') {
				$this->excludedNamespaces = is_array($value) ? $value : [$value];
			}
			if ($paramName === 's' || $paramName === 'source-paths') {
				$this->sourcePaths = is_array($value) ? $value : [$value];
			}
			if ($paramName === 'n' || $paramName === 'base-namespace') {
				$this->outputDir = $value;
			}

			$this->composerDumpAutoload = ($paramName === 'd' || $paramName === 'composer-dump-autoload') ? TRUE : $this->composerDumpAutoload;
			$this->verbose = ($paramName === 'v' || $paramName === 'verbose') ? TRUE : $this->verbose;
			$this->help = ($paramName === 'h' || $paramName === 'help') ? TRUE : $this->help;
			$this->progressbar = ($paramName === 'p' || $paramName === 'pg-bar') ? TRUE : $this->progressbar;
			$this->printGenerated = ($paramName === 'g' || $paramName === 'print-generated') ? TRUE : $this->printGenerated;
			$this->printAllProcessed = ($paramName === 'l' || $paramName === 'list-all-processed') ? TRUE : $this->printAllProcessed;
			$this->copyWithoutAnnotation = ($paramName === 'i' || $paramName === 'copy-without-annotation') ? TRUE : $this->copyWithoutAnnotation;
			$this->copyNonPhp = ($paramName === 'j' || $paramName === 'copy-non-php') ? TRUE : $this->copyNonPhp;
			$this->printConfig = ($paramName === 'c' || $paramName === 'print-config') ? TRUE : $this->printConfig;
		}
	}

	public function getVariableDescriptions()
	{
		return [
			'-o | --output-dir (string / codegen-generated)' => 'Where to place generated files/folders.',
			'-t | --template-dir (string[] / ["Templates"])' => 'Where to look for code template classes.',
			'-a | --accept-namespace (string[] / [])' => 'File namespaces to be included in source files.',
			'-e | --exclude-namespace (string[]/ [])' => 'File namespaces to be excluded from source files.',
			'-s | --source-paths (string[] / [])' => 'Paths to be included in source files.',
			'-n | --base-namespace (string / App)' => 'Namespace defining rootDir in generated folder. Important for keeping file structure when copying non PHP files',
			'-i | --copy-without-annotation (bool / FALSE)' => 'Copy PHP files withou @Codegen annotation to generated folder.',
			'-j | --copy-non-php (bool / FALSE)' => 'Copy non PHP files to generated folder.',
			'-d | --composer-dump-autoload (bool / TRUE)' => 'Dump composer autoload after generating files.',
			'-p | --pg-bar (bool / FALSE)' => 'Print progress bar (prevent to print generated files if enabled).',
			'-g | --prit-generated (bool / TRUE)' => 'Print generated files (if progress bar disabled).',
			'-l | --print-all-processed (bool / FALSE)' => 'Print all processed files (generated/copied) (if progress bar disabled).',
			'-c | --print-config (bool / FALSE)' => 'Print loaded configuration.',
			'-v | --verbose (bool / FALSE)' => 'Print composer info and stackTrace when error occur.',
			'-h | --help (bool / FALSE)' => 'Print this help page.',
		];
	}

	/**
	 * @return string
	 */
	public function getRootDir(): string
	{
		return $this->rootDir;
	}

	/**
	 * @return string
	 */
	public function getOutputDir(): string
	{
		return $this->outputDir;
	}

	/**
	 * @return array
	 */
	public function getAcceptedNamespaces(): array
	{
		return $this->acceptedNamespaces;
	}

	/**
	 * @return array
	 */
	public function getExcludedNamespaces(): array
	{
		return $this->excludedNamespaces;
	}

	/**
	 * @return array
	 */
	public function getSourcePaths(): array
	{
		return $this->sourcePaths;
	}

	/**
	 * @return string
	 */
	public function getBaseNamespace(): string
	{
		return $this->baseNamespace;
	}

	/**
	 * @return array
	 */
	public function getTemplateDirs(): array
	{
		return $this->templateDirs;
	}

	/**
	 * @return array
	 */
	public function getComposerDumpAutoload(): bool
	{
		return $this->composerDumpAutoload;
	}

	/**
	 * @return bool
	 */
	public function isVerbose(): bool
	{
		return $this->verbose;
	}

	/**
	 * @return bool
	 */
	public function isHelp(): bool
	{
		return $this->help;
	}

	/**
	 * @return bool
	 */
	public function printProgressBar(): bool
	{
		return $this->progressbar;
	}

	/**
	 * @return bool
	 */
	public function printGenerated(): bool
	{
		return $this->printGenerated;
	}

	/**
	 * @return bool
	 */
	public function printAllProcessed(): bool
	{
		return $this->printAllProcessed;
	}

	/**
	 * @return string
	 */
	public function getOutputFolderToCopy(): string
	{
		return $this->outputDir . '/' . $this->baseNamespace;
	}

	/**
	 * @return bool
	 */
	public function copyWithoutAnnotation(): bool
	{
		return $this->copyWithoutAnnotation;
	}

	/**
	 * @return bool
	 */
	public function copyNonPHP(): bool
	{
		return $this->copyNonPhp;
	}

}