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
use Contao\Database;
use Contao\StringUtil;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/*
 * Klasse FemodulBerichttabellenlkskverteilerController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/verteilertabellen.html
 * Menüpunkt: Euro-Office intern > "Verteilertabellen LK/SK" ("Verteiler der LK/SK")
 * 
 * - Tabellen der Verteiler pro LK/SK tagesaktuell
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_berichttabellenlkskverteiler')]
class FemodulBerichttabellenlkskverteilerController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulBerichttabellenlkskverteilerController ableiten !!!
    public const TYPE = 'femodul_berichttabellenlkskverteiler'; // Übersetzung aus/via modules.php geht sonst nicht

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

        return $services;
    }

    // protected function compile()
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        // Get the database connection
        // $db = $this->container->get('database_connection');
        // $framework = $this->container->get('contao.framework');
        
        // eo: Ref: Company bestimmen
        //$this->import('FrontendUser', 'Member');
        //$member_company = $this->Member->company;

        /** die Themen incl. Abkürzung etc. holen **/
        // $objEoArr = new EoBeKlasseGetSelects();
        $themenAlle_arr = EoBeKlasseGetSelects::getThemenArray();  // arr[id][title]=.../[abkrz]=.../[kuerzel]=...

        // sortieren nötig geworden; die neuen Verteiler, wie bspw. Corona, sind sonst am Ende der Liste ...
        asort($themenAlle_arr);
        
        /** die Verteileradressen und Zuordnungen abrufen **/
        // $database = \Database::getInstance(); 
        $database = Database::getInstance();
        $result = $database->prepare('SELECT * FROM tl_eo_be_vertadr WHERE geloescht != 1 ORDER BY company, mail')->execute();
        
        while ($result->next()) {
            $row = $result->row();
            // dump($row);
            $themenVertAdr_tmp = StringUtil::deserialize($row['themen']);
            if (is_array($themenVertAdr_tmp)) {
                foreach($themenAlle_arr as $key => $themen_blub) {
                    if (in_array($key, $themenVertAdr_tmp)) {
                        $vertAdr_arr[$row['company']][$row['mail']]['themen'][$key] = "x";
                    } else {
                        $vertAdr_arr[$row['company']][$row['mail']]['themen'][$key] = " ";
                    }
                }
                $vertAdr_arr[$row['company']][$row['mail']]['kommentar'] = $row['comment'];
                $vertAdr_arr[$row['company']][$row['mail']]['name'] = $row['name'];
                $vertAdr_arr[$row['company']][$row['mail']]['firstname'] = $row['firstname'];
                $vertAdr_arr[$row['company']][$row['mail']]['institution'] = $row['institution'];
            }
        }
        // dump($vertAdr_arr);
        
        // $this->Template->themenAlle = $themenAlle_arr;	
        // $this->Template->vertAdr = $vertAdr_arr;
        $template->themenAlle = $themenAlle_arr;
        $template->vertAdr = $vertAdr_arr;
        // $template->VertNamen = $VertNamen_arr;
        // $template->themenVertAdr = $themenVertAdr_arr;

        return $template->getResponse();
    }
}
