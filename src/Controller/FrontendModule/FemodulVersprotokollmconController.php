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
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulVersprotokollmconController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/versandprotokoll.html
 * Menüpunkt: Euro-Office intern > "Versandprotokoll" = Versandprotokoll (MCON intern)
 * 
 * - erstellt Versandprotokoll mit Auszählung nach Jahren und Monaten
 * +------------+-------+----------------------------+------+-----------+-------------------+--------------+
 * | Datum      | EO-ID | Titel                      | Abs. | Verteiler | Mitgesch. Anlagen | Empf.        |
 * +------------+-------+----------------------------+------+-----------+-------------------+--------------+
 * | 2025       | 116   | Euro-Office Infos          |      |           |                   |              |
 * | 08/2025    | 8     | Euro-Office Infos          |      |           |                   |              |
 * | 13.08.2025 | 2565  | Internationale Kulturpr... |      |           |                   | LG, WE, ReM  |
 * | 12.08.2025 | 2564  | Förderung Strategischer... | B.R. | Regio     | CA Auf...2025.pdf | LG, WE, ReM  |
 * +------------+-------+----------------------------+------+-----------+-------------------+--------------+
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_versprotokollmcon')]
class FemodulVersprotokollmconController extends AbstractFrontendModuleController
{

	public const TYPE = 'femodul_versprotokollmcon'; // Übersetzung aus/via modules.php geht sonst nicht

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
		$member = FrontendUser::getInstance();
		$name = $member->company;

		// Get the database connection
		$db = $this->container->get('database_connection');
		
		/** die Themen incl. Abkürzung etc. holen **/
		$objEoArr = new EoBeKlasseGetSelects();
		$themenAlle_arr = $objEoArr->getThemenArray(); // arr[id][title]/[abkrz]/[kuerzel]
		$userAlle_arr   = $objEoArr->getUserNameArray(); // arr[id][name]/[mail]
		$prgAlle_arr    = $objEoArr->getEOPrgArray(); // arr[id][title]
		$schlwAlle_arr  = $objEoArr->getEOSchlwArray(); // arr[id][title]
		$empfGrp_arr = array(9 => 'LG', 12 => 'WE', 8 => 'Küste', 10 => 'ReM', 11 => 'Test', 16 => 'GRW');
		
		$database = Database::getInstance(); 
		
