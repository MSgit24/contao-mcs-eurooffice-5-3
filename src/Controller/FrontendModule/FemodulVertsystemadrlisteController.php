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
use Doctrine\DBAL\Connection;
use Contao\StringUtil;
use Doctrine\DBAL\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;

/*
 * Klasse FemodulVertsystemadrlisteController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/verteilersystem.html
 * Menüpunkt: Eigener Bereich > "Verteilersystem" ("Euro-Office - thematische Verteiler")
 * 
 * - zeigt die erfassten Verteilerempfänger des LK/SK an
 * - mit Sortieren nach Mailadresse | Nachname | Institution | Themen
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_vertsystemadrliste')]
class FemodulVertsystemadrlisteController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulVertsystemadrlisteController ableiten !!!
    public const TYPE = 'femodul_vertsystemadrliste'; // Übersetzung aus/via modules.php geht sonst nicht

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
        // $db = $this->container->get('database_connection');
        $db = Database::getInstance();
        
        // eo: Ref: Company bestimmen
        $member_company = FrontendUser::getInstance()->company;

        /** die Themen incl. Abkürzung etc. holen **/
        // $objEoArr = new EoBeKlasseGetSelects();
        // $themenAlle_arr = $objEoArr->getThemenArray();  // arr[id][title]/[abkrz]/[kuerzel]
        $themenAlle_arr = EoBeKlasseGetSelects::getThemenArray();  // arr[id][title]/[abkrz]/[kuerzel]
        
        // dump($themenAlle_arr);
        
        /** die auf veröffentlicht gesetzten EO-Infos des Zeitraums abrufen **/
        // $database = \Database::getInstance(); 
        // $result = $database->prepare('SELECT * FROM tl_eo_be_vertadr WHERE company = ? AND geloescht != 1 ORDER BY mail')->execute($member_company);
        $result = $db->prepare('SELECT * FROM tl_eo_be_vertadr WHERE company = ? AND geloescht != 1 ORDER BY mail')->execute($member_company);
        
        // while ($result->next()) {
        //     $row = $result->row();
        $vertAdr_arr = [];
        $themenVertAdr_arr = [];
        $VertNamen_arr = [];
        $VertInstitution_arr = [];
        
        while ($result->next()) {
            $row = $result->row();

            /* für Templateausgabe vorbereiten:
               - Zuordnung EO-ID zu Programmen, Schlagworten und Datum im gewählten Zeitraum
               - Bezeichnung der Prg. und Schlw. gleich hier einsetzen
               - Angaben zur jeweiligen EO-Infos */
            $vertAdr_arr[$row['id']]['mail'] = $row['mail'];
            $vertAdr_arr[$row['id']]['name'] = $row['name'];
            $vertAdr_arr[$row['id']]['firstname'] = $row['firstname'];
            $vertAdr_arr[$row['id']]['institution'] = $row['institution'];
            $vertAdr_arr[$row['id']]['comment'] = $row['comment'];
            $vertAdr_arr[$row['id']]['published'] = $row['published'];
            $vertAdr_arr[$row['id']]['published_comment'] = $row['published_comment'];
            
            // $themenVertAdr_tmp = deserialize($row['themen']);
            // $themenVertAdr_tmp = unserialize($row['themen']);
            $themenVertAdr_tmp = StringUtil::deserialize($row['themen']);
            if (is_array($themenVertAdr_tmp)) {
                foreach($themenVertAdr_tmp as $themen_nr) {
                    $vertAdr_arr[$row['id']]['themen'][] = $themen_nr;
                    $themenVertAdr_arr[$themen_nr][] = $row['id'];
                }
            }
            $VertNamen_arr[$row['id']] = $row['name'];
            $VertInstitution_arr[$row['id']] = $row['institution'];
            
        } // Ende while DB-Erg. durchlaufen
        // dump($VertInstitution_arr);
        /* [23] => Array
            (
                [mail] => schepke@eurooffice.de
                [name] => war 20
                [comment] => sdfa
                [themen] => Array
                    (
                        [0] => 3
                        [1] => 8
                    )

            )
            */
            // dump($themenVertAdr_arr);
                /*
                [3] => Array
                (
                    [0] => 23
                )

                [8] => Array
                (
                    [0] => 23
                    [1] => 22
                )

        */

        // natcasesort() sortiert (case insensitive) nach value und ändert Key-Zuordnung nicht
        if (is_array($VertNamen_arr)) {
            natcasesort($VertNamen_arr);
        }
        if (is_array($VertInstitution_arr)) {
            natcasesort($VertInstitution_arr);
        }
        

        $template->themenAlle = $themenAlle_arr;	
        $template->vertAdr = $vertAdr_arr;
        $template->VertNamen = $VertNamen_arr;
        $template->VertInstitution = $VertInstitution_arr;
        $template->themenVertAdr = $themenVertAdr_arr;
        
        return $template->getResponse();
    }
}
