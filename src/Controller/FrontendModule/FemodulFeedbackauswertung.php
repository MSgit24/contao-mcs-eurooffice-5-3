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
use Contao\Database;
use Contao\Input;

/*
 * Klasse FemodulFeedbackauswertung
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/feedback.html
 * Menüpunkt: Euro-Office intern > "Feedback"
 * 
 * - Analysiert Feedback-Daten von Benutzern
 * - Gibt pro EO-Info die positiven und negativen Feedbacks aus
 */

#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_feedbackauswertung')]
class FemodulFeedbackauswertung extends AbstractFrontendModuleController
{
	// Ref: muss sich aus class-Bezeichnung FemodulFeedbackauswertung ableiten !!!
	public const TYPE = 'femodul_feedbackauswertung'; // Übersetzung aus/via modules.php geht sonst nicht

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

		$aktuellesJahr = date('Y', time());

		$getJahr = Input::get('jahr');

		if (empty($getJahr) or $getJahr > $aktuellesJahr or $getJahr < 2020) {
			$getJahr = date('Y', time());
		}

		$arr_id_final = array();
		$arr_id_angaben = array();
		$arr_fbeoinfos = array();

		$database = Database::getInstance();

		// alle EO-IDs, zu denen eine Feedback abgegeben wurde, durchlaufen ---------------------------------
		$sql = 'SELECT DISTINCT(eo_id), title, FROM_UNIXTIME(eo_tstamp, "%d.%m.%Y") AS eo_datum FROM tl_eo_feedback WHERE fb_src = "mail" AND FROM_UNIXTIME(eo_tstamp, "%Y") = ' . $getJahr . ' ORDER BY eo_id DESC';

		// echo "<pre>";
		// var_dump($sql);
		// echo "</pre>";
		// exit;

		$result_eo_ids = $database->query($sql);

