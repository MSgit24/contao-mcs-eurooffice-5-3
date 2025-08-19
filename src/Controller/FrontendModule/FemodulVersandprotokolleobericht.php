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
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulVersandprotokolleobericht
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/versandbericht.html
 * Menüpunkt: Versandbericht Monate > "EO-Bericht - Versandprotokoll Vorjahr"
 * 
 * - Zeigt Versandprotokoll der EO-Infos nach Monaten des Vorjahres
 * - mit Datum, Titel
 * - zwei Tabellen nach LG, WE
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_versandprotokolleobericht')]
class FemodulVersandprotokolleobericht extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulVersandprotokolleobericht ableiten !!!
    public const TYPE = 'femodul_versandprotokolleobericht'; // Übersetzung aus/via modules.php geht sonst nicht

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
        // (9 => 'LG', 12 => 'WE', 8 => 'Küste', 10 => 'ReM', 11 => 'Test')
		
		// $database = \Database::getInstance(); 
		$database = Database::getInstance();
		$sql = 'SELECT id, datum, title, vers_empfgrp, published, vers_anlagen FROM tl_eo_be_eoinfos WHERE datum >= UNIX_TIMESTAMP("'. (date("Y")-1) .'-01-01") AND datum < UNIX_TIMESTAMP("'. date("Y") .'-01-01") ORDER BY datum ASC'; // AND published = 1
		// $result = $database->query($sql); 
		$result = $database->query($sql);
		
		while ($result->next()) {
			$row = $result->row();
			// dump($row);
			$jahr = date("Y", $row['datum']);
			$monat = date("m", $row['datum']);
			$datum = date("d.m.Y", $row['datum']);
			
			$cnt_m_arr[$jahr.'-'.$monat][] = 1;
			$cnt_y_arr[$jahr][] = 1;
			
			// $vers_empfgrp_tmp = deserialize($result->vers_empfgrp);
			$vers_empfgrp_tmp = StringUtil::deserialize($result->vers_empfgrp);
			if (is_array($vers_empfgrp_tmp)) {
				foreach ($vers_empfgrp_tmp as $key => $g_nr) {
					// Null-Prüfung für vers_anlagen hinzufügen
					$vers_anlagen_count = ($row['vers_anlagen'] !== null) ? substr_count($row['vers_anlagen'], ',') + 1 : 0;
					
					if ($g_nr == 9) {
						$prot_arr['LG'][$monat][$datum][$row['id']]['title'] = $row['title'];
						$prot_cnt['LG'][$monat][$row['id']] = 1;
						$anl_cnt['LG'][$monat][$row['id']] = $vers_anlagen_count;
					}
					if ($g_nr == 12) {
						$prot_arr['WE'][$monat][$datum][$row['id']]['title'] = $row['title'];
						$prot_cnt['WE'][$monat][$row['id']] = 1;
						$anl_cnt['WE'][$monat][$row['id']] = $vers_anlagen_count;
					}
					if ($g_nr == 8) {
						$prot_arr['LG'][$monat][$datum][$row['id']]['title'] = $row['title'];
						$prot_cnt['LG'][$monat][$row['id']] = 1;
						$anl_cnt['LG'][$monat][$row['id']] = $vers_anlagen_count;
						$prot_arr['WE'][$monat][$datum][$row['id']]['title'] = $row['title'];
						$prot_cnt['WE'][$monat][$row['id']] = 1;
						$anl_cnt['WE'][$monat][$row['id']] = $vers_anlagen_count;
					}
				}
			}
			
		}
		$template->versandProtokollMcon = $prot_arr;
		$template->versandProtokollSum = $prot_cnt;
		$template->versandProtokollAnlSum = $anl_cnt;

        return $template->getResponse();
    }
}
