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
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulEodbformular
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/euro-office-datenbank.html
 * Menüpunkt: Fördermittelsuche > "Euro-Office Programme & Schlagworte"
 * 
 * - nach Prg. & Schlw filtern
 */


#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_eodbformular')]
class FemodulEodbformular extends AbstractFrontendModuleController
{
    public const TYPE = 'femodul_eodbformular';

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

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        // Select-Optionen laden
        $prgAktuell = EoBeKlasseGetSelects::getEOPrgArray("aktuell");
        $prgZukunft = EoBeKlasseGetSelects::getEOPrgArray("zukunft");  
        $prgAlt = EoBeKlasseGetSelects::getEOPrgArray("alt");
        $schlagworte = EoBeKlasseGetSelects::getEOSchlwArray();

        // Template-Variablen setzen
        $template->prgAktuell = $prgAktuell;
        $template->prgZukunft = $prgZukunft;
        $template->prgAlt = $prgAlt;
        $template->schlagworte = $schlagworte;

        $template->requestToken = $this->container->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return $template->getResponse();
    }
}