<?php

declare(strict_types=1);

/*
 * This file is part of eurooffice.
 *
 * (c) MS 2025 <schepke@mcon-consulting.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/mcs/contao-mcs-eurooffice
 */

namespace Mcs\ContaoMcsEurooffice\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Contao\FrontendUser;
use Contao\Database;
use Contao\Input;
use Contao\System;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulEodboutputController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/eo-db-output.html
 * Menüpunkt: Euro-Office Infos > "EO-DB Output"
 * 
 * - Anzeige von EO-Infos basierend auf Programm- oder Schlagwortauswahl
 * - Auflistung von Fristen und Anlagen
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_eodboutput')]
class FemodulEodboutputController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulEodboutputController ableiten !!!
    public const TYPE = 'femodul_eodboutput'; // Übersetzung aus/via modules.php geht sonst nicht

    protected ?PageModel $page;

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Get the page model
        $this->page = $page;

        $scopeMatcher = $this->container->get('contao.routing.scope_matcher');

        if ($this->page instanceof PageModel && $scopeMatcher->isFrontendRequest($request)) {
            $this->page->loadDetails();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Lazyload services.
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services['contao.framework'] = ContaoFramework::class;
        $services['database_connection'] = Connection::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;

        return $services;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        // Get the database connection
        $db = $this->container->get('database_connection');
        $framework = $this->container->get('contao.framework');
        
        // Initialize Contao classes
        $frontendUser = $framework->getAdapter(FrontendUser::class);
        $input = $framework->getAdapter(Input::class);
        
        // $objUser = $frontendUser::getInstance();
		
		// Regionen und Koordinatoren bei der Auflistung berücksichtigen
		// if ($objUser->isMemberOf(2)) { // Web_EO-LG = 2
		if (FrontendUser::getInstance()->isMemberOf(2)) { // Web_EO-LG = 2
			$sql_part1 = "publ4LG = 1 AND published = 1 ";
		}
		
		// if ($objUser->isMemberOf(3)) { // Web_EO-WE = 3
		if (FrontendUser::getInstance()->isMemberOf(3)) { // Web_EO-WE = 3
			$sql_part1 = "publ4WE = 1 AND published = 1 ";
		}

		// wenn Besucher kein Koordinator ist, muss publ4Koord ungleich 1 sein
		$sql_part2 = "";
		// if (! $objUser->isMemberOf(18)) { // Web_EO-Koordinator = 18
		if (! FrontendUser::getInstance()->isMemberOf(18)) { // Web_EO-Koordinator = 18
			$sql_part2 = "AND publ4Koord != 1 ";
		}
		
		// falls Mcönner eingeloggt: alles überschreiben (kann LG und WE gleichzeitig zugeordnet sein und soll die Infos für Koordinatoren sehen können)
		// if ($objUser->isMemberOf(1)) { // Web_MCON = 1
		if (FrontendUser::getInstance()->isMemberOf(1)) { // Web_MCON = 1
			$sql_part1 = "published = 1 ";
			$sql_part2 = "";
		}

		$sql_part = $sql_part1 . $sql_part2;
		
	 
		$suche = "none";
			
		// $poost = \Input::post('prg_id');
		// echo '<pre>post-Var'."\r\n";
		// var_dump($poost);
		
		// $geet = \Input::get('prg_id');
		// echo '<pre>get-Var'."\r\n";
		// var_dump($geet);
		
		// $subm = \Input::post('submit');
		// echo '<pre>submit-Var'."\r\n";
		// var_dump($subm);
		
		// $schl = \Input::post('schlw_id');
		// echo '<pre>schl-Var'."\r\n";
		// var_dump($schl);
		// echo '</pre>';

		
		// exit;
			
		// in Auflistung der Fristen oder dem DL-Bereich sind die Programme verlinkt: ID kommt per get
		// if ($input::get('prg_id') > 0) {
		if (Input::get('prg_id') > 0) {
			$suche = "prg";
			$prg_id = Input::get('prg_id');
		}
		else // EO-DB Formular wurde benutzt -------------------------------------------
		{
			// Submit-Button zu aktuellen Programmen wurde angeklickt --------
			if (Input::post('submit') == "Infos zum Programm abrufen") {
				if (Input::post('prg_id') > 0) {
					$suche = "prg";
					$prg_id = Input::post('prg_id');
				}
			}
			
			// Submit-Button zu alten Programmen wurde angeklickt ------------
			if (Input::post('submit') == "Infos zu zukünftigem Programm abrufen") {
				if (Input::post('prg_id_zukunft') > 0) {
					$suche = "prg";
					$prg_id = Input::post('prg_id_zukunft');
				}
			}
			
			// Submit-Button zu alten Programmen wurde angeklickt ------------
			if (Input::post('submit') == "Infos zum alten Programm abrufen") {
				if (Input::post('prg_id_alt') > 0) {
					$suche = "prg";
					$prg_id = Input::post('prg_id_alt');
				}
			}
			
			// Schlagwortsuche -----------------------------------------------
			if (Input::post('submit') == "Infos zum Schlagwort abrufen" AND Input::post('schlw_id') > 0) {
				$suche = "schlw";
				$schlw_id = Input::post('schlw_id');
			} 
		}
		
		
		
		if ($suche != "none") {
			//$objEoArr = new EoBeKlasseGetSelects();
			$artenArray = EoBeKlasseGetSelects::getAnlArtenArray(); 
			
			// eo: Programmtitel und Beschreibung abrufen =============================================
			if ($suche == "prg") {
				$stmt = $db->executeQuery('SELECT title, beschr FROM tl_eo_be_programme WHERE id = ?', [$prg_id]);
				$row = $stmt->fetchAssociative();
				$outputTitel = $row['title'];
				$outputBeschr = $row['beschr'];
			}
			if ($suche == "schlw") {
				$stmt = $db->executeQuery('SELECT title FROM tl_eo_be_schlagworte WHERE id = ?', [$schlw_id]);
				$row = $stmt->fetchAssociative();
				$outputTitel = $row['title'];
				$outputBeschr = "";
			}
			// eo: Fristen zum Prg. abrufen
			if ($suche == "prg") {
				$stmt = $db->executeQuery('SELECT id, teaser, startDate FROM `tl_calendar_events` WHERE prgkat = ? AND startDate >= ? ORDER BY startDate ASC', [$prg_id, time()]);
				while (false !== ($row = $stmt->fetchAssociative())) {
					$frist[$row['id']]['startDate'] = $row['startDate'];
					$frist[$row['id']]['teaser'] = $row['teaser'];
				}
			}
			
			// eo: EO-CMS-Infos durchsuchen, die dem Prg. oder Schlw. zugeordnet sind -> IDs sammeln und auflisten
			if ($suche == "prg") {
				$sql = 'SELECT id, title, datum FROM tl_eo_be_eoinfos WHERE programme LIKE \'%"'.$prg_id.'"%\' AND ' . $sql_part . ' ORDER BY id DESC';    
			}
			if ($suche == "schlw") {
				$sql = 'SELECT id, title, datum FROM tl_eo_be_eoinfos WHERE schlagworte LIKE \'%"'.$schlw_id.'"%\' AND ' . $sql_part . ' ORDER BY id DESC';
			}
			
			
			$stmt = $db->executeQuery($sql);
			while (false !== ($row = $stmt->fetchAssociative())) {
				$eoinfos['neu'][$row['id']]['title'] = $row['title'];
				$eoinfos['neu'][$row['id']]['datum'] = date("d.m.Y", $row['datum']);
			}
			
			// eo: alte EO-Infos durchsuchen, die dem Prg. oder Schlw. zugeordnet sind -> auflisten
			if ($suche == "prg") {
				$sql = 'SELECT f.file_id AS id, title, datum FROM `tl_eoalt_file_prg` AS fp, `tl_eoalt_file` AS f WHERE `fileinhtyp` = 25 AND `programm` = '.$prg_id.' AND fp.file_id = f.file_id ORDER BY f.file_id DESC';
			}
			if ($suche == "schlw") {
				$sql = 'SELECT f.file_id AS id, title, datum FROM `tl_eoalt_file_schlw` AS fs, `tl_eoalt_file` AS f WHERE `fileinhtyp` = 25 AND `schlagwort` = '.$schlw_id.' AND fs.file_id = f.file_id ORDER BY f.file_id DESC';
			}
			$stmt = $db->executeQuery($sql);
			while (false !== ($row = $stmt->fetchAssociative())) {
				$eoinfos['alt'][$row['id']]['title'] = $row['title'];
				//$datum_tmp = split("-", $result->datum);
				$datum_tmp = explode("-", $row['datum']);
				$eoinfos['alt'][$row['id']]['datum'] = $datum_tmp[2].".".$datum_tmp[1].".".$datum_tmp[0];
			}
			
			// eo: Anlagen der ("neuen") EO-CMS-Infos zusammenstellen ...==============================
			
			// $objEoArr = new EoBeKlasseGetSelects();
			
			// Abruf der EO-Infos oben mit neu vor alt - hier soll das aktuellste Datum der Anlagen im Vordergrund stehen,
			// deshalb Abruf in umgekehrter Reihenfolge, somit überschreiben identische/doppelt zugeordnete Anlagen neueren Datums die alten ...
			// außerdem: hier nur sortieren/bearbeiten, wenn schon "neue" EO-CMS-Infos vorliegen
			if (!empty($eoinfos['neu'])) {
				// ksort($eoinfos['neu']);
				// dump($eoinfos['neu']);
				// exit;
				foreach($eoinfos['neu'] as $eoid => $foo) {
					$infoanlagen = EoBeKlasseGetSelects::getEoInfoAnlagen($eoid);
				// dump($infoanlagen);
				// exit;
					foreach($infoanlagen as $anlart => $dateipfad_tmp) {
						foreach($dateipfad_tmp as $lfdNr => $dateipfad) {
							$eodateien[$anlart][$dateipfad]['datum'] = $eoinfos['neu'][$eoid]['datum'];
							$bez_tmp = basename($dateipfad);
							$bez_tmp_arr = explode("_",$bez_tmp);
							// $bez = str_replace("_".end($bez_tmp_arr), "", $bez_tmp);
							$bez = str_replace($bez_tmp_arr."_", "", $bez_tmp);
							$eodateien[$anlart][$dateipfad]['bez'] = $bez;
							$dateilink = str_replace("files/", "", $dateipfad);
							// $eodateien[$anlart][$dateipfad]['link'] = 'index.php/datei-ausliefern.html?file='.$dateilink;
							$eodateien[$anlart][$dateipfad]['link'] = '/datei-ausliefern.html?file='.$dateilink;
							// Muster
							//index.php/datei-ausliefern.html?file=EO-Intranet/EO-CMS_Infos/2015/0828_wtt/Beratung%20von%20kleinen%20und%20mittleren%20Unternehmen%20zu%20Wissens-%20und%20Technologietransfer_rl.pdf
						}
					}
				}
				ksort($eodateien);
				//dump($eodateien);
				//exit;
			}

			// ... und die der alten DB zusammenstellen ===============================================
			//ksort($eoinfos['alt']);
			// $eoids = implode(",", array_keys($eoinfos['alt']));
			empty($eoinfos['alt']) ? $eoids = 0 : $eoids = implode(",", array_keys($eoinfos['alt']));
			$sql = "SELECT fileanl_id, verzeichnis, datei_name, fileinhtyp, DATE_FORMAT(datum,'%d.%m.%Y') AS datum, title FROM tl_eoalt_file_fileanl AS ff, tl_eoalt_file AS f WHERE ff.file_id IN (".$eoids.") AND fileanl_id = f.file_id ORDER BY fileanl_id DESC";
			// eo: Ref: query reicht $sql direkt durch, execute nur wenn Parameter eingesetzt werden sollen
			//     bei query somit keine Probleme mit %-Zeichen wie hier bei DATE_FORMAT ...
			$stmt = $db->executeQuery($sql);
			while (false !== ($row = $stmt->fetchAssociative())) {
				$eodateien[$artenArray[$row['fileinhtyp']]][$row['verzeichnis']."/".$row['datei_name']]['datum'] = $row['datum'];
				// die alten Anlagen haben im Titel in Klammern den Namen der Info - rausnehmen
				$pos_klammer = strpos($row['title'], " (");
				if ($pos_klammer > 0) {
					$bez = substr($row['title'],0,$pos_klammer);
					$eodateien[$artenArray[$row['fileinhtyp']]][$row['verzeichnis']."/".$row['datei_name']]['bez'] = $bez;
				} else {
					// Nachträge zu alten Infos sind ohne den Titel in Klammern ...
					$eodateien[$artenArray[$row['fileinhtyp']]][$row['verzeichnis']."/".$row['datei_name']]['bez'] = $row['title'];
				}
				
				// $eodateien[$artenArray[$result->fileinhtyp]][$result->verzeichnis."/".$result->datei_name]['link'] = 'index.php/datei-ausliefern.html?file=EO-Intranet/EO-Infos/'.$result->verzeichnis."/".$result->datei_name;
				$eodateien[$artenArray[$row['fileinhtyp']]][$row['verzeichnis']."/".$row['datei_name']]['link'] = 'datei-ausliefern.html?file=EO-Intranet/EO-Infos/'.$row['verzeichnis']."/".$row['datei_name'];
				// Muster
				//http://intranet.eurooffice.de/index.php/datei-ausliefern.html?file=EO-Intranet/EO-Infos/15/150706-1/150710auftakt_ki-1.pdf
			}
			
			if (empty($eoinfos['alt'])) {
				
			} else {
				// dump($eoinfos['alt']);
				// exit;
				foreach($eoinfos['alt'] as $eoid => $foo) {
					/*foreach($infoanlagen as $anlart => $dateipfad) {
						$eodateien[$anlart][$dateipfad[0]]['datum'] = $eoinfos['neu'][$eoid]['datum'];
						$bez_tmp = basename($dateipfad[0]);
						$bez_tmp_arr = explode("_",$bez_tmp);
						$bez = str_replace("_".end($bez_tmp_arr), "", $bez_tmp);
						$eodateien[$anlart][$dateipfad[0]]['bez'] = $bez;
					}*/
				}
				// eo: Anlagenarten alphabetisch ...
				ksort($eodateien);
				// dump($eodateien);
				// exit;
			}
		} else {
			
			// echo '<pre>keine Werte abgeschickt';
			// exit;
		}
		
		
			// $prg_id = \Input::get('prg_id');
			// $prg_id = \Input::post('prg_id');
			// $prg_id = \Input::post('prg_id_alt');
			// $schlw_id = \Input::post('schlw_id');
		
//$_GET Variable setzen
// \Input::setGet('prg_id', 0);

//$_POST Variable setzen
// \Input::setPost('prg_id', 0);
// \Input::setPost('prg_id_alt', 0);
// \Input::setPost('schlw_id', 0);
		
		// unset($_POST);
		
		//$this->Template->fehlermeldung = $fehlermeldung;
		$template->outputTitel = $outputTitel;
		$template->outputBeschr = $outputBeschr;
		$template->fristen = $frist;
		$template->eoinfos = $eoinfos;
		$template->eodateien = $eodateien;

        return $template->getResponse();
    }
}
