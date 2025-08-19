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
use Contao\CoreBundle\InsertTag\InsertTagParser; // neu in Contao 5.3
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Contao\FrontendUser;
use Contao\Database;
use Contao\Input;
use Contao\StringUtil;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/*
 * Klasse FemodulAuflistungaktualisiertereoinfosController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/aktualisierungen.html
 * Menüpunkt: Euro-Office Infos > "Aktualisierungen" ("Aktualisierte Euro-Office Infos")
 * 
 * - Auflistung von aktualisierten EO-Infos
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_auflistungaktualisiertereoinfos')]
class FemodulAuflistungaktualisiertereoinfosController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulAuflistungaktualisiertereoinfosController ableiten !!!
    public const TYPE = 'femodul_auflistungaktualisiertereoinfos'; // Übersetzung aus/via modules.php geht sonst nicht

    protected ?PageModel $page;

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, ?array $classes = null, ?PageModel $page = null): Response
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
        $services['contao.insert_tag.parser'] = InsertTagParser::class; // neu in Contao 5.3

        return $services;
    }

    // protected function compile()
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        // Get the database connection
        // $db = $this->container->get('database_connection');
        $framework = $this->container->get('contao.framework');
        $insertTagParser = $this->container->get('contao.insert_tag.parser'); // neu in Contao 5.3
        
        // Initialize Contao classes
        $frontendUser = $framework->getAdapter(FrontendUser::class);
        $input = $framework->getAdapter(Input::class);
        $database = $framework->getAdapter(Database::class);
        
        // $objUser = \FrontendUser::getInstance();
        $objUser = FrontendUser::getInstance();
		
		$sql_part1 = "published = 1 ";
		
		// Regionen und Koordinatoren bei der Auflistung berücksichtigen
		if ($objUser->isMemberOf(2)) { // Web_EO-LG = 2
			$sql_part1 = "publ4LG = 1 AND published = 1 ";
		}
		
		if ($objUser->isMemberOf(3)) { // Web_EO-WE = 3
			$sql_part1 = "publ4WE = 1 AND published = 1 ";
		}

		// wenn Besucher kein Koordinator ist, muss publ4Koord ungleich 1 sein
		$sql_part2 = "";
		if (! $objUser->isMemberOf(18)) { // Web_EO-Koordinator = 18
			$sql_part2 = "AND publ4Koord != 1 ";
		}
		
		// falls Mcönner eingeloggt: alles überschreiben (kann LG und WE gleichzeitig zugeordnet sein und soll die Infos für Koordinatoren sehen können)
		if ($objUser->isMemberOf(1)) { // Web_MCON = 1
			$sql_part1 = "published = 1 ";
			$sql_part2 = "";
		}

		$sql_part = $sql_part1 . $sql_part2;
	
        /** Zeitraum für Abfrage festlegen
            - liefert generell Zeitraum, ob erster Aufruf (ohne Zeitraum) oder per Formular **/
        // if (\Input::post('end_datum')) {
        if (Input::post('end_datum')) {
            // $end_datum_human = \Input::post('end_datum');
            $end_datum_human = Input::post('end_datum');
			/* "Note: Be aware of dates in the m/d/y or d-m-y formats; if the separator is a slash (/), then the American m/d/y is assumed. If the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed." */
			$zeitraum_tstamp[1] = strtotime($end_datum_human);
			// ungültige Eingaben ergeben 1.1.1970 -> auf akt. Datum setzten
			if (date("d.m.Y", $zeitraum_tstamp[1]) == "01.01.1970") {
				$zeitraum_tstamp[1] = time();
			}
		} else {
			$zeitraum_tstamp[1] = time();
		}
		// eo: ToDo: Eingabe offset-Wert über Modulmaske ermöglichen
		$offset_monate = 60; // orig 12;
		$offset = $offset_monate * 30 * 24 * 60 * 60;
		
        // if (\Input::post('start_datum')) {
        if (Input::post('start_datum')) {
            // $start_datum_human = \Input::post('start_datum');
            $start_datum_human = Input::post('start_datum');
			$zeitraum_tstamp[0] = strtotime($start_datum_human);
			// ungültige Eingaben (s.o.) -> auf Offset-Datum setzten
			if (date("d.m.Y", $zeitraum_tstamp[0]) == "01.01.1970") {
				$zeitraum_tstamp[0] = time() - $offset;
			}
		} else {
			// wenn keine Eingabe, dann Start- = Enddatum
			$zeitraum_tstamp[0] = $zeitraum_tstamp[1] - $offset;
		}
		// sicherheitshalber sortieren, falls in falscher Reihenfolge eingegeben
		// array_multisort() sortiert nach value und passt key entspr. an
		array_multisort($zeitraum_tstamp);

        /** die Nr. und Bezeichnungen der Prg. und Schlw. holen **/
        // $objEoArr = new EoBeKlasseGetSelects();
        // $prgBez_arr = $objEoArr->getEOPrgArray();  
        // $schlwBez_arr = $objEoArr->getEOSchlwArray(); 
        $prgBez_arr = EoBeKlasseGetSelects::getEOPrgArray();
        $schlwBez_arr = EoBeKlasseGetSelects::getEOSchlwArray(); 
		
        /** die auf veröffentlicht gesetzten EO-Infos des Zeitraums abrufen **/
        // $database = \Database::getInstance(); 
        $database = Database::getInstance(); 
		
		// ursprüngliche Abfrage ohne Berücksichtigung der Region
		// $result = $database->prepare('SELECT id, title, datum, programme, schlagworte FROM tl_eo_be_eoinfos WHERE published = 1 AND (datum >= ? AND datum <= ?) ORDER BY datum, id ASC')->execute($zeitraum_tstamp[0], $zeitraum_tstamp[1]);
		
		$sql = 'SELECT id, title, teaser_aktualdatum, datum, programme, schlagworte FROM tl_eo_be_eoinfos WHERE ' . $sql_part . ' AND teaser_aktualisierung != "" AND teaser_aktualdatum > 0 AND (datum >= '.$zeitraum_tstamp[0].' AND datum <= '.$zeitraum_tstamp[1].') ORDER BY teaser_aktualdatum DESC';
		
		//dump($sql);
		//exit;
		
        // $result = $this->Database->prepare($sql)->execute(); 
        $result = $database->prepare($sql)->execute(); 
		
		//dump($result);
		//exit;
		
		while ($result->next()) {
			$row = $result->row();
			/* für Templateausgabe vorbereiten:
			   - Zuordnung EO-ID zu Programmen, Schlagworten und Datum im gewählten Zeitraum
			   - Bezeichnung der Prg. und Schlw. gleich hier einsetzen
			   - Angaben zur jeweiligen EO-Infos */
			   
            // $programme_tmp = deserialize($row['programme']);
            $programme_tmp = StringUtil::deserialize($row['programme']);
			if (is_array($programme_tmp)) {
				foreach($programme_tmp as $key => $prg_nr) {
					// Prüfung ob Programm-ID im Array existiert, um "Undefined array key" Fehler zu vermeiden
					if (array_key_exists($prg_nr, $prgBez_arr)) {
						$programme_eoid[$prgBez_arr[$prg_nr]][] = $row['id'];
					}
				}
			}
			
            // $schlagworte_tmp = deserialize($row['schlagworte']);
            $schlagworte_tmp = StringUtil::deserialize($row['schlagworte']);
			if (is_array($schlagworte_tmp)) {
				foreach($schlagworte_tmp as $key => $schlw_nr) {
					// Prüfung ob Schlagwort-ID im Array existiert, um "Undefined array key" Fehler zu vermeiden
					if (array_key_exists($schlw_nr, $schlwBez_arr)) {
						$schlagworte_eoid[$schlwBez_arr[$schlw_nr]][] = $row['id'];
					}
				}
			}
			$datum_eoid[$row['datum']][] = $row['id'];
			
			$eoinfo[$row['id']]['title'] = $row['title'];
			$eoinfo[$row['id']]['datum'] = $row['datum'];
			$eoinfo[$row['id']]['aktualdatum'] = $row['teaser_aktualdatum'];
			
		} // Ende while DB-Erg. durchlaufen
		
		
		// nur wenn Besucher zur Gruppe MCON gehört zugeordnete Schlw. & Prg. an Template übergeben
		/* $this->import('FrontendUser', 'Member');
		$memberGroups = $this->Member->groups; 
		// ID der Mitgliedergruppe "Web_MCON" ist "1"
		 if (in_array(1, $memberGroups)) {
		 } */
		// eo: Testbereich, nur durchlaufen, wenn MS angemeldet
		// $this->import("FrontendUser","User");
		// eo: ToDo: if-Abfrage auf Datum vor der Umstellung (4.9.2015) einstellen
		// if ($this->User->id == 126) {
		if ($zeitraum_tstamp[0] <= 1441620000) {
			$dbdatum_start = date("Y-m-d", $zeitraum_tstamp[0]);
			$dbdatum_end =  date("Y-m-d", $zeitraum_tstamp[1]);
			$result_dbalt = $database->prepare('SELECT file_id, title, UNIX_TIMESTAMP(datum) AS tsdatum FROM tl_eoalt_file WHERE fileinhtyp = 25 AND (datum >= ? AND datum <= ?) ORDER BY datum DESC')->execute($dbdatum_start, $dbdatum_end);

			while ($result_dbalt->next()) {
				$row_dbalt = $result_dbalt->row();
				$datum_eoid[$row_dbalt['tsdatum']][] = $row_dbalt['file_id'];
				$eoinfo[$row_dbalt['file_id']]['title'] = $row_dbalt['title'];
				$eoinfo[$row_dbalt['file_id']]['datum'] = $row_dbalt['tsdatum'];
				
				$result_dbalt_prg = $database->prepare('SELECT programm FROM tl_eoalt_file_prg WHERE file_id = ?')->execute($row_dbalt['file_id']);
				while ($result_dbalt_prg->next()) {
					$row_dbalt_prg = $result_dbalt_prg->row();
					// Prüfung ob Programm-ID im Array existiert, um "Undefined array key" Fehler zu vermeiden
					if (array_key_exists($row_dbalt_prg['programm'], $prgBez_arr)) {
						$programme_eoid[$prgBez_arr[$row_dbalt_prg['programm']]][] = $row_dbalt['file_id'];
					}
				}
				
				// var_dump($programme_eoid);
				// exit;
				 
				$result_dbalt_schlw = $database->prepare('SELECT schlagwort FROM tl_eoalt_file_schlw WHERE file_id = ?')->execute($row_dbalt['file_id']);
				while ($result_dbalt_schlw->next()) {
					$row_dbalt_schlw = $result_dbalt_schlw->row();
					// Prüfung ob Schlagwort-ID im Array existiert, um "Undefined array key" Fehler zu vermeiden
					if (array_key_exists($row_dbalt_schlw['schlagwort'], $schlwBez_arr)) {
						$schlagworte_eoid[$schlwBez_arr[$row_dbalt_schlw['schlagwort']]][] = $row_dbalt['file_id'];
					}
				}
			}
			
		}
		
		// Array mit Datum der Aktualisierung sortieren (neue zuoberst)
		//if (is_array($teaser_aktualdatum)) {
		//	krsort($teaser_aktualdatum);
		//}

		//ksort($programme_eoid);
		//ksort($schlagworte_eoid);
		// echo "<pre>";
		// var_dump($programme_eoid);
		// exit;
		
        // $this->Template->eoinfos = $eoinfo;
        // $this->Template->zeitraum = $zeitraum_tstamp;
        $template->eoinfos = $eoinfo;
        $template->zeitraum = $zeitraum_tstamp;
        $template->insertTagParser = $insertTagParser; // neu in Contao 5.3
        $template->frontendUser = $objUser; // NEU in Contao 5.3
        
        //$template->programme_eoid = $programme_eoid;
        //$template->schlagworte_eoid = $schlagworte_eoid;
        //$template->datum_eoid = $datum_eoid;

        return $template->getResponse();
    }
}
