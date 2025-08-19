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
use Contao\Input;
use Contao\System;
use Contao\Email;
use Contao\BackendTemplate;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Klasse FemodulEoinfoanzeigeController
 * 
 * Seite im EO-Intranet: https://intranet.eurooffice.de/eo-info-versand.html?ph=1&eoidneu=2565
 * Menüpunkt: Euro-Office Info unten > Anlagen versenden > "Anlagen der Euro-Office Info versenden"
 * 
 * - Anlagen der ausgewählten EO-Info selbst über online Formular versenden
 */


#[AsFrontendModule(category: 'eurooffice_neu', template: 'templ_anlagenpereoinfoversenden')]
class FemodulAnlagenpereoinfoversendenController extends AbstractFrontendModuleController
{
    // Ref: muss sich aus class-Bezeichnung FemodulAnlagenpereoinfoversendenController ableiten !!!
    public const TYPE = 'femodul_anlagenpereoinfoversenden'; // Übersetzung aus/via modules.php geht sonst nicht

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


	/**
	 * Generate the module
	 *   FE: Formular zum Versenden von Anlagen einer EO-Info & nach submit des Formulars Versand einer Mail
	 */
	protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
	{
		// Link aus EO-Info Anzeige heraus
		// if ($request->query->get('eoidneu')) {
		if (Input::get('eoidneu')) {
			$phase = Input::get('ph');
			$id = Input::get('eoidneu');
		}
		// Werte aus Versandformular
		if (Input::post('ph')) {
			$phase = Input::post('ph');
			$id = Input::post('eoidneu');
		}
		
		// Get the database connection
		$db = $this->container->get('database_connection');
		
		// $artenArray = $objEoArr->getAnlArtenArray();
		
		// phase 1 = Formular / 2 = Mail verschicken
		if ($phase == "1") { 
			$stmt = $db->executeQuery('SELECT * FROM tl_eo_be_eoinfos WHERE id=?', [$id]);
			$eoinfo_arr = $stmt->fetchAssociative();
			$infoanlagen = EoBeKlasseGetSelects::getEoInfoAnlagen($id);
			
		}
		
		// phase 1 = Formular / 2 = Mail verschicken
		if ($phase == 2) { 
		
			// $this->import('FrontendUser', 'Member');
            $user = FrontendUser::getInstance();
            if (FE_USER_LOGGED_IN)
            {
               $ccMail = $user->email;
            }
		
			// $AbsenderMailAdresse = $this->Input->post('AbsenderMailAdresse');
			$AbsenderMailAdresse = "verteiler@eurooffice.de";
			$AbsenderMailName = Input::post('AbsenderMailName'); 		// Name eingelogtes Mitglied
			$AntwortMailAdresse = Input::post('AntwortMailAdresse');		// für ReplyTo
			$EmpfaengerMailAdresse = Input::post('EmpfaengerMailAdresse');
			$Betreff = html_entity_decode(Input::post('Betreff'));
			
			$Mailtext = '<div style="font: 12px arial, sans-serif;">';
			$Mailtext .= nl2br(Input::post('Mailtext'));
			$Mailtext .= '</div>';
			
			$Dateien_arr = Input::post('Dateien');
			
			// Initialisiere anlagen_log Variable
			$anlagen_log = '';
			
			$objEmail=new Email();
			$objEmail->from=$AbsenderMailAdresse;
			$objEmail->fromName=$AbsenderMailName;
			$objEmail->replyTo($AntwortMailAdresse);
			// empty($versender_replyto) ? '' : $objEmail->replyTo($versender_replyto);
			$objEmail->subject=$Betreff;
			$objEmail->html=$Mailtext;
			$objEmail->sendCc($ccMail);
			$objEmail->sendBcc('schepke@eurooffice.de');
			
			
			if (is_array($Dateien_arr)) {
				foreach($Dateien_arr as $anlage) {
						// eo: Dateinamen der Anlage ohne Kürzel und Nummern erzeugen
						$filename_tmp = basename($anlage);
						$filename_arr = explode("_",$filename_tmp);
						//$ext_tmp = explode(".",end($filename_arr));
						//$ext = end($ext_tmp);
						$filename = str_replace($filename_arr[0]."_", "", $filename_tmp);
						if ($filename_arr[0] == "eoinfo") {
							$filename = "Euro-Office Info.pdf";
						}
						
						// einfache Version ohne Möglichkeit (Attachment-)Dateinamen zu beeinflussen wäre:
						// $objEmail->attachFile($filepath);
						$filepath = TL_ROOT . '/' . $anlage;
						// if ($verstab_result->mail_art != 'p') {
							$objEmail->attachFileFromString(file_get_contents($filepath), $filename);
						// }
						// geht so nicht, da attach() von der Contao-Klasse nicht erkannt wird:
						// $objEmail->attach(\Swift_Attachment::fromPath($filepath)->setFilename($filename));
						$anlagen_log .= '&nbsp;-&nbsp;' . $filename . '<br>';
				}
			}
			
			// dump($AbsenderMailAdresse);
			// dump($AbsenderMailName);
			// dump($AntwortMailAdresse);
			// dump($EmpfaengerMailAdresse);
			// dump($Betreff);
			// dump($Mailtext);
			// dump($objEmail);
			$objEmail->sendTo($EmpfaengerMailAdresse);
			
			// Protokollmail zum Debuggen an mich
			unset($objEmail);
			$objEmail=new Email();
			$objEmail->from=$AbsenderMailAdresse;
			$objEmail->fromName="Debugmailer";
			$objEmail->subject="EO Anlagenversand - Debugmail";
			$Mailtext = '<div style="font: 12px arial, sans-serif;">';
			$Mailtext .= 'Von ' . $AbsenderMailName .' (' .$AntwortMailAdresse. ') wurden folgende Anlagen versendet:<br>';
			$Mailtext .= $anlagen_log;
			$Mailtext .= '<br>Gruß M.S.  :-)';
			$Mailtext .= '</div>';
			$objEmail->html=$Mailtext;
			$objEmail->sendTo('schepke@eurooffice.de');
		}
		
		/**** Variablen/Arrays an Template übergeben **/

		$template->phase = $phase;
		$template->eoinfotitle = $eoinfo_arr['title']; 
		$template->anlagen = $infoanlagen;
		$template->eoidneu = $id;
		
		return $template->getResponse();
	}
}
