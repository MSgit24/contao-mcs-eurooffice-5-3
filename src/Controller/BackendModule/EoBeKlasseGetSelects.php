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

// PHP 8.3 Fix: Use statements für Contao-Klassen hinzufügen um "Undefined type" Fehler zu beheben
use Contao\BackendModule;
use Contao\Database;
use Contao\Input;
use Contao\FilesModel;
use Contao\StringUtil;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseEoOperations;

/*
 * Klasse `EoBeKlasseGetSelects` dient als Sammlung von Methoden zur Rückgabe von Datenbankabfragen als Arrays.
 * 
 * - Zweck: Sammlung von Methoden zur Rückgabe von Datenbankabfragen als Arrays
 * - Methoden:
 *   - getEOPrgArray(): Gibt ein Array von Programmnamen basierend auf dem Status zurück
 *   - getEOSchlwArray(): Gibt ein Array von Schlagworten zurück
 * 
 */
 
class EoBeKlasseGetSelects extends BackendModule
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = '';


	/**
	 * Generate the module
	 */
	protected function compile()
	{

	}
	
	//==Programme==============================================================================
	// Var $status mit Vorgabewert, falls Aufruf ohne Parameter
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie bereits statisch aufgerufen wird (::)
	public static function getEOPrgArray($status = "alle") {

		$database = Database::getInstance();
		
		// PHP 8.3 Fix: Array initialisieren um undefined variable warning zu vermeiden
		$EOPrgArray = array();
		
		if ($status == "alle") {
			$result = $database->query('SELECT id, title FROM tl_eo_be_programme WHERE 1 ORDER BY title');
		} elseif ($status == "neu_und_zukunft") {
			$result = $database->query('SELECT id, title FROM tl_eo_be_programme WHERE status != "alt" ORDER BY title');
		} else {
			$result = $database->prepare('SELECT id, title FROM tl_eo_be_programme WHERE status=? ORDER BY title')->execute($status);
		}

		while ($result->next()) {
			$row = $result->row();
			$EOPrgArray[$row['id']] = $row['title'];
		}

		return $EOPrgArray;
	}
	
	//==Schlagworte============================================================================
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie bereits statisch aufgerufen wird (::)
	public static function getEOSchlwArray() {

		$database = Database::getInstance();
		
		// PHP 8.3 Fix: Array initialisieren um undefined variable warning zu vermeiden
		$EOSchlwArray = array();
			
		$result = $database->query('SELECT id, title FROM tl_eo_be_schlagworte WHERE 1 ORDER BY title');

		while ($result->next()) {
				$row = $result->row();
				$EOSchlwArray[$row['id']] = $row['title'];
		}

		return $EOSchlwArray;
	}
	//==Userangaben============================================================================
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie bereits statisch aufgerufen wird (::)
	public static function getUserNameArray() {

		$database = Database::getInstance();
		
		// PHP 8.3 Fix: Array initialisieren um undefined variable warning zu vermeiden
		$UserNameArray = array();
			
		$result = $database->query('SELECT id, name, email FROM tl_user WHERE 1');

		while ($result->next()) {
				$row = $result->row();
				$UserNameArray[$row['id']]['name'] = $row['name'];
				$UserNameArray[$row['id']]['email'] = $row['email'];
		}

		return $UserNameArray;
	}
	
	//==Themen=================================================================================
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie bereits statisch aufgerufen wird (::)
	public static function getThemenArray() {

		$database = Database::getInstance();
		
		// PHP 8.3 Fix: Array initialisieren um undefined variable warning zu vermeiden
		$ThemenArray = array();
			
		$result = $database->query('SELECT * FROM tl_eo_be_themen WHERE 1');

		while ($result->next()) {
				$row = $result->row();
				$ThemenArray[$row['id']]['title'] = $row['title'];
				$ThemenArray[$row['id']]['abkrz'] = $row['abkrz'];
				$ThemenArray[$row['id']]['kuerzel'] = $row['kuerzel'];
		}

		return $ThemenArray;
	}
	
	//==Anlagearten============================================================================
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie bereits statisch aufgerufen wird (::)
	public static function getAnlArtenArray() {

		$database = Database::getInstance();
		
		// PHP 8.3 Fix: Array initialisieren um undefined variable warning zu vermeiden
		$AnlagenArray = array();
			
		$result = $database->query('SELECT * FROM tl_eo_be_anlagearten WHERE 1');

		while ($result->next()) {
				$row = $result->row();
				$AnlagenArray[$row['id']] = $row['title'];
				$AnlagenArray[$row['kuerzel']] = $row['title'];
		}
		return $AnlagenArray;
	}
	
	//==EO-Info Anlagen (der neuen, mit dem CMS angelegten Infos)==============================
	// PHP 8.3 Fix: Methode zu statisch geändert, da sie statisch aufgerufen wird ("Non-static method cannot be called statically" Fehler)
	public static function getEoInfoAnlagen($objEoId) {
    
		// echo '<p>objEoId: ' . $objEoId;
		// exit;

		// ID-Ermittlung
		// if ($objEoId > 0) {
			$id = $objEoId;
		// } else {
		// 	if (Input::get('eoidneu')) {
		// 		$id = Input::get('eoidneu');
		// 	} else {
		// 		$id = Input::get('id');
		// 	}
		// }
		
		$database = Database::getInstance();
		
		// Daten aus DB holen
		$result = $database->prepare('SELECT multiSRC FROM tl_eo_be_eoinfos WHERE id=?')
						   ->execute($id);
		$db_erg = $result->fetchAllAssoc();
		
		// Prüfen ob Ergebnis vorhanden
		if (empty($db_erg)) {
			return array();
		}
		
		$eoinfo_arr = $db_erg[0];
		
		// Dateianlagen ermitteln
		$multiSRC = $eoinfo_arr['multiSRC'];
		// echo '<p>multiSRC: ' . $multiSRC;
		// exit;
		$arrUuids = StringUtil::deserialize($multiSRC, true); // true = immer Array
		// echo '<p>arrUuids: ' . print_r($arrUuids, true);
		// exit;
		$anlagen = array();
		
		if (!empty($arrUuids)) {
			$objFiles = FilesModel::findMultipleByUuids($arrUuids);
			
			if ($objFiles !== null) {
				while ($objFiles->next()) {
					if ($objFiles->type == "file") {
						$anlagen[] = $objFiles->path;
					} else {
						// Für Ordner: Nutze den Pfad um alle Dateien zu finden
						$objSubfiles = FilesModel::findMultipleFilesByFolder($objFiles->path);
						
						if ($objSubfiles !== null) {
							while ($objSubfiles->next()) {
								$anlagen[] = $objSubfiles->path;
							}
						}
					}
				}
			}
		}

		// echo '<pre>anlagen: ' . print_r($anlagen, true);
		// exit;
		
		// Falls keine Anlagen gefunden
		if (empty($anlagen)) {
			$anlagen = array();
			$infoanlagen = array();
		} else {
			// Duplikate entfernen
			$anlagen = array_unique($anlagen);
		}
		
		// Anlagenarten verarbeiten
		$artenArray = self::getAnlArtenArray();
		$infoanlagen = array();
		// echo '<p>artenArray: ' . print_r($artenArray, true);
		// exit;
		foreach ($anlagen as $key => $dateipfad) {
			$dateiname = basename($dateipfad);
			$art_tmp_arr = explode("_", $dateiname);
			$anlage_art = strtolower($art_tmp_arr[0]);
			
			if (strpos($anlage_art, "-") === false) {
				// Nicht nummerierte Anlagen
				if (array_key_exists($anlage_art, $artenArray)) {
					$infoanlagen[$artenArray[$anlage_art]][] = $dateipfad;
				} else {
					$infoanlagen[$artenArray['tx']][] = $dateipfad;
				}
			} else {
				// Nummerierte Anlagen
				$anlage_art_arr = explode("-", $anlage_art);
				$anlage_art = $anlage_art_arr[0];
				
				if (is_numeric($anlage_art_arr[1])) {
					$anlage_nr = $anlage_art_arr[1];
				} else {
					$anlage_nr = 0;
				}
				
				if (array_key_exists($anlage_art, $artenArray)) {
					// Sicherstellen dass kein Index überschrieben wird
					if (isset($infoanlagen[$artenArray[$anlage_art]]) && 
						array_key_exists($anlage_nr, $infoanlagen[$artenArray[$anlage_art]])) {
						$anlage_nr = max(array_keys($infoanlagen[$artenArray[$anlage_art]])) + 1;
					}
					$infoanlagen[$artenArray[$anlage_art]][$anlage_nr] = $dateipfad;
					ksort($infoanlagen[$artenArray[$anlage_art]]);
				} else {
					$infoanlagen[$artenArray['tx']][] = $dateipfad;
				}
			}
		}
		
		// Nach Anlagenarten sortieren
		ksort($infoanlagen);

		// echo '<pre>infoanlagen: ' . print_r($infoanlagen, true);
		// exit; // eo: Debug auskommentiert, damit Backend-Versandauswahl erreicht wird

		/*
			infoanlagen: Array
			(
				[Aufruf] => Array
					(
						[1] => files/EO-Intranet/EO-CMS_Infos/2025/08/05_BMM/CA-1_Initialfoerderung_BMM_04.08.2025.pdf
						[2] => files/EO-Intranet/EO-CMS_Infos/2025/08/05_BMM/CA-2_Breitenfoerderung_BMM_04.08.2025.pdf
					)

				[Euro-Office-Info PDF-Version] => Array
					(
						[0] => files/EO-Intranet/EO-CMS_Infos/2025/08/05_BMM/OT_Euro-Office Info vom 05.08.2025.pdf
					)

				[Richtlinie] => Array
					(
						[0] => files/EO-Intranet/EO-CMS_Infos/2024/05/23_BMM/RL_Richtlinie_vom_10.04.2024.pdf
					)

				[Text] => Array
					(
						[0] => files/EO-Intranet/EO-CMS_Infos/2025/08/05_BMM/TX_Fragen_und_Antworten_BMM_22.07.2025.pdf
					)

			)
			*/

		// Rückgabe je nach Kontext
		// if (Input::get('eoidneu') || Input::get('id')) {
		// 	if (Input::get('eoidneu')) {
		// 		return $infoanlagen;
		// 	} else {


				// Backend-Versandauswahl
				// $objEoBlub = new EoBeKlasseEoOperations();
				// $versandanlagen = array();
				// // exit; // eo: exit auskommentiert - verhinderte die Ausführung der foreach-Schleifen
				// foreach ($infoanlagen as $arten) {
				// 	foreach ($arten as $datei) {
				// 		$datei_standardisiert = $objEoBlub->eostandardize($datei);
						
				// 		if ($datei_standardisiert == $datei) {
				// 			if (stripos($datei, "OT_") === false) {
				// 				// Prüfung auf Whitespace-Zeichen
				// 				if (!preg_match('/\s/', $datei)) {
				// 					$versandanlagen[$datei] = $datei;
				// 				}
				// 			}
				// 		}
				// 	}
				// }
				// echo '<pre>versandanlagen: ' . print_r($versandanlagen, true);
				// // exit;



				return $infoanlagen;
		// 	}
		// } else {
		// 	return $infoanlagen;
		// }
	}	

}
