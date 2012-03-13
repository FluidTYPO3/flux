<?php
/**
 * Class for migrating user extensions using FED page and fce template
 * feature to flux
 * @TODO make this a base class that can be used by other migrations?
 * maybe even implement something like "migration recipes"?
 */
class DynFlexMigration {

	const CONFIG_EXT = 'extension';

	const CONFIG_MODE = 'mode';
	const CONFIG_MODE_AUTO = 'auto';
	const CONFIG_MODE_INTERACTIVE = 'interactive';
	const CONFIG_MODE_DRY = 'dry';

	const CONFIG_TARGET = 'target';
	const CONFIG_TARGET_TEMPLATES = 'templates';
	const CONFIG_TARGET_DATABASE = 'database';

	const CONFIG_TEMPLATE_TYPES = 'fileextensions';
	const CONFIG_TEMPLATE_DIRECTORIES = 'templatedirs';

	const CONFIG_OMIT_BACKUP_WARNING = 'omitbackupwarning';

	/**
	 * @var t3lib_cli
	 */
	protected $cli;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 *
	 * @var Tx_Flux_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 *
	 * @var Tx_Fed_Configuration_ConfigurationManager
	 */
	protected $fedConfigurationManager;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexformService;

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * @var array migration configuration
	 */
	protected $migrationConfiguration;

	/**
	 * CONSTRUCTOR
	 * @var t3lib_cli $cli command line i/f
	 */
	public function __construct(t3lib_cli $cli) {
		$this->cli = $cli;
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationManager = $this->objectManager->get('Tx_Flux_Configuration_ConfigurationManager');
		$this->fedConfigurationManager = $this->objectManager->get('Tx_Fed_Configuration_ConfigurationManager');
		$this->flexformService = $this->objectManager->get('Tx_Flux_Service_FlexForm');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Provider_ConfigurationService');
	}

	/**
	 *
	 */
	public function migrate($configuration) {
		$this->migrationConfiguration = $configuration;
		$this->cli->cli_echo('Migration settings:' . PHP_EOL);
		$this->cli->cli_echo('  Extension to parse:            ' . ($configuration[self::CONFIG_EXT] == '*' ? '*all*' : $configuration[self::CONFIG_EXT]) . PHP_EOL);
		$this->cli->cli_echo('  Template extensions to parse:  ' . join(', ', $configuration[self::CONFIG_TEMPLATE_TYPES]) . PHP_EOL);
		$this->cli->cli_echo('  Template directories to parse: ' . join(', ', $configuration[self::CONFIG_TEMPLATE_DIRECTORIES]) . PHP_EOL . PHP_EOL);
		if($configuration[self::CONFIG_MODE] != self::CONFIG_MODE_DRY && !$configuration[self::CONFIG_OMIT_BACKUP_WARNING]) {
			$this->cli->cli_echo(PHP_EOL . 'WARNING! There is no BACKUP mechanism in place - make sure to take care on your own!' . PHP_EOL, TRUE);
			if(!$this->cli->cli_keyboardInput_yes('Continue')) {
				exit;
			}
		}

		//$this->cli->cli_echo($this->cli->cli_indent('Mod', $indent))
		if($configuration[self::CONFIG_TARGET][self::CONFIG_TARGET_TEMPLATES]) {
			$this->migrateTemplates();
		}
		if($configuration[self::CONFIG_TARGET][self::CONFIG_TARGET_DATABASE]) {
			$this->migrateDatabase();
		}
		if(!$configuration[self::CONFIG_MODE_DRY]) {
			$this->clearCaches();
		}
/*
		//$configurationManager =
		$templateFiles = $this->fedConfigurationManager->getAvailablePageTemplateFiles();
		var_dump($templateFiles);

		$extconfs = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		foreach ($extconfs as $extName => $config) {
			echo $extName . PHP_EOL;
		}
*/
	}