		while ($result_eo_ids->next()) {

			$row_eo_ids = $result_eo_ids->row();

			$lfd_eoinfo_id = $row_eo_ids['eo_id'];

			// gleich das Array mit Datum & Titel der EO-Infos mit aufbauen
			$arr_fbeoinfos[$lfd_eoinfo_id]['eo_titel'] = $row_eo_ids['title'];
			$arr_fbeoinfos[$lfd_eoinfo_id]['eo_datum'] = $row_eo_ids['eo_datum'];

			// PHP 8.3 Fix: Array-Index für aktuelle EO-Info-ID initialisieren
			if (!isset($arr_id_final[$lfd_eoinfo_id])) {
				$arr_id_final[$lfd_eoinfo_id] = array();
			}

			$arr_id_auswahl = array();

			/*
				Die EO-Info-Mails kommen direkt nach dem Versand bei den Empfängern an und werden geprüft. Die Prüfung(en) der in den Mails enthaltenen Links
				lösen somit einen "Feedback-Klick" aus, dies aber unterschiedlich (1-, 2-, 3-, n-fache Auslösung). Da die Wahrscheinlichkeit gering ist, dass
				die Mails innerhalb von 60 Sekunden nach Eingang regelmäßig von mehreren Empfängern gelesen und bewertet wurden, werden Feedbacks innerhalb
				dieser Zeitspanne verworfen.
			*/
			// alle Feedbacks der lfd. EO-Info-ID ermitteln, die später als 60 Sekunden nach Eingang (MIN(fb_tstamp)) abgegeben wurden
			// $sql = 'SELECT id, user_id, fb_tstamp, user_company, fb_wert FROM tl_eo_feedback WHERE eo_id = ' . $lfd_eoinfo_id . ' AND fb_tstamp > ((SELECT MIN(fb_tstamp) FROM tl_eo_feedback WHERE eo_id = ' . $lfd_eoinfo_id . ' AND fb_src = "mail") + 60) AND fb_src = "mail" ORDER BY id ASC';

			// vereinfachte Abfrage, die nur die ersten Feedbacks pro User enthält
			$sql = 'SELECT MIN(id) as id, user_id, fb_tstamp, user_company, fb_wert FROM tl_eo_feedback WHERE eo_id = ' . $lfd_eoinfo_id . ' AND fb_src = "mail" GROUP BY user_id;';

			// echo "<pre>";
			// var_dump($sql);
			// echo "</pre>";
			// exit;

			$result_fb_ids_pro_eoid = $database->query($sql);

			// leere SQL-Ergebnisse abfangen (es liegen immer Feedbacks vor, aber nicht immer nach den ersten 60 Sekunden)
			if ($result_fb_ids_pro_eoid->numRows >= 1) {

				while ($result_fb_ids_pro_eoid->next()) {
					$row = $result_fb_ids_pro_eoid->row();

					// Array mit den FB-IDs der lfd. EO-Info-ID
					$arr_id_auswahl[$row['id']]['user_id'] = $row['user_id'];
					$arr_id_auswahl[$row['id']]['fb_tstamp'] = $row['fb_tstamp'];

					// zu Feedbacks gleich die Angaben mit ablegen
					$arr_id_angaben[$row['id']]['user_company'] = $row['user_company'];
					$arr_id_angaben[$row['id']]['fb_wert'] = $row['fb_wert'];
				}

				if (is_array($arr_id_auswahl)) {
					$comma_separated = implode(",", array_keys($arr_id_auswahl));

					// Umgebungserkennung: Offline vs. Online
					$isOfflineEnvironment = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'eo-intranet') !== false);

					if ($isOfflineEnvironment) {
						// Für Offline-Umgebung: MySQL-kompatible Abfrage mit only_full_group_by
						$sql = 'SELECT MIN(id) AS id, MIN(eo_id) AS eo_id, user_company, MIN(fb_wert) AS fb_wert, MIN(user_id) AS user_id, count(user_company) AS Anzahl FROM tl_eo_feedback WHERE id IN (' . $comma_separated . ') GROUP BY user_company ORDER BY MIN(id) ';
					} else {
						// Für Online-Umgebung: Original-Abfrage beibehalten
						$sql = 'SELECT MIN(id) AS id, eo_id, user_company, fb_wert, user_id, count(user_company) AS Anzahl FROM tl_eo_feedback WHERE id IN (' . $comma_separated . ') GROUP BY user_company ORDER BY MIN(id) ';
					}

					$result_fb_eindeutig = $database->query($sql);

					while ($result_fb_eindeutig->next()) {
						$row_fb_eindeutig = $result_fb_eindeutig->row();


						if ($row_fb_eindeutig['Anzahl'] == 1) {
							// PHP 8.3 Fix: Prüfe ob Array existiert bevor in_array() verwendet wird
							if (! in_array($row_fb_eindeutig['user_id'], $arr_id_final[$lfd_eoinfo_id])) {
								// ins Array der Feedback-IDs aufnehmen
								$arr_id_final[$lfd_eoinfo_id][$row_fb_eindeutig['id']] = $row_fb_eindeutig['user_id'];
							}
							// aus dem Auswahl-Array entfernen (hier sind dann nur noch mehrdeutige - also welche, die ggf. nur Linkprüfung entstanden sind - drin)
							unset($arr_id_auswahl[$row_fb_eindeutig['id']]);
						}
					}

					// das Array mit der ID-Auswahl kann während des Abarbeitens sich nicht selbst ändern (also kein unset von Keys, die entfallen können)
					// deshalb ein skip-Array, in den dynamisch zu ignorierende IDs eingetragen werden
					$skip = array();

					foreach ($arr_id_auswahl as $lfd_id => $arr_dummy) {

						if (array_key_exists($lfd_id, $skip)) {
							// tu nix
						} else {
							// innerhalb von 30 Sekunden aufeinander folgende Feedbacks (IDs) von Usern/LKs sind autom. Linkprüfungen
							$sql = 'SELECT id, user_id, fb_tstamp, user_company, fb_wert FROM tl_eo_feedback WHERE id >= ' . $lfd_id . ' AND eo_id = ' . $lfd_eoinfo_id . ' AND user_id = ' . $arr_id_auswahl[$lfd_id]['user_id'] . ' AND fb_tstamp < (' . $arr_id_auswahl[$lfd_id]['fb_tstamp'] . ' + 30) AND fb_src = "mail" ORDER BY id ASC';

							$result_fb_uneindeutig = $database->query($sql);

							if ($result_fb_uneindeutig->numRows >= 2) {
								// s.o., aufeinander folgende IDs ignorieren
								$skip = array();
								while ($result_fb_uneindeutig->next()) {
									$row_fb_uneindeutig = $result_fb_uneindeutig->row();
									$skip[$row_fb_uneindeutig['id']] = $row_fb_uneindeutig['id'];
								}
							} else {
								// verbleibende IDs sind echte FB-Antworten
								// abgleichen, ob schon eine Antwort vorliegt: wenn mehrfaches Feedback, dann das erste nehmen, also hier dann nicht mehr ins final-Array aufnehmen
								// PHP 8.3 Fix: Prüfe ob Array existiert bevor in_array() verwendet wird
								if (! in_array($arr_id_auswahl[$lfd_id]['user_id'], $arr_id_final[$lfd_eoinfo_id])) {
									$arr_id_final[$lfd_eoinfo_id][$lfd_id] = $arr_id_auswahl[$lfd_id]['user_id'];
								}
							}
						}
					}
				}
			}
		}

		// letzte EO-Infos zuerst anzeigen
		// PHP 8.3 Fix: Nur sortieren wenn Array nicht leer ist
		if (!empty($arr_id_final)) {
			krsort($arr_id_final);
		}
		// dump($arr_id_final);

		// dump($arr_fbeoinfos);

		// dump($arr_id_angaben);

		$template->arr_id_final = $arr_id_final;
		$template->arr_fbeoinfos = $arr_fbeoinfos;
		$template->arr_id_angaben = $arr_id_angaben;

		return $template->getResponse();
	}
}
