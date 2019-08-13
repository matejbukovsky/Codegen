<?php

namespace Codegen;

class ColoredText
{
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct()
	{
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	public function bold(string $string)
	{
		return "\033[1m$string\033[0m";
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null)
	{
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .= $string . "\033[0m";
		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors()
	{
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors()
	{
		return array_keys($this->background_colors);
	}

	public function printColoredString($string, $foreground_color = null, $background_color = null) {
		echo $this->getColoredString($string, $foreground_color, $background_color) . "\n";
	}

	public function printErrorString($string)
	{
		$this->printColoredString(sprintf("\n\xE2\x9c\x95 %s", $string), 'white', 'red');
	}

	public function printKeyVal(string $key, string $val, string $mask = "%-25s%-10s%-150s")
	{
		$this->printColoredString(sprintf($mask, $key, ' ', $this->getColoredString($val, 'brown')), 'yellow');
	}

	private function getLibVersion(): ?string
	{
		$installed = __DIR__ . '/../../../composer/installed.json';
		if (file_exists(__DIR__ . '/../../../composer/installed.json')) {
			$packages = json_decode(file_get_contents($installed), TRUE);
			foreach ($packages as $package) {
				if ($package['name'] === 'matejbukovsky/codegen') {
					return $package['version'];
				}
			}
		}

		return NULL;
	}

	public function printAppName()
	{

		$version = $this->getLibVersion();
		if ($version) {
			$version = 'v' . $version;
		}
		$appName = <<<EOF
   _____          _
  / ____|        | |
 | |     ___   __| | ___  __ _  ___ _ __
 | |    / _ \ / _` |/ _ \/ _` |/ _ \ '_ \
 | |___| (_) | (_| |  __/ (_| |  __/ | | |
  \_____\___/ \__,_|\___|\__, |\___|_| |_| $version
                          __/ |
                         |___/

EOF;
		$this->printColoredString($appName, 'light_blue');
	}

	public function printArrayTable(array $values)
	{
		foreach ($values as $paramname => $description) {
			$this->printKeyVal($paramname, $description, "%-60s%-10s%-150s");
		}
		echo "\n";
	}

	public function progressBar(int $done, int $total, int $width = 50) {
		$perc = floor(($done / $total) * 100);
		$donePerc = $perc === 0.0 ? 0 : floor($width / (100 / $perc));
		$toDoPerc = $perc === 100.0 ? 0 : round($width / (100 / (100 - $perc)));
		$write = sprintf("\033[0G\033[2K[%'={$donePerc}s>%-{$toDoPerc}s] - $perc%% - $done/$total", "", "");
		fwrite(STDOUT, $write);
		if ($perc === 100.0) {
			echo "\n";
		}
	}

}