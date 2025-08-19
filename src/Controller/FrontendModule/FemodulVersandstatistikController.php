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
 * Klasse FemodulVersandstatistikController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/versandstatistik.html
 * Menüpunkt: Eigener Bereich > "Versandstatistik"
 * 
 * - Analysiert Versandstatistiken von Benutzern => den LK/SK
 * - Tabelle mit Datum, Titel, Verteiler, Anz. Empf.
 */
#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_versandstatistik')]
class FemodulVersandstatistikController extends AbstractFrontendModuleController
{

	public const TYPE = 'femodul_versandstatistik'; // Übersetzung aus/via modules.php geht sonst nicht

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
		$member_company = $member->company;

		// echo '<pre>';
		// var_dump($member_company);
		// exit;

		/** die Themen incl. Abkürzung etc. holen **/
		$objEoArr = new EoBeKlasseGetSelects();
		$themenAlle_arr = $objEoArr->getThemenArray(); // arr[id][title]/[abkrz]/[kuerzel]
		
		// echo '<pre>';
		// var_dump($themenAlle_arr);
		// exit;

		/** an "company" des eingelogten Users geschickte Infos einlesen und auch ggf. vorhanden Verteiler-Empf. ermitteln **/
		$database = Database::getInstance(); 
		$result = $database->prepare('SELECT eoid,title,mail_art,vers_zeit,company,mail_adrbcc,vers_themen FROM `tl_eo_be_versand` WHERE company = ? AND mail_art = "p" ORDER BY vers_zeit DESC')->execute($member_company);
		
		// echo '<pre>';
		// var_dump($result);
		// exit;

		while ($result->next()) {
			$row = $result->row();

			// echo '<pre>';
			// var_dump($row);

			$prot_arr['infoliste'][date("Y", $row['vers_zeit'])][date("d.m.Y", $row['vers_zeit'])][$row['eoid']]['title'] = $row['title'];
			
			$vers_themen_tmp = StringUtil::deserialize($result->vers_themen);
			foreach ($vers_themen_tmp as $t_nr) {
				$t_abkrzen[] = $themenAlle_arr[$t_nr]['abkrz'];
			}
			$prot_arr['infoliste'][date("Y", $row['vers_zeit'])][date("d.m.Y", $row['vers_zeit'])][$row['eoid']]['themen'] = implode(", ", $t_abkrzen);
			
			$prot_arr['infoliste'][date("Y", $row['vers_zeit'])][date("d.m.Y", $row['vers_zeit'])][$row['eoid']]['empf_anz'] = substr_count($row['mail_adrbcc'], '@');
			
			$prot_arr['infoliste'][date("Y", $row['vers_zeit'])][date("d.m.Y", $row['vers_zeit'])][$row['eoid']]['empf_adr'] = $row['mail_adrbcc'];
			
			$eoid_alt = $row['eoid'];
			unset($t_abkrzen);
		}

		// exit;


		$result = $database->prepare('SELECT YEAR(FROM_UNIXTIME(vers_zeit)) as jahr, COUNT(id) as anz_infos FROM `tl_eo_be_versand` WHERE mail_art = "p" and company = ? GROUP BY YEAR(FROM_UNIXTIME(vers_zeit))')->execute($member_company);
		while ($result->next()) {
			$row = $result->row();
			$prot_arr['anzahlliste'][$row['jahr']] = $row['anz_infos'];
		}

		// echo '<pre>';
		// var_dump($prot_arr);
		// exit;

		$template->versandProtokoll = $prot_arr;
		
		return $template->getResponse();
		
	}
}