	/**
	 * do template migration
	 * @return void
	 */
	protected function migrateTemplates() {
		$cli = $this->cli;
		$cli->cli_echo(PHP_EOL . 'MIGRATING TEMPLATES' . PHP_EOL);
		// retrieve absolute and relative directories
		$absoluteDirectories = $relaviveDirectories = array();
		foreach($this->migrationConfiguration[self::CONFIG_TEMPLATE_DIRECTORIES] as $directory) {
				if(strpos($directory, DIRECTORY_SEPARATOR) === 0){
					#echo 'FOUND ABSOLUTE';
					$absoluteDirectories[] = substr($directory, 1);
				} else {
					$relaviveDirectories[] = $directory;
				}
		}
		// parse absolute direcories
		$cli->cli_echo(PHP_EOL . 'Parsing absolute directories' . PHP_EOL);
		foreach($absoluteDirectories as $directory) {
			$cli->cli_echo($cli->cli_indent('Parsing ' . $directory . '...' . PHP_EOL, 2));
			$files = t3lib_div::getAllFilesAndFoldersInPath(array(), $directory . '/', join(',', $this->migrationConfiguration[self::CONFIG_TEMPLATE_TYPES]));
			foreach($files as $file) {
				$this->migrateFile($file);
			}
		}
		// parse extensions
		if(($ext = $this->migrationConfiguration[self::CONFIG_EXT]) == '*') {
			$this->migrationConfiguration[self::CONFIG_EXT] = t3lib_div::get_dirs('typo3conf/ext/');
		} else {
			$this->migrationConfiguration[self::CONFIG_EXT] = array($ext);
		}
		foreach($this->migrationConfiguration[self::CONFIG_EXT] as $ext) {
			if (t3lib_extMgm::isLoaded($ext) === FALSE) {
				continue;
			}
			$cli->cli_echo(PHP_EOL . 'Parsing ' . $ext . '...' . PHP_EOL);
			foreach($relaviveDirectories as $directory) {
				$cli->cli_echo($cli->cli_indent(PHP_EOL . 'Parsing ' . $directory . '...' . PHP_EOL, 2));
				$path = t3lib_extMgm::siteRelPath($ext) . $directory . '/';
				$files = t3lib_div::getAllFilesAndFoldersInPath(array(), $path, join(',', $this->migrationConfiguration[self::CONFIG_TEMPLATE_TYPES]));//, TRUE);
				foreach($files as $file) {
					$this->migrateFile($file, $path);
				}
			}
		}
	}

