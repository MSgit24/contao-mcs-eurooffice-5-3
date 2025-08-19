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

use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;

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
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulVolltextsucheController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/volltextsuche.html
 * Menüpunkt: Fördermittelsuche > "Volltextsuche"
 * 
 * - Ermöglicht Volltextsuche in EO-Infos basierend auf Benutzerrechten
 * - Berücksichtigt verschiedene Benutzergruppen (LG, WE, Koordinatoren, MCON)
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_volltextsuche')]
class FemodulVolltextsucheController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulVolltextsucheController ableiten !!!
    public const TYPE = 'femodul_volltextsuche'; // Übersetzung aus/via modules.php geht sonst nicht

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
        $objUser = FrontendUser::getInstance();
        
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
        
        
        // if (\Input::post('submit')) {
        if (Input::post('submit')) {
            /***************************************************************/
            // if (\Input::post('suchbegriff')) {
            if (Input::post('suchbegriff')) {
                // $str_SuchFeldinhalt = strtolower(\Input::post('suchbegriff'));
                $str_SuchFeldinhalt = strtolower(Input::post('suchbegriff'));
                
                // Suchfeldinhalt an Template übergeben, damit die Suchwörter zum Ergebnis mit angezeigt werden können
                // $this->Template->SuchFeldinhalt = $str_SuchFeldinhalt;
                $template->SuchFeldinhalt = $str_SuchFeldinhalt;
                
                $str_SuchFeldinhaltBereinigt = trim ( preg_replace('/\s+/', ' ', $str_SuchFeldinhalt) );
                $str_SuchFeldinhaltBereinigt = preg_replace ( '/[^a-z0-9äöüßÄÖÜ ]/i', '', $str_SuchFeldinhaltBereinigt );
                
                $arr_Suchbegriffe = explode(" ", $str_SuchFeldinhaltBereinigt);
                // debug : $this->Template->testvar2 = $arr_Suchbegriffe;
                // debug : $template->testvar2 = $arr_Suchbegriffe;
                
                foreach ($arr_Suchbegriffe as &$str_Suchbegriff) {
                    if (strlen($str_Suchbegriff) > 2) {
                        $str_Suchbegriff = 'infotext LIKE \'%' . $str_Suchbegriff . '%\'';
                    } else {
                        $str_Suchbegriff = '';
                    }
                }
                unset($str_Suchbegriff); // Entferne die Referenz auf das letzte Element
                
                $arr_Suchbegriffe = array_filter($arr_Suchbegriffe);
                
                $str_SqlWhere = implode(" AND ", $arr_Suchbegriffe);
                
                if (strlen($str_SqlWhere) < 3) {
                    $str_SqlWhere = 1;
                }
                
                // $this->Template->testvar3 = $str_SqlWhere;
                $template->testvar3 = $str_SqlWhere;
                
                
            } else {
                // Ausgabe wenn leeres Feld abgeschickt wurde
                $str_SqlWhere = 1;
            }
            /***************************************************************/
            
            /** die Nr. und Bezeichnungen der Prg. und Schlw. holen **/
            $objEoArr = new EoBeKlasseGetSelects();
            $prgBez_arr = $objEoArr->getEOPrgArray();  
            $schlwBez_arr = $objEoArr->getEOSchlwArray(); 
            
            /** die auf veröffentlicht gesetzten EO-Infos des Zeitraums abrufen **/
            $database = Database::getInstance();
            
            $sql = 'SELECT id, title, datum, programme, schlagworte FROM tl_eo_be_eoinfos WHERE '.$str_SqlWhere.' AND ' . $sql_part . ' ORDER BY datum, id ASC';
            
            $result = $database->query($sql);  
            
            while ($result->next()) {
                $row = $result->row();
                /* für Templateausgabe vorbereiten:
                   - Zuordnung EO-ID zu Programmen, Schlagworten und Datum im gewählten Zeitraum
                   - Bezeichnung der Prg. und Schlw. gleich hier einsetzen
                   - Angaben zur jeweiligen EO-Infos */
                   
                $datum_eoid[$row['datum']][] = $row['id'];
                
                $eoinfo[$row['id']]['title'] = $row['title'];
                $eoinfo[$row['id']]['datum'] = $row['datum'];
                
            } // Ende while DB-Erg. durchlaufen
            
            // Array mit Datum-EO-ID Zuordnung sortieren (neue zuoberst)
            if (is_array($datum_eoid)) {
                krsort($datum_eoid);
            }
        }
        
        $template->eoinfos = $eoinfo;
        
        $template->datum_eoid = $datum_eoid;
        
        return $template->getResponse();
    }
}
