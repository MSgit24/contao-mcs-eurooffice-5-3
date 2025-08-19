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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/*
 * Klasse FemodulKundenmemberlisteController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/euro-office-nutzer.html
 * Menüpunkt: Eigener Bereich > "Euro-Office-Nutzer" ("Übersicht Euro-Office-Nutzer")
 * 
 * - zeigt die EO-Nutzer mit Login (Contao-Member) des LK/SK an
 * - mit Sortieren nach Nachname mit letztem Login
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_kundenmemberliste')]
class FemodulKundenmemberlisteController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulKundenmemberlisteController ableiten !!!
    public const TYPE = 'femodul_kundenmemberliste'; // Übersetzung aus/via modules.php geht sonst nicht

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
        // $this->import('FrontendUser', 'Member');
        // $memberCompany = $this->Member->company;
        $memberCompany = FrontendUser::getInstance()->company;
        
        // $this->Template->memberCompany = $memberCompany;
        
        // Mitglieder des LK/SK abfragen ----------------------------------------------------
        
        // $database = \Database::getInstance(); 
        $db = Database::getInstance();
        
        // $result = $database->prepare('SELECT firstname, lastname, lastLogin FROM tl_member WHERE company = "'.$memberCompany.'" AND disable != 1 ORDER BY lastname ASC')->execute();
        $result = $db->prepare('SELECT firstname, lastname, lastLogin FROM tl_member WHERE company = "'.$memberCompany.'" AND disable != 1 ORDER BY lastname ASC')->execute();
        
        $html_tab = '<table><tr><td><b>Name</b></td><td><b>letzter Login</b></td></tr>';
        
        while ($result->next()) {
            $row = $result->row();
            // $LKSKuser_arr[$row['lastname']]['name'] = $row['lastname'] . ", " . $row['firstname'];
            // $LKSKuser_arr[$row['lastname']]['lastlogin'] = date('d.m.Y', $row['lastLogin']);
            $row['lastLogin'] == 0 ? $letzesLogin = 'noch nie' : $letzesLogin = date('d.m.Y', $row['lastLogin']);  // $var == Bedingung ? $wahr=... : $falsch=...;
            $html_tab .= '<tr><td>'.$row['lastname'] . ", " . $row['firstname'].'</td><td>'.$letzesLogin.'</td></tr>';
        }
        
        $html_tab .= '</table>';
        
        // $this->Template->html_tab = $html_tab;
        $template->html_tab = $html_tab;
        
        return $template->getResponse();
    }
}
