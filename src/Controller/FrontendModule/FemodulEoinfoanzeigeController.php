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
use Contao\FilesModel;
use Contao\StringUtil;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulEoinfoanzeigeController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/eo-info-anzeige.html
 * Menüpunkt: Euro-Office Infos > "EO-Info Anzeige"
 * 
 * - Anzeige der ausgewählten EO-Info (Text und Auflistung Anlagen)
 * - PDF-Vorschau generieren
 */


#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_eoinfoanzeige')]
class FemodulEoinfoanzeigeController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulEoinfoanzeigeController ableiten !!!
    public const TYPE = 'femodul_eoinfoanzeige'; // Übersetzung aus/via modules.php geht sonst nicht

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
        // Aufruf aus dem BE heraus ("EO-Infos"): PDF-Vorschau generieren
        // Stand Okt. 2021 : wird nicht mehr verwendet
        if($request->query->get('pdf_pv'))
        {
            // $objEoPdf = new EoFeKlasseEoinfoPdf();
            // if ($request->query->get('pdf_pv') == "mail") {
            //     // Vorschau der Versandversion des PDF
            //     $objEoPdf->generateEoPdf($request->query->get('eoidneu'), 126, 'mail'); // User-ID als 2. Param.
            // } else {
            //     // PDF der EO-Info an sich (zum DL und Hinzufügen zu den Anlagen)
            //     $objEoPdf->generateEoPdf($request->query->get('eoidneu'), 0, 'allg');
            // }
            // generateEoPdf endet mit exit;
        }
        
        $id = $request->query->get('eoidneu');
        // geht auch: $id = \Input::get('eoidneu');

        // Get the database connection
        $db = $this->container->get('database_connection');
        $objEoArr = new EoBeKlasseGetSelects();
        
        $artenArray = $objEoArr->getAnlArtenArray();
        
        // 2 Bereich abzudecken:
        // - alter Bestand an EO-Infos ab ID 8035 = ID der Euro-Office Info vom 06.01.2006
        // - und EO-Infos, die schon mit der CMS-Version des EO-Intranets erstellt wurden
        // Konflikt wird auftreten, wenn über 8.000 EO-Infos mit der CMS-Version erstellt wurden
        // Abhilfe: erst Infos ab 3.1.2007 ausgeben lassen, ID dann 9387
        if ($id >= 8035) { // ID wird weiter unten noch einmal verwendet ...
            $stmt = $db->executeQuery('SELECT title, UNIX_TIMESTAMP(datum) AS tsdatum, verzeichnis, datei_name FROM tl_eoalt_file WHERE fileinhtyp = 25 AND file_id = ?', [$id]);
            $row = $stmt->fetchAssociative();
            $eoinfo_arr['title'] = $row['title'];
            $eoinfo_arr['datum'] = $row['tsdatum'];
            
            $eoinfopfad = TL_ROOT . "/files/EO-Intranet/EO-Infos/".$row['verzeichnis']."/".$row['datei_name'];
            $tmp = file_get_contents($eoinfopfad);
            $infotext = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $tmp);
            // utf8_encode() ist seit PHP 8.2 veraltet - ersetzen durch mb_convert_encoding()
            $eoinfo_arr['infotext'] = mb_convert_encoding($infotext, 'UTF-8', 'ISO-8859-1');
            
            $sql = "SELECT verzeichnis, datei_name, fileinhtyp, DATE_FORMAT(datum,'%d.%m.%Y') AS datum, title FROM tl_eoalt_file_fileanl AS ff, tl_eoalt_file AS f WHERE ff.file_id = ".$id." AND fileanl_id = f.file_id ORDER BY datei_name";
            $stmt = $db->executeQuery($sql);
            while (false !== ($row = $stmt->fetchAssociative())) {
                $datei_pfad = "/EO-Intranet/EO-Infos/".$row['verzeichnis']."/".$row['datei_name'];
                $infoanlagen_alt[$artenArray[$row['fileinhtyp']]][$datei_pfad] = $row['title'];
                $infoanlagen_alt_datum[$datei_pfad] = $row['datum'];
            }
            ksort($infoanlagen_alt);
            
        } else {
            $stmt = $db->executeQuery('SELECT * FROM tl_eo_be_eoinfos WHERE id = ?', [$id]);
            $eoinfo_arr = $stmt->fetchAssociative();
            
            //$objEoArr = new EoBeKlasseGetSelects();
            $infoanlagen = $objEoArr->getEoInfoAnlagen($id); 
            
            
        
        
        // themat. Verteiler sollen mit in den Teaser
        $versandthemen_tmp = StringUtil::deserialize($eoinfo_arr['vers_themen']);

        if (is_array($versandthemen_tmp)) {
            foreach($versandthemen_tmp as $key => $versandthemen_nr) {
                $stmt = $db->executeQuery('SELECT title FROM tl_eo_be_themen WHERE id = ?', [$versandthemen_nr]);
                $row = $stmt->fetchAssociative();
                $arrVersandthemen4teaser[] = $row['title'];		
            }
        }
        $versandthemen4teaser = implode(", ", $arrVersandthemen4teaser);

        $eoinfo_arr['teaser_versandthemen'] = $versandthemen4teaser;
        
        // $vers_themen_tmp = deserialize($eoinfo_arr[vers_themen]);	
        // var_dump($versandthemen4teaser);
        // exit;
            
        }
        
        /**** zugeordnete Programme und Schlagworte ermitteln **/
        
        // nur wenn Besucher zur Gruppe MCON gehört zugeordnete Schlw. & Prg. an Template übergeben
        // $this->import('FrontendUser', 'Member');
        $memberGroups = FrontendUser::getInstance()->groups; 
        // ID der Mitgliedergruppe "Web_MCON" ist "1"
         if (in_array(1, $memberGroups)) {
            /** die Nr. und Bezeichnungen der Prg. und Schlw. holen **/
            // obsolet, da nun auch oben schon nötig : $objEoArr = new EoBeKlasseGetSelects();
            $prgBez_arr = $objEoArr->getEOPrgArray();  
            $schlwBez_arr = $objEoArr->getEOSchlwArray(); 
            
            if ($id >= 8035) {
                $sql = "SELECT schlagwort FROM tl_eoalt_file_schlw WHERE file_id = ".$id;
                $stmt = $db->executeQuery($sql);
                while (false !== ($row = $stmt->fetchAssociative())) {
                    $schlagworte[] = $schlwBez_arr[$row['schlagwort']];
                }
                $sql = "SELECT programm FROM tl_eoalt_file_prg WHERE file_id = ".$id;
                $stmt = $db->executeQuery($sql);
                while (false !== ($row = $stmt->fetchAssociative())) {
                    $programme[] = $prgBez_arr[$row['programm']];
                }
            } else {
                $programme_tmp = StringUtil::deserialize($eoinfo_arr['programme']);
                if (is_array($programme_tmp)) {
                    foreach($programme_tmp as $key => $prg_nr) {
                        $programme[] = $prgBez_arr[$prg_nr];
                    }
                }
                $schlagworte_tmp = StringUtil::deserialize($eoinfo_arr['schlagworte']);
                if (is_array($schlagworte_tmp)) {
                    foreach($schlagworte_tmp as $key => $schlw_nr) {
                        $schlagworte[] = $schlwBez_arr[$schlw_nr];
                    }
                }
            }
            #
            // Zuordnung der Info hinsichtlich Sichtbarkeit mit ausgeben: publ4WE, publ4LG, publ4Koord
            
            if ($eoinfo_arr['publ4WE'] == 1) {
                $str_publ4WE_html = '<p>&#9745;&nbsp;Region Weser-Ems';
            } else {
                $str_publ4WE_html = '<p>&#9744;&nbsp;Region Weser-Ems';			
            }
    
            if ($eoinfo_arr['publ4LG'] == 1) {
                $str_publ4LG_html = '<br>&#9745;&nbsp;Region Lüneburg';
            } else {
                $str_publ4LG_html = '<br>&#9744;&nbsp;Region Lüneburg';			
            }
            
            if ($eoinfo_arr['publ4Koord'] == 1) {
                $str_publ4Koord_html = '<br>&#9745;&nbsp;nur für EO-Koordinatoren</p>';
            } else {
                $str_publ4Koord_html = '<br>&#9744;&nbsp;nur für EO-Koordinatoren</p>';			
            }
            
        }
        
            $stmt = $db->executeQuery('SELECT username FROM tl_user WHERE id = ?', [$eoinfo_arr['author']]);
            $row = $stmt->fetchAssociative();
            $username = $row['username'];
            
            $stmt = $db->executeQuery('SELECT firstname, lastname, phone, email FROM tl_member WHERE username = ?', [$username]);
            $row = $stmt->fetchAssociative();
        
            
            $eoinfo_arr['bearbeiter'] = $row['firstname'] . "&nbsp;" . $row['lastname'];
            $eoinfo_arr['bearbeiter_mail'] = $row['email'];
            $eoinfo_arr['bearbeiter_phone'] = $row['phone'];
            
            // Dummy-Werte wenn ehemaliger MA die EO Info erstellt hat und nicht mehr im System erfasst ist
            if ($eoinfo_arr['bearbeiter_mail'] == '') {
                $eoinfo_arr['bearbeiter'] = 'MCON Team';
                $eoinfo_arr['bearbeiter_mail'] = 'mail@eurooffice.de';
                $eoinfo_arr['bearbeiter_phone'] = '0441/80994-0';
            }




        /**** Teaser-Check und -Verarbeitung ****/
        $teaserVisible = $this->checkTeaserVisibility($eoinfo_arr);

        /**** Text-Verarbeitung - Link-Ersetzung ****/
        $eoinfo_arr['infotext_processed'] = $this->processEoinfoText($eoinfo_arr['infotext'] ?? '');

        /**** Feedback-System für EU-Koordinatoren ****/
        $feedbackData = $this->generateFeedbackData($id);

        /**** Anlagen-Verarbeitung ****/
        $infoanlagen = $this->processAnlagen($infoanlagen ?? []);

        /**** Logging für EO-Info-Zugriff ****/
        $this->logEoinfoAccess($eoinfo_arr['title']);

        /**** Variablen/Arrays an Template übergeben **/

        //$this->Template->eoinfo_arr = $result->fetchAllAssoc();
        $template->eoinfo = $eoinfo_arr; 
        $template->anlagen = $infoanlagen;
        $template->anlagen_alt = $infoanlagen_alt;
        $template->anlagen_alt_datum = $infoanlagen_alt_datum;
        if (is_array($programme)) {
            sort($programme);
        }
        $template->programme = $programme;
        if (is_array($schlagworte)) {
            sort($schlagworte);
        }
        $template->schlagworte = $schlagworte;
        
        $template->str_publ4WE_html = $str_publ4WE_html; 
        $template->str_publ4LG_html = $str_publ4LG_html; 
        $template->str_publ4Koord_html = $str_publ4Koord_html; 
        $template->teaserVisible = $teaserVisible;
        $template->feedbackData = $feedbackData; 

        return $template->getResponse();
    }

    /**
     * Loggt den Zugriff auf eine EO-Info
     * 
     * @param string $eoinfoTitle Titel der EO-Info
     */
    private function logEoinfoAccess(string $eoinfoTitle): void
    {
        // InsertTags für aktuellen Benutzernamen verwenden
        $framework = $this->container->get('contao.framework');
        $controller = $framework->getAdapter(\Contao\Controller::class);
        $log_username = $controller->replaceInsertTags("{{user::username}}");
        
        $log_bez_eobereich = 'Anzeige EO-Info "' . $eoinfoTitle . '"';
        
        // MCON-Zugriffe nicht mit loggen
        if (strpos($log_username, 'MCON-') !== false) {
            // Kommentiert: MCON-Zugriffe werden nicht geloggt
            // System::log("$log_username : $log_bez_eobereich ", __CLASS__.'::'.__FUNCTION__, TL_ACCESS);
        } else {
            // Normale Benutzer-Zugriffe werden geloggt
            System::log("$log_username : $log_bez_eobereich ", __CLASS__.'::'.__FUNCTION__, TL_ACCESS);
        }
    }

    /**
     * Prüft ob der Teaser angezeigt werden soll und gibt die Sichtbarkeit zurück
     * 
     * @param array $eoinfo_arr Array mit EO-Info-Daten
     * @return bool True wenn Teaser sichtbar sein soll
     */
    private function checkTeaserVisibility(array $eoinfo_arr): bool
    {
        // Prüfung aller Teaser-Felder auf Inhalt
        $teasercheckallg = trim($eoinfo_arr['teaser_frist'] ?? '') . 
                          trim($eoinfo_arr['teaser_berechtigt'] ?? '') . 
                          trim($eoinfo_arr['teaser_fmgeber'] ?? '') . 
                          trim($eoinfo_arr['teaser_themen'] ?? '') . 
                          trim($eoinfo_arr['teaser_hinweis'] ?? '') . 
                          trim($eoinfo_arr['teaser_aktualisierung'] ?? '');
        
        return strlen($teasercheckallg) > 0;
    }

    /**
     * Verarbeitet den EO-Info-Text und ersetzt veraltete Links
     * 
     * @param string $infotext Der ursprüngliche Info-Text
     * @return string Der verarbeitete Text mit korrigierten Links
     */
    private function processEoinfoText(string $infotext): string
    {
        // Durch alte parallel laufende Systeme sind Links im Quelltext, die entsprechend geändert werden müssen
        // aus dem Link auf eine EO-Info die ID extrahieren (3ter Suchstring)
        // regex kann ungeändert weiter verwendet werden, wenn Links auf EO-Infos mit '<a href="eoid=123456">EO-Info vom ...</a>' gesetzt werden
        $regex = '#(<a href=")(.[^>]*)id=([0-9]*)">#';
        
		$cmslink = 'https://' . $_SERVER['HTTP_HOST'] . '/eo-cms-info-anzeigen.html';
        
        // Anfang vom <a>-Tag + Link vom CMS + ID der EO-Info:
        $replace = "$1$cmslink?eoidneu=$3\">"; 
        
        return preg_replace($regex, $replace, $infotext, -1);
    }

    /**
     * Generiert die Feedback-Daten für EU-Koordinatoren
     * 
     * @param int|string $eoid Die EO-Info ID
     * @return array|null Feedback-Daten oder null wenn nicht berechtigt
     */
    private function generateFeedbackData($eoid): ?array
    {
        // Prüfung ob User zur Gruppe der EU-Koordinatoren gehört
        // Gruppen: 9=LG, 12=WE [zum Testen: 1=MCON]
        $user = FrontendUser::getInstance();
        if (!($user->isMemberOf(9) || $user->isMemberOf(12))) {
            return null;
        }

        // Quelle der Bewertung [Web / Mail]
        $quelle = 'web';
        
        // User-ID ermitteln - bei Web-/Intranetnutzung
        $empf_id = $user->id;
        
        // Parameter für beide Bewertungsoptionen aufbauen
        // Aufbau: WiFoe-ID - EO-ID - Bewertung - Quelle
        $param_1 = $empf_id . '-' . $eoid . '-1-' . $quelle; // weniger relevant
        $param_2 = $empf_id . '-' . $eoid . '-2-' . $quelle; // ist relevant
        
        $crc_1 = crc32($param_1);
        $crc_2 = crc32($param_2);
        
        $param_final_1 = $param_1 . '-' . $crc_1 . '-dummy';
        $param_final_2 = $param_2 . '-' . $crc_2 . '-dummy';
        
        return [
            'link_weniger_relevant' => 'https://www.eurooffice.de/feedback.html?fb=' . $this->b64link_encode($param_final_1),
            'link_ist_relevant' => 'https://www.eurooffice.de/feedback.html?fb=' . $this->b64link_encode($param_final_2)
        ];
    }

    /**
     * Base64-Kodierung für Feedback-Links
     * 
     * @param string $string String zum kodieren
     * @return string Kodierter String
     */
    private function b64link_encode(string $string): string
    {
        $string = base64_encode($string);
        $string = urlencode($string);
        
        return $string;
    }

    /**
     * Verarbeitet die Anlagen und fügt Metadaten hinzu
     * 
     * @param array $anlagen Array mit Anlagen-Daten
     * @return array Verarbeitete Anlagen mit Metadaten
     */
    private function processAnlagen(array $anlagen): array
    {
        if (empty($anlagen)) {
            return [];
        }

        foreach ($anlagen as $anlagenart => &$datei_arr) {
            foreach ($datei_arr as $nr => &$dateipfad) {
                // Datei-Ausliefern.html des bisherigen EO-Systems ruft sendfiletobrowser auf - weiterverwenden
                // dafür muss "files/" entfernt werden
                $dateilink = str_replace("files/", "", $dateipfad);
                
                // Linktext ist Dateiname ohne Extension und Anlagenartkennzeichen
                $linktext_tmp = basename($dateipfad);
                $linktext_arr = explode("_", $linktext_tmp);
                $linktext = str_replace($linktext_arr[0] . "_", "", $linktext_tmp);
                $linktext = str_replace("_", " ", $linktext);
                
                // Datei-Modell für Metadaten laden
                $objModel = FilesModel::findMultipleByPaths(array($dateipfad));
                $tstamp = $objModel ? $objModel->tstamp : time();
                
                // Strukturierte Daten für Template bereitstellen
                $dateipfad = [
                    'pfad' => $dateipfad,
                    'link' => $dateilink,
                    'text' => $linktext,
                    'datum' => date("d.m.Y", $tstamp)
                ];
            }
        }
        
        return $anlagen;
    }
}