		// Umgebungserkennung: Offline vs. Online
		$isOfflineEnvironment = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'eo-intranet') !== false);
		
		if ($isOfflineEnvironment) {
			// Für Offline-Umgebung: MySQL-kompatible Abfrage mit only_full_group_by
			$result = $database->prepare('SELECT eoid, MAX(vers_anlagen) as vers_anlagen FROM tl_eo_be_versand WHERE 1 GROUP BY eoid')->execute();
		} else {
			// Für Online-Umgebung: Original-Abfrage beibehalten
			$result = $database->prepare('SELECT DISTINCT eoid, vers_anlagen FROM tl_eo_be_versand WHERE 1 GROUP BY eoid')->execute();
		}
		
		while ($result->next()) {
			$row = $result->row();
			$arr_files_temp = explode(",", $row['vers_anlagen']);
			foreach ($arr_files_temp as $key => $verz_string) {
				if (strpos(basename($verz_string),'eoinfo_ot')!==false) {
					// "eoinfo_ot" im String enthalten, also die normal mitgeschickte EO-PDF
					// $this->log("$log_bez_eobereich durch $log_username", '', TL_ACCESS);
				} else {
					if (strlen(basename($verz_string)) > 6) {
						$versandanlagen[$row['eoid']][] = str_replace('_', ' ', basename($verz_string));
					}
				}				
			}
		}
		// echo '<pre>';
		// var_dump($versandanlagen);
		// exit;
		
		// $result = $database->prepare('SELECT id, datum, SUBSTRING(title,1,50) AS title, author, programme, schlagworte, vers_themen, vers_empfgrp, published, vers_zeit FROM tl_eo_be_eoinfos WHERE 1 ORDER BY datum DESC')->execute();
		
		$result = $database->prepare('SELECT id, datum, title, author, programme, schlagworte, vers_themen, vers_empfgrp, published, vers_zeit FROM tl_eo_be_eoinfos WHERE 1 ORDER BY  id DESC')->execute();
		
		while ($result->next()) {
			$row = $result->row();
			
			$jahr = date("Y", $row['datum']);
			$monat = date("m", $row['datum']);
			$datum = date("d.m.Y", $row['datum']);
			// $vers_zeit = date("d.m.Y H:i", $row['vers_zeit']);

			// $prot_arr[$jahr][$monat][$datum][$row['id']]['title'] = $row['title'] . "...";
			$prot_arr[$jahr][$monat][$datum][$row['id']]['title'] = $row['title'];
			$cnt_m_arr[$jahr.'-'.$monat][] = 1;
			$cnt_y_arr[$jahr][] = 1;
			
			// $prot_arr[$jahr][$monat][$datum][$row['id']]['author'] = $userAlle_arr[$row['author']][name];
			
			// $initialien_arr = explode(" ", $userAlle_arr[$row['author']]['name']);
            // $prot_arr[$jahr][$monat][$datum][$row['id']]['author'] = substr($initialien_arr[0],0,1) . "." . substr
            // ($initialien_arr[1],0,1) . ".";
			// Prüfen ob Benutzerdaten vorhanden sind
			if (isset($userAlle_arr[$row['author']]['name']) && !empty($userAlle_arr[$row['author']]['name'])) {
				$initialien_arr = explode(" ", $userAlle_arr[$row['author']]['name']);
				if (count($initialien_arr) >= 2) {
					$prot_arr[$jahr][$monat][$datum][$row['id']]['author'] = substr($initialien_arr[0],0,1) . "." . substr($initialien_arr[1],0,1) . ".";
				} else {
					// Fallback: Nur ein Name vorhanden
					$prot_arr[$jahr][$monat][$datum][$row['id']]['author'] = substr($initialien_arr[0],0,1) . ".";
				}
			} else {
				// Fallback: Keine Benutzerdaten vorhanden
				$prot_arr[$jahr][$monat][$datum][$row['id']]['author'] = "N/A";
			}
			
			// $prot_arr[$jahr][$monat][$datum][$row['id']]['title'] = $row['title'];

			$empfgrp = array();
			
			$programme_tmp = StringUtil::deserialize($result->programme);
			if (is_array($programme_tmp)) {
				foreach ($programme_tmp as $key => $p_nr) {
					$prg_liste[] = $prgAlle_arr[$p_nr];
				}
				$prot_arr[$jahr][$monat][$datum][$row['id']]['programme'] = implode(", ", $prg_liste);
				unset($prg_liste);
			} else {
				$prot_arr[$jahr][$monat][$datum][$row['id']]['programme'] = '';
			}
			
			$schlagworte_tmp = StringUtil::deserialize($result->schlagworte);
			if (is_array($schlagworte_tmp)) {
				foreach ($schlagworte_tmp as $key => $s_nr) {
					$schlw_liste[] = $schlwAlle_arr[$s_nr];
				}
				$prot_arr[$jahr][$monat][$datum][$row['id']]['schlagworte'] = implode(", ", $schlw_liste);
				unset($schlw_liste);
			} else {
				$prot_arr[$jahr][$monat][$datum][$row['id']]['schlagworte'] = '';
			}
			$vers_themen_tmp = StringUtil::deserialize($result->vers_themen);
			if (is_array($vers_themen_tmp)) {
				foreach ($vers_themen_tmp as $t_nr) {
					$t_abkrzen[] = $themenAlle_arr[$t_nr]['abkrz'];
				}
				$prot_arr[$jahr][$monat][$datum][$row['id']]['vers_themen'] = implode(", ", $t_abkrzen);
				unset($t_abkrzen);
			}
			$vers_empfgrp_tmp = StringUtil::deserialize($result->vers_empfgrp);
			if (is_array($vers_empfgrp_tmp)) {
				foreach ($vers_empfgrp_tmp as $key => $g_nr) {
					$empfgrp[] = $empfGrp_arr[$g_nr];
				}
				$prot_arr[$jahr][$monat][$datum][$row['id']]['vers_empfgrp'] = implode(", ", $empfgrp);
				unset($empfgrp);
			}
			// var_dump($row['id']);
			
			
			$versendeteAnlagen = "";
			if (is_array($versandanlagen[$row['id']])) {
		
		// echo '<pre>';
		// var_dump($row['id']);
		// var_dump($versandanlagen[$row['id']]);
		
		
				$versendeteAnlagen = implode("<li>",$versandanlagen[$row['id']]);
				
		// echo '<pre>';
		// var_dump($versendeteAnlagen);
		
				$prot_arr[$jahr][$monat][$datum][$row['id']]['versendeteAnlagen'] = '<ul><li>'.$versendeteAnlagen.'</ul>';
				$versendeteAnlagen = "";
			}
			
		// echo '<pre>';
		// var_dump($versandanlagen[$row['id']]);
		// exit;
			
			$prot_arr[$jahr][$monat][$datum][$row['id']]['published'] = $row['published'];
			// $prot_arr[$jahr][$monat][$datum][$row['id']]['vers_zeit'] = $vers_zeit;
			
		}
		$template->CounterM = $cnt_m_arr;
		$template->CounterY = $cnt_y_arr;
		$template->versandProtokollMcon = $prot_arr;
		
		return $template->getResponse();
	}
}
