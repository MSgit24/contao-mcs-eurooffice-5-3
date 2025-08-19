<?php

// declare(strict_types=1);

/*
 * This file is part of eurooffice.
 *
 * (c) MS 2025 <schepke@mcon-consulting.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/mcs/contao-mcs-eurooffice
 */

 namespace Mcs\ContaoMcsEurooffice\Controller\BackendModule;

 use Contao\BackendModule;
 use Contao\Database;
 use Contao\Input;
 use Contao\FilesModel;
 use Contao\Controller;
 use Contao\System;

/*
 * Klasse `EoBeKlasseEoOperations` dient als Sammlung von Funktionen, die in BE-Modulen verwendet werden.
 * 
 * - deleteVersandSerie(): Löscht alle Versandserien mit dem Status "w" für eine gegebene EO-ID.
 * - deleteEinzelVersand(): Löscht einen einzelnen Versandeintrag basierend auf der Versand-ID und dem Status "w".
 */

class EoBeKlasseEoOperations extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = '';


	public function __construct() 
    { 
        //parent::__construct(); 
        //$this->import('BackendUser', 'User'); 
		//$this->import('Database');
    }
	
	/**
	 * Generate the module
	 */
	protected function compile()
	{

	}
	
	public function deleteVersandSerie() 
	{
		// einfacher:
		$database = Database::getInstance();
		$database->prepare('DELETE FROM tl_eo_be_versand WHERE eoid=? AND status = "w"')->execute(Input::get('eoid'));
		
		// Zurück zur Übersicht - moderne Contao 5-Redirect-Methode
        // \Contao\Controller::redirect('contao/main.php?do=versand'); // alte Methode auskommentiert
        Controller::redirect(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'versand']));

	} 
	
	public function deleteEinzelVersand() 
	{		
		// einfacher:
		$database = Database::getInstance();
		
		$sql = 'SELECT id, eoid, company FROM tl_eo_be_versand WHERE id = '.Input::get('vers_id').' AND status = "w"';
		// dump($sql);
		$result = $database->prepare($sql)->limit(1)->execute();
		
		$sql = 'DELETE FROM tl_eo_be_versand WHERE eoid = '.$result->eoid.' AND company = "'.$result->company.'" AND status = "w"';
		// dump($sql);
		$database->prepare($sql)->execute();
		$database->prepare('DELETE FROM tl_eo_be_versand WHERE id=? AND status = "w"')->execute(Input::get('vers_id'));
		
		// Zurück zur Übersicht - moderne Contao 5-Redirect-Methode
        // \Contao\Controller::redirect('contao/main.php?do=versand'); // alte Methode auskommentiert
        Controller::redirect(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'versand']));

	}
	
}
