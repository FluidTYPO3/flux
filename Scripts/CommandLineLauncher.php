<?php
if (!defined('TYPO3_cliMode'))
	die('You cannot run this script directly!');

require_once (PATH_t3lib . 'class.t3lib_cli.php');
require_once ('DynFlexMigration.php');

/**
 * CLI
 */
class tx_flux_cli extends t3lib_cli {

	/**
	 * Constructor
	 */
	function tx_flux_cli() {
		parent::t3lib_cli();
		$this->cli_help['name'] = 'Flux CLI script';
		$this->cli_help['synopsis'] = '###OPTIONS###';
		$this->cli_help['description'] = 'available tasks:' . PHP_EOL . PHP_EOL . $this->cli_indent('migrateFluxOutsource - Migration script for extensions using FEDs page/fce template features', 2);
		$this->cli_help['examples'] = '/.../cli_dispatch.phpsh flux <taskname>';
		$this->cli_help['author'] = 'http://fedext.net';
		$this->cli_help['license'] = 'GNU General Public License';
	}

	/**
	 * CLI engine
	 *
	 * @param array Command line arguments
	 * @return string
	 */
	function cli_main($argv) {
		$task = (string)$this->cli_args['_DEFAULT'][1];
		if (!$task) {
			$this->cli_validateArgs();
			$this->cli_help();
			exit ;
		}

		switch($task) {
			case 'migrate' :
				$this->cli_options[] = array('[extension]', 'Extension (folder) name to parse');
				$this->cli_options[] = array('-m', 'Migrate to flux (templates & DB)');
				$this->cli_options[] = array('-t', 'Migrate to flux (only templates)');
				$this->cli_options[] = array('-d', 'Migrate to flux (only DB)');
				$this->cli_options[] = array('-i', 'Interactive mode (no modifications w/o user confirmation)');
				$this->cli_options[] = array('--dry-run', 'Only display migration changes');
				$this->cli_options[] = array('--additional-type foo [bar]', 'Additional template file type(s) to parse (default types: html)');
				$this->cli_options[] = array('--additional-dir dir1 [dir2]', 'Additional template directories to parse (e.g.: /fileadmin/templates) - absolute paths relate to TYPO3 root; realtive paths relate to extenision root');
				$this->cli_options[] = array('-f', 'Force (omit warning message regarding backup)');
				$configuration = $this->parseMigrationConfig();

				if (!$configuration[DynFlexMigration::CONFIG_TARGET]) {
					$this->cli_help();
					exit ;
				}
				//var_dump($configuration);
				$migrator = new DynFlexMigration($this);
				$migrator->migrate($configuration);
				break;
			default :
				echo 'No valid Task given' . "\n\n";
				$this->cli_help();
				break;
		}
	}

	/**
	 * parse CLI arguments and assign defaults
	 * @TODO set defaults in migration class?
	 * @return array configuration array
	 */
	protected function parseMigrationConfig() {
		$configuration = array();
		// extension name
		$configuration[DynFlexMigration::CONFIG_EXT] = '*';
		if ($extension = $this->cli_args['_DEFAULT'][2]) {
			$configuration[DynFlexMigration::CONFIG_EXT] = $extension;
		}
		// mode: auto / interactive / dry
		$configuration[DynFlexMigration::CONFIG_MODE] = DynFlexMigration::CONFIG_MODE_AUTO;
		if ($this->cli_isArg('-i')) {
			$configuration[DynFlexMigration::CONFIG_MODE] = DynFlexMigration::CONFIG_MODE_INTERACTIVE;
		} elseif ($this->cli_isArg('--dry-run')) {
			$configuration[DynFlexMigration::CONFIG_MODE] = DynFlexMigration::CONFIG_MODE_DRY;
		}
		// target: template / database
		if ($this->cli_isArg('-t')) {
			$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_TEMPLATES] = TRUE;
		}
		if ($this->cli_isArg('-d')) {
			$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_DATABASE] = TRUE;
		}
		if ($this->cli_isArg('-m')) {
			$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_TEMPLATES] = TRUE;
			$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_DATABASE] = TRUE;
		}
		// enable both mirations if interactive or dry-run mode and no -t -d -m switch given
		if ($configuration[DynFlexMigration::CONFIG_MODE] != DynFlexMigration::CONFIG_MODE_AUTO) {
			if (!$configuration[DynFlexMigration::CONFIG_TARGET]) {
				$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_TEMPLATES] = TRUE;
				$configuration[DynFlexMigration::CONFIG_TARGET][DynFlexMigration::CONFIG_TARGET_DATABASE] = TRUE;
			}
		}
		// template file types
		$configuration[DynFlexMigration::CONFIG_TEMPLATE_TYPES][] = 'html';
		#$configuration[DynFlexMigration::CONFIG_TEMPLATE_TYPES][] = 'xml';
		$index = 0;
		while ($extension = $this->cli_argValue('--additional-type', $index++)) {
			$configuration[DynFlexMigration::CONFIG_TEMPLATE_TYPES][] = $extension;
		}
		// template directories
		$configuration[DynFlexMigration::CONFIG_TEMPLATE_DIRECTORIES][] = 'Resources/Private';
		$index = 0;
		while ($directory = $this->cli_argValue('--additional-dir', $index++)) {
			$configuration[DynFlexMigration::CONFIG_TEMPLATE_DIRECTORIES][] = $directory;
		}
		// omit backup warning
		$configuration[DynFlexMigration::CONFIG_OMIT_BACKUP_WARNING] = FALSE;
		if ($this->cli_isArg('-f')) {
			$configuration[DynFlexMigration::CONFIG_OMIT_BACKUP_WARNING] = TRUE;
		}
		return $configuration;
	}

	/**
	 * Asks for Yes/No from shell and returns true if "y" or "yes" is found as input (flushes ob before waitung 4 input).
	 *
	 * @param	string		String to ask before...
	 * @return	boolean		TRUE if "y" or "yes" is the input (case insensitive)
	 */
	function cli_keyboardInput_yes($msg = '') {
		echo $msg . ' (Yes/No + return): ';
		// ONLY makes sense to echo it out since we are awaiting keyboard input - that cannot be silenced...
		ob_flush();
		return t3lib_div::inList('y,yes', strtolower($this->cli_keyboardInput()));
	}

}

$cleanerObj = t3lib_div::makeInstance('tx_flux_cli');
$cleanerObj->cli_main($_SERVER['argv']);
?>