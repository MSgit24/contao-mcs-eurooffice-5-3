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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulBerichtallgController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/bericht-allg.html
 * Menüpunkt: Euro-Office intern > "Bericht allg."
 * 
 * - Zählt Schlagwörter, Programme, EO-Infos, Dateien im Bereich Strukturfonds
 * - Zählt EO-Infos nach Jahr
 * - Zählt Dateien nach Jahr und Extension
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_berichtallg')]
class FemodulBerichtallgController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulBerichtallgController ableiten !!!
    public const TYPE = 'femodul_berichtallg'; // Übersetzung aus/via modules.php geht sonst nicht

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
        
        // Anzahl Programme abfragen ----------------------------------------------------
        $stmt = $db->executeQuery('SELECT status, count(id) AS anzahl FROM tl_eo_be_programme GROUP BY status');
        $PrgAnz_arr = [];
        
        while (false !== ($row = $stmt->fetchAssociative())) {
            $PrgAnz_arr[$row['status']] = $row['anzahl'];
        }
        $template->PrgAnz = $PrgAnz_arr;
        
        // Anzahl Schlagworte abfragen ============================================================
        $stmt = $db->executeQuery('SELECT count(id) AS anzahl FROM tl_eo_be_schlagworte');
        $SchlwAnz = 0;
        
        while (false !== ($row = $stmt->fetchAssociative())) {
            $SchlwAnz = $row['anzahl'];
        }
        $template->SchlwAnz = $SchlwAnz;
        
        // Anzahl zu EO-Infos gehörende Dateien abfragen ==========================================
        $aktJahr = date("Y");
        $vorJahr = $aktJahr - 1;
        $eoDateien_arr = [];
        
        for ($i = $aktJahr; $i >= $vorJahr; $i--) {
            // EO-Infos waren früher einzelne HTML-Dateien, zur bessere Vgl.barkeit als solche hinzunehmen
            $sql = 'SELECT count(id) AS anzahl FROM tl_eo_be_eoinfos WHERE published = 1 AND datum >= UNIX_TIMESTAMP("'.$i.'-01-01") AND datum < UNIX_TIMESTAMP("'.($i+1).'-01-01")';
            $stmt = $db->executeQuery($sql);
            
            while (false !== ($row = $stmt->fetchAssociative())) {
                $eoDateien_arr[$i]['html*'] = $row['anzahl'];
            }
            
            $sql = 'SELECT extension, count(type) AS anzahl FROM tl_files WHERE type = "file" AND path LIKE "files/EO-Intranet/EO-CMS_Infos%" AND tstamp >= UNIX_TIMESTAMP("'.$i.'-01-01") AND tstamp < UNIX_TIMESTAMP("'.($i+1).'-01-01") GROUP BY extension';
            $stmt = $db->executeQuery($sql);
            
            while (false !== ($row = $stmt->fetchAssociative())) {
                $eoDateien_arr[$i][$row['extension']] = $row['anzahl'];
            }
        }
        $template->eoDateien = $eoDateien_arr;
        
        // Anzahl Dateien im Bereich Strukturfonds ================================================
        $sfDateien_arr = [];
        $sql = 'SELECT extension, count(type) AS anzahl FROM tl_files WHERE type = "file" AND path LIKE "files/EO-Intranet/Strukturfonds/Strukturfonds_%" GROUP BY extension';
        $stmt = $db->executeQuery($sql);
        
        while (false !== ($row = $stmt->fetchAssociative())) {
            $sfDateien_arr[$row['extension']] = $row['anzahl'];
        }
        
        $template->sfDateien = $sfDateien_arr;
        
        // Anzahl Dateien im Bereich Corona-Hilfen ================================================
        $coronaDateien_arr = [];
        $sql = 'SELECT extension, count(type) AS anzahl FROM tl_files WHERE type = "file" AND path LIKE "files/EO-Intranet/Corona-Hilfen/%" GROUP BY extension';
        $stmt = $db->executeQuery($sql);
        
        while (false !== ($row = $stmt->fetchAssociative())) {
            $coronaDateien_arr[$row['extension']] = $row['anzahl'];
        }
        
        $template->coronaDateien = $coronaDateien_arr;

        return $template->getResponse();
    }
}