	protected function migrateFile($fileName, $stripPath = NULL) {
		$cli = $this->cli;
		$oldTags = array(
			array('fed:flexform.group', 'fed:page.group', 'fed:fce.group'),
			array('fed:fce', 'fed:flexform'),
			array('fed:page.field.'),
			array('fed:page '),
			array('<\/fed:page>')
		);
		$newTags = array(
			'flux:flexform.sheet',
			'flux:flexform',
			'flux:flexform.field.',
			'flux:flexform ',
			'</flux:flexform>'
		);

		$fileInfo = pathinfo($fileName);
		if ($stripPath !== NULL) {
			$displayFilename = str_replace($stripPath, '', $fileName);
		} else {
			$displayFilename = $fileInfo['basename'];
		}
		$cli->cli_echo($cli->cli_indent('Parsing ' . $displayFilename . PHP_EOL, 2));
		$file = file_get_contents($fileName);
		$fileBackup = $file;

		// replace tags
		$i=0;
		foreach ($newTags as $newTag) {
			$file = preg_replace('/(' . implode('|', $oldTags[$i++]) . ')/um', $newTag, $file);
		}

		// add flux namespace if it does not exist and is required
		$fluxNamespace = '{namespace flux=Tx_Flux_ViewHelpers}';
		$fluxTag = 'flux:';
		if ((strpos($file, $fluxNamespace) === FALSE) && (strpos($file, $fluxTag) !== FALSE)) {
			$file = $fluxNamespace . LF . $file;
		}

		// add flux:widget.grid if required
		$previewSection = NULL;
		$previewEndingTagPosition = FALSE;
		$previewOpeningTagPosition = strpos($file, '<f:section name="Preview"');
		if ($previewOpeningTagPosition === FALSE) {
			$previewOpeningTagPosition = strpos($file, "<f:section name='Preview'");
		}
		if ($previewOpeningTagPosition !== FALSE) {
			$previewEndingTagPosition = strpos($file, "</f:section", $previewOpeningTagPosition);
		} elseif (strpos($file, 'flux:flexform.content') !== FALSE && strpos($file, 'flux:widget.grid') === FALSE) {
			$previewSection = "<f:section name=\"Preview\">\n\t<flux:widget.grid />\n</f:section>\n\n";
		} elseif ($previewEndTagPosition === FALSE) {
			$previewSection = '<f:section name="Preview"></f:section>' . LF;
		}


		$splitPoint = FALSE;
		if ($previewSection) {
				// completely new Preview section - add it after the Configuration section
			$configurationSectionOpeningTagPosition = strpos($file, '<f:section name="Configuration"');
			if ($configurationSectionOpeningTagPosition === FALSE) {
				$configurationSectionOpeningTagPosition = strpos($file, "<f:section name='Configuration'");
			}
			$configurationSectionClosingTagPosition = strpos($file, '</f:section', $configurationSectionOpeningTagPosition);
			$splitPoint = $configurationSectionClosingTagPosition ;
		} else if ($previewEndingTagPosition !== FALSE && strpos($file, 'flux:flexform.content') !== FALSE && strpos($file, 'flux:widget.grid') === FALSE) {
				// preview section only needs the grid Widget, set splitPoint and merged "section" markup accordingly
			$splitPoint = $previewEndTagPosition - 12;
			$previewSection = "\t<flux:widget.grid />" . LF;
		}

		if ($previewSection !== NULL && $splitPoint !== FALSE) {
			$before = substr($file, 0, $splitPoint);
			$after = substr($file, $splitPoint);
			$file = $before . $previewSection . $after;
		}

		// display / store changes
		if ($file != $fileBackup) {
			$file = trim($file);
			$doStoreTemplate = FALSE;
			$cli->cli_echo($cli->cli_indent('file modified!' . PHP_EOL, 4), TRUE);

			#if ($this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_DRY || $this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_DRY) {
				$tempFileLeft = PATH_site . 'typo3temp/' . uniqid('fluxmigrateTempFile_');
				$tempFileRight = PATH_site . 'typo3temp/' . uniqid('fluxmigrateTempFile_');
				t3lib_div::writeFile($tempFileLeft, $fileBackup);
				t3lib_div::writeFile($tempFileRight, $file);
				$command = 'diff ' . $tempFileLeft . ' ' . $tempFileRight;
				$output = shell_exec($command);
				unlink($tempFileLeft);
				unlink($tempFileRight);
				$cli->cli_echo($cli->cli_indent($output . LF, 0));
			#}
			if($this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_INTERACTIVE){
				if($cli->cli_keyboardInput_yes('Save modifications')){
					$doStoreTemplate = TRUE;
				}
			}
			if($this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_AUTO || $doStoreTemplate) {
				file_put_contents($fileName, $file);
			}
		}
	}

	/**
	 * do database migration
	 * @return void
	 */
	protected function migrateDatabase() {
		$this->cli->cli_echo(PHP_EOL . 'MIGRATING DATABASE' . PHP_EOL);
		$doDbUpdate = FALSE;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, tx_fed_fcecontentarea', 'tt_content', 'deleted=0 AND tx_fed_fcecontentarea <> "" AND tx_flux_column = ""');
		$numberOfRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if($numberOfRows) {
			$this->cli->cli_echo($numberOfRows . ' tt_content entries need to get updated' . PHP_EOL);
			if($this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_INTERACTIVE) {
				if($this->cli->cli_keyboardInput_yes('Update DB entries')) {
					$doDbUpdate = TRUE;
				}
			}
		} else {
			$this->cli->cli_echo('tt_content entries are up to date' . PHP_EOL);
		}

		if($numberOfRows && ($this->migrationConfiguration[self::CONFIG_MODE] == self::CONFIG_MODE_AUTO || $doDbUpdate)){
			// update DB if needed
			$this->cli->cli_echo('updateing tt_content entries...' . PHP_EOL);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$res2 = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.$row['uid'], array('tx_flux_column' => $row['tx_fed_fcecontentarea']));
			}
			// sanity check
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, tx_fed_fcecontentarea', 'tt_content', 'deleted=0 AND tx_fed_fcecontentarea <> "" AND tx_flux_column = ""');
			$numberOfFailedRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			if($numberOfFailedRows) {
				$this->cli->cli_echo('WARNING! Failed updating ' . $numberOfFailedRows . ' tt_content entries' . PHP_EOL, TRUE);
			} else {
				$this->cli->cli_echo('SUCCESS...' . PHP_EOL);
			}
		}
	}

	/**
	 *
	 */
	protected function clearCaches() {
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4006000) {
		} else {
		}
	}
}

?>
