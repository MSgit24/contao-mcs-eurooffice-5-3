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
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/*
 * Klasse FemodulBerichtanzahlvertempfaengerController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/verteilerversand.html
 * Menüpunkt: Euro-Office intern > "Verteilerversand Anz. Empf." ("Anzahl Empfänger entsprechend den Verteilern der LK/SK")
 * 
 * - Anzahl Empfänger aus Verteilersystem heraus pro LK/SK
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_berichtanzahlvertempfaenger')]
class FemodulBerichtanzahlvertempfaengerController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulBerichtanzahlvertempfaengerController ableiten !!!
    public const TYPE = 'femodul_berichtanzahlvertempfaenger'; // Übersetzung aus/via modules.php geht sonst nicht

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
        
        // Initialize Contao classes
        // $database = $framework->getAdapter(Database::class);
        
        $aktJahr = date("Y");
        $vorJahr = $aktJahr - 2; // VertSys startete in 2015
        
        for ($i = $aktJahr; $i >= $vorJahr; $i--) {
            // $database = \Database::getInstance(); 
            $database = Database::getInstance();
            
            $sql = 'SELECT company, SUM( CHAR_LENGTH(mail_adrbcc) - CHAR_LENGTH( REPLACE ( mail_adrbcc, "@", "")) ) AS count FROM tl_eo_be_versand WHERE status = "v" AND mail_art = "p" AND tstamp >= UNIX_TIMESTAMP("'.$i.'-01-01") AND tstamp < UNIX_TIMESTAMP("'. ($i+1) .'-01-01") GROUP BY company ORDER BY company, tstamp';

            // dump($sql);
            $result = $database->query($sql); 
            
            while ($result->next()) {
                $row = $result->row();
                $vertVers_arr[$i][$row['company']] = $row['count'];
            }
        }
    
        
        
        // $this->Template->vertVers = $vertVers_arr;
        $template->vertVers = $vertVers_arr;

        return $template->getResponse();
    }
}
