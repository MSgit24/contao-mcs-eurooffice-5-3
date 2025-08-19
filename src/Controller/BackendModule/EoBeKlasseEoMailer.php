<?php

// declare(strict_types=1);

/*
 * This file is part of eurooffice.
 *
 * (c) MS 2025 <schepke@mcon-consulting.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/mcs/contao-mcs-eurooffice
 */

 namespace Mcs\ContaoMcsEurooffice\Controller\BackendModule;
 
// PHP 8.3 & Contao 5 Kompatibilität: Use-Statements für Contao-Klassen hinzugefügt
use Contao\BackendModule;
use Contao\Database;
use Contao\Config;
use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Contao\ProjectDir;
use Contao\Email;
use Contao\Input;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;

/*
 * Die Klasse `EoBeKlasseEoMailer` dient als Backend-Modul innerhalb des Contao-Systems,
 * um den Versand von EO-Infos zu steuern und zu protokollieren. Sie ermöglicht sowohl
 * den manuellen Versand durch Benutzerinteraktion im Backend als auch den automatisierten
 * Versand über Cronjobs. Die Klasse stellt sicher, dass die Versandinformationen korrekt
 * in der Datenbank aktualisiert werden und protokolliert alle relevanten Aktionen im
 * Contao-Log für eine verbesserte Nachverfolgbarkeit und Fehlerdiagnose.
 * 
	=> wird per Klick in BE-Versand ausgelöst oder ... 
		- Link wird in tl_eo_be_versand mit key=... gesetzt
		- außerdem Eintrag der Methode (und in welcher Klasse zu finden) in config
		-> aktueller Timestamp per get
		-> welche EO-Info (ID) per get
	=> ... per cron-Aufruf
		- Eintrag in config unter CRON
		- timestamp selbst ermitteln
		- eoid : noch unversendete EO-Info suchen, davon die älteste (= kleinste ID) nehmen
*/
 
class EoBeKlasseEoMailer extends System
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = '';


	public function __construct() 
    { 
        //parent::__construct(); 
        //$this->import('BackendUser', 'User'); 
		//$this->import('Database');
		//$objFeUser = \FrontendUser::getInstance(); 
    }
	

	public function sendVersandSerie() 
	{
		// b64link_encode Funktion als private Methode definiert (siehe unten)
		
		$database = Database::getInstance();
		
		// Projekt-Verzeichnis für Contao 5-Kompatibilität
		$projectDir = System::getContainer()->getParameter('kernel.project_dir');
	
		if (Input::get('vers_tstamp') > 0) {
			// exit;
			// eo: ausgelöster Versand - nur (w)artende & (d)irektversand (kein Verteilermails auslösen) & published (nicht ausgetoggelt)
			$vers_tstamp = Input::get('vers_tstamp');
			$eoid = Input::get('eoid');
			$verstab_sql = 'SELECT * FROM tl_eo_be_versand WHERE eoid = ' . $eoid . ' AND status = "w" AND mail_art = "d" AND published = 1 LIMIT 15';
		} else {
			//exit;
			// eo: Versand via Cron-Job - wartende Mails, beide Arten (direkt und Verteiler)
			
			// debug : $this->log('per CRON ausgelöst: sendVersandSerie', __METHOD__, TL_CRON); // alte Logging-Methode auskommentiert
			// System::getContainer()->get('monolog.logger.contao')->log(LogLevel::DEBUG, 'per CRON ausgelöst: sendVersandSerie', ['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]);
			
			//     niedrigste eoID (= älteste) zuerst versenden, nächste eoID beim nächsten Cron-Durchlauf
			$eoid_sql = 'SELECT MIN(eoid) AS eoid FROM tl_eo_be_versand WHERE vers_zeit <= ' . time() . ' AND status = "w" AND published = 1';
			$eoid_result = $database->prepare($eoid_sql)->limit(1)->execute();
			if ($eoid_result->eoid > 0) {
				$eoid = $eoid_result->eoid;
			} else {
				$eoid = 0;
			}
			$verstab_sql = 'SELECT * FROM tl_eo_be_versand WHERE eoid = ' . $eoid . ' AND vers_zeit <= ' . time() . ' AND status = "w" AND published = 1 LIMIT 10';
			// print_r($verstab_sql);
			// exit;
		}
		
		/** die Themen incl. Abkürzung etc. holen **/
		$objEoArr = new EoBeKlasseGetSelects();
		// themenAlle_arr[lfdId] + [title]/[abkrz]/[kuerzel]
		$themenAlle_arr = $objEoArr->getThemenArray(); 
		
		// eo: EoInfoText zur eoid abrufen
		// $eoinfo_sql = 'SELECT infotext FROM tl_eo_be_eoinfos WHERE id = '. $eoid;
		$eoinfo_sql = 'SELECT infotext, vers_themen, teaser_frist, teaser_berechtigt, teaser_fmgeber, teaser_themen, teaser_hinweis FROM tl_eo_be_eoinfos WHERE id = '. $eoid;
		$eoinfotab_result = $database->prepare($eoinfo_sql)->limit(1)->execute();
		
		// themat. Verteiler sollen mit in den Teaser
		$versandthemen_tmp = StringUtil::deserialize($eoinfotab_result->vers_themen);

		// Initialisiere Array für Verteiler-Themen
		$arrVersandthemen4teaser = array();

		if (is_array($versandthemen_tmp)) {
			foreach($versandthemen_tmp as $key => $versandthemen_nr) {
				$result = $database->prepare('SELECT title FROM tl_eo_be_themen WHERE id=?')->execute($versandthemen_nr);
				$arrVersandthemen4teaser[] = $result->title;		
			}
		}
		$versandthemen4teaser = implode(", ", $arrVersandthemen4teaser);
		
		// $verstab_result = $database->prepare($verstab_sql)->execute();
		$verstab_result = $database->query($verstab_sql);
		
		while($verstab_result->next())
		{
			// eo: unterschiedl. Felder nötig für d bzw. v
			if ($verstab_result->mail_art == 'd') { 
				// eo: die Versandadressen kommen aus der Versandtabelle (dort eingetragen zur besseren Kontrolle der Versandinformationen bzw. zwischenzeitlichen Änderungen), ab mail_abs die Angaben für den Verteilerversand (mail_art 'v')
				// Abruf der Daten passend zum entspr. Direktverteiler-Member
				$member_sql = 'SELECT mail_anr, mail_txtextra, mail_txterkl FROM tl_member WHERE id = '. $verstab_result->member_id;
			} else {
				// Abruf der Angaben für den Verteilerversand: da ggf. Verteilerabsender und Direktempfänger unterschiedlich, den "erstbesten" Absender pro company nehmen
				$member_sql = 'SELECT mail_abs, mail_replyto, mail_globempf, mail_betreff, mail_verttext, mail_txterkl, vert_maillog, vert_notbcc FROM tl_member WHERE mail_verteilerabs = 1 AND company = "'. $verstab_result->company .'" ORDER BY id ASC';
			}
			$membertab_result = $database->prepare($member_sql)->limit(1)->execute();
			
			if ($membertab_result->numRows > 0) {
				$vers_themen = StringUtil::deserialize($verstab_result->vers_themen);
				
				// Initialisiere Arrays für Themen-Abkürzungen und Vollbezeichnungen
				$t_abkrzen = array();
				$t_vollbez = array();
				
				// Prüfe ob vers_themen ein Array ist
				if (is_array($vers_themen)) {
					foreach ($vers_themen as $t_nr) {
						$t_abkrzen[] = $themenAlle_arr[$t_nr]['abkrz'];
						$t_vollbez[] = $themenAlle_arr[$t_nr]['title'];
					}
				}
				// Betreff steht im Mailheader, Codierung im Body greift also nicht. Also hier "Sonderbehandlung" ...
				$db_title = html_entity_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $verstab_result->title)); 
				
				if ($verstab_result->mail_art == 'd') {
					$versender_adr = $verstab_result->sender_adr;
					$versender_replyto = '';

					// zum 1.1.2021 sollen die themat. Verteiler nicht mehr im Betreff aufgeführt werden
					// $betreff = 'Euro-Office-Info ('.implode(", ", $t_abkrzen).') - ' . $db_title;
					$betreff = 'Euro-Office-Info - ' . $db_title;
					
					$empf_adr = $verstab_result->mail_adr;
				}
				if ($verstab_result->mail_art == 'v') {
					$versender_adr = $membertab_result->mail_abs;
					$versender_replyto = $membertab_result->mail_replyto;
					// $betreff = $membertab_result->mail_betreff .' - ' . htmlspecialchars_decode($verstab_result->title);
					// $betreff_tmp = $membertab_result->mail_betreff .' - ' . $verstab_result->title;
					// $betreff = '=?UTF-8?B?'.base64_encode($betreff_tmp).'?=';
					$betreff = $membertab_result->mail_betreff .' - ' . $db_title;
					$empf_adr = $membertab_result->mail_globempf;
				}
				if ($verstab_result->mail_art == 'p') {
					$versender_adr = $verstab_result->sender_adr;
					$versender_replyto = '';
					// $betreff = 'Versandprotokoll zur Euro-Office-Info "' . htmlspecialchars_decode($verstab_result->title).'"';
					// $betreff_tmp = 'Versandprotokoll zur Euro-Office-Info "' . $verstab_result->title.'"';
					// $betreff = '=?UTF-8?B?'.base64_encode($betreff_tmp).'?=';
					$betreff = 'Versandprotokoll zur Euro-Office-Info "' . $db_title.'"';
					$empf_adr = $verstab_result->mail_adr;
				}
				
				if ($verstab_result->mail_art == 'd') {
					// $mailtext_html = '<div style="font: 13px Calibri, arial, sans-serif;">';

					// Angleich Web, Mail, PDF
					// Outlook Mac nimmt normale & OL Win nimmt mso Angaben
					$mailtext_html = '<style>
					p, ul, ol {margin: 0; margin-bottom: 0.3rem; color: black;} 
					div {margin: 0; margin-top: 0.3rem; margin-bottom: 0.9rem; color: black;} 
					ul, ol {margin-left: -1.0rem; margin-top: 0px;} 
					.lineheight025 {line-height: 0.25;}
					.lineheight050 {line-height: 0.50;}
					.lineheight100 {line-height: 1.00;}
					.lineheight150 {line-height: 1.50;}
					</style>
					
<!--[if mso ]>
    <style type="text/css">
    /* Your Outlook-specific CSS goes here. */
					p, ul, ol {margin: 0; margin-bottom: 0.3rem; color: black;} 
					div {margin: 0; margin-top: 0.3rem; margin-bottom: 0.9rem; color: black;} 
					ul, ol {margin-left: -1.0rem; margin-top: 0px;} 
					.lineheight025 {line-height: 25%;}
					.lineheight050 {line-height: 50%;}
					.lineheight100 {line-height: 100%;}
					.lineheight150 {line-height: 150%;}
    </style>
<![endif]-->					
					
					';
					$mailtext_html .= '<div style="font-family: Helvetica, Calibri, sans-serif; font-size: 13px;">';
					// h2 in Thunderbird in times ...
					// Kopfzeile ---------------------
					//$mailtext_html .= '<table width="100%"><tr><td valign="center"><h2 style="font-family: sans-serif;">Euro-Office Infodienst</h2></td><td align="right"><img src="logo_mcon-europa.png"></td></tr></table>';
					$mailtext_html .= '<table width="100%"><tr><td valign="center"><h2 style="font-family: sans-serif;">Euro-Office Infodienst</h2></td><td align="right"><img src="logo_mcon_mail.png"><br>&nbsp;</td></tr></table>';
					
					// Teaser ------------------------
					/* Teaserelemente sind: teaser_verteiler (ab 1.1.2021), teaser_frist, teaser_berechtigt, teaser_fmgeber, teaser_themen, teaser_hinweis */
					
					// so in meinem Code, aber $eoinfo_arr['teaser_hinweis']) könnte falsch sein
					// $teasercheckallg = trim($eoinfotab_result->teaser_frist) . trim($eoinfotab_result->teaser_berechtigt) . trim($eoinfotab_result->teaser_fmgeber) . trim($eoinfotab_result->teaser_themen) . trim($eoinfotab_result->teaser_hinweis);
					$teasercheckallg = trim($eoinfotab_result->teaser_frist) . trim($eoinfotab_result->teaser_berechtigt) . trim($eoinfotab_result->teaser_fmgeber) . trim($eoinfotab_result->teaser_themen) . trim($eoinfotab_result->teaser_hinweis);
					if (strlen($teasercheckallg) > 0) {
						// $mailtext_html_teaser = "<table style='font-size:85%;'><tr><td colspan='2'><b>Überblick:</b></td></tr>";
						$mailtext_html_teaser = '<table border="0" width="100%" style="font-family:Helvetica,Calibri,sans-serif; font-size:13px;"><tr><td width="120"><tr><td colspan="2"><b>Überblick:</b></td></tr>';
						
						(strlen(trim($eoinfotab_result->teaser_frist))      > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Antragsfrist:</td><td>"             . $eoinfotab_result->teaser_frist      . "</td></tr>" : $mailtext_html_teaser .= "" ;
						(strlen(trim($eoinfotab_result->teaser_berechtigt)) > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Antragsberechtigte:&nbsp;</td><td>" . $eoinfotab_result->teaser_berechtigt . "</td></tr>" : $mailtext_html_teaser .= "" ;
						(strlen(trim($eoinfotab_result->teaser_fmgeber))    > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Zuwendungsgeber:</td><td>"        . $eoinfotab_result->teaser_fmgeber    . "</td></tr>" : $mailtext_html_teaser .= "" ;
						(strlen(trim($eoinfotab_result->teaser_themen))     > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Thema:</td><td>"            . $eoinfotab_result->teaser_themen     . "</td></tr>" : $mailtext_html_teaser .= "" ;
						(strlen(trim($eoinfotab_result->teaser_hinweis))    > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Hinweis:</td><td>"                  . $eoinfotab_result->teaser_hinweis    . "</td></tr>" : $mailtext_html_teaser .= "" ;
						(strlen(trim($versandthemen4teaser))                > 0) ? $mailtext_html_teaser .= "<tr bgcolor='#f5f5f5'><td vertical-align: top;'>Verteiler:</td><td>"             . $versandthemen4teaser      . "</td></tr>" : $mailtext_html_teaser .= "" ;
						$mailtext_html_teaser .= "</table>";
						$mailtext_html .= $mailtext_html_teaser;
					}
					
					// Anrede ------------------------
					$mailtext_html .= '<p><br>' . $membertab_result->mail_anr . '</p><p>&nbsp;</p>';
					// Text der Mail -----------------
					// Alternativer Text ...
					if (trim($membertab_result->mail_txtextra) <> "") {
						$mailtext_html_tmp = $membertab_result->mail_txtextra;
						$mailtext_html_tmp = str_replace('#datum#', date("d.m.Y",time()), $mailtext_html_tmp);
						$mailtext_html_tmp = str_replace('#themen#', implode(", ", $t_abkrzen), $mailtext_html_tmp);
						$mailtext_html_tmp = str_replace('#eotitel#', htmlspecialchars_decode($verstab_result->title), $mailtext_html_tmp);
						$mailtext_html .= $mailtext_html_tmp;
					} else {
						// oder Text der EO-Info
						$mailtext_html_tmp = $eoinfotab_result->infotext;
						$mailtext_html_tmp = str_replace('[&]', '&', $mailtext_html_tmp);
						//////////$mailtext_html .= str_replace('[nbsp]', ' ', $mailtext_html_tmp);
						$mailtext_html_tmp = str_replace('[nbsp]', '&nbsp;', $mailtext_html_tmp);
						//$mailtext_html_tmp = str_replace('p style', 'p class', $mailtext_html_tmp);
						$mailtext_html_tmp = str_replace('<ol', '<ol style="margin-top: 0cm;"', $mailtext_html_tmp);
						$mailtext_html_tmp = str_replace('<ul', '<ul style="margin-top: 0cm;"', $mailtext_html_tmp);
						//$mailtext_html_tmp = str_replace('line-height: 0.25; color: lightgray;', 'lineheight025', $mailtext_html_tmp);
						$mailtext_html .= $mailtext_html_tmp;
					}
					
					$mailtext_html .= '<p class="MsoNormal, lineheight100" style="line-height: 100%; color: limegreen;">&nbsp;</p>
					<p>Mit freundlichen Grüßen<br>MCON</p>
					<p class="MsoNormal, lineheight100" style="line-height: 100%; color: limegreen;">&nbsp;</p>
					<p>' . $verstab_result->author . '</p>';
					
					// Feedbackteil Start ------- 
					
					
					// b64link_encode Funktion jetzt als private Klassenmethode (siehe unten)
					// Quelle der Bewertung [Web / Mail]
					$quelle = 'mail';
					
					// Wertangabe der Bewertung
					// $bewertung = 1; s.u. bei den Links
					 
					// User-ID ermitteln --
					// bei Web-/Intranetnutzung
					// $objUser = FrontendUser::getInstance();
					// $empf_id = $objUser->id;
					// beim Mailversand
					$empf_id = $verstab_result->member_id;
					
					// EO-ID ermitteln --
					// bei Web-/Intranetnutzung
					// $eoid = $this->Input->get('eoidneu');
					// beim Mailversand
					// ID ist oben schon in $eoid abggelegt worden

					//  Aufbau : $param = WiFoe-ID - EO-ID - Bewertung - Quelle;
					$param_1 = $empf_id.'-'.$eoid.'-1-'.$quelle; // weniger relevant
					$param_2 = $empf_id.'-'.$eoid.'-2-'.$quelle; // ist relevant

					$crc_1 = crc32($param_1);
					$crc_2 = crc32($param_2);

					$param_final_1 = $param_1.'-'.$crc_1.'-dummy';
					$param_final_2 = $param_2.'-'.$crc_2.'-dummy';



					$mailtext_html .=  '<p>&nbsp;</p><p>&nbsp;</p><p>Feedback:&nbsp;<i>Geben Sie uns gerne eine kurze Rückmeldung, inwieweit diese Euro-Office-Info von Relevanz ist (durch Klick auf einen der beiden Links): &rArr; <a href="https://www.eurooffice.de/feedback.html?fb='.$this->b64link_encode($param_final_2).'" target="_blank">ist relevant</a> / &rArr; <a href="https://www.eurooffice.de/feedback.html?fb='.$this->b64link_encode($param_final_1).'" target="_blank">ist weniger relevant</a></i></p>';
					
					// Feedbackteil Ende --------
					
					// Textblock mit Erklärung zu Euro-Office und Kontaktdaten WiFö
					// alte braune Einfärbung : $mailtext_html .= '<p>&nbsp;</p><div style="background-color:#f5f1e6; padding: 5px;">' . $membertab_result->mail_txterkl . '</div></div>';
					$mailtext_html .= '<p>&nbsp;</p><div style="background-color:#f5f5f5; padding: 5px;">' . $membertab_result->mail_txterkl . '</div></div>';
					
				}
				if ($verstab_result->mail_art == 'v') {
					// $mailtext_html = '<div style="font: 13px Calibri, arial, sans-serif;">';
					$mailtext_html = '<div style="font-family: Helvetica, Calibri, sans-serif; font-size: 13px;">';
					$mailtext_html .= str_replace('titeldummy', '"' . htmlspecialchars_decode($verstab_result->title) .'" (Thema bzw. Verteiler: '. implode(", ", $t_vollbez) .')', $membertab_result->mail_verttext);
					// alte braune Einfärbung : $mailtext_html .= '<p>&nbsp;</p><div style="background-color:#f5f1e6; padding: 5px;">' . $membertab_result->mail_txterkl . '</div></div>';
					$mailtext_html .= '<p>&nbsp;</p><div style="background-color:#f5f5f5; padding: 5px;">' . $membertab_result->mail_txterkl . '</div></div>';
					// $mailtext_html .= str_replace('[nbsp]', ' ', $eoinfotab_result->infotext);
					// $mailtext_html = $membertab_result->mail_verttext;
				}
				if ($verstab_result->mail_art == 'p') {
					// $mailtext_html = '<div style="font: 13px Calibri, arial, sans-serif;">';
					$mailtext_html = '<div style="font-family: Helvetica, Calibri, sans-serif; font-size: 13px;">';
					$mailtext_html .= '<p>Diese Euro-Office Info wurde an folgende Empfänger verteilt:</p>';
					$mailtext_html .= str_replace(',', '<br>', $verstab_result->mail_adrbcc);
					$mailtext_html .= '<p>Mit freundlichen Grüßen<br>MCON</p><p>Markus Schepke</p>';
					$mailtext_html .= '</div>';
					// $mailtext_html = $membertab_result->mail_verttext;
				}
				
				$objEmail=new Email();
				// eo: Ref: fromName ergibt Angabe eines Namens/Wortes vor der Absenderadr. (Schepke <schepke@...>) 
				$objEmail->from=$versender_adr; 
				//$objEmail->replyTo=$versender_adr; 
				empty($versender_replyto) ? '' : $objEmail->replyTo($versender_replyto);
				$objEmail->subject=$betreff;
				$objEmail->html=$mailtext_html;
				
				$anlagen_arr = array();
				$anlagen_arr = explode(",", $verstab_result->vers_anlagen);
				
				foreach($anlagen_arr as $anlage) {
					if ($anlage != "") {
					
						// eo: Dateinamen der Anlage ohne Kürzel und Nummern erzeugen
						$filename_tmp = basename($anlage);
						$filename_arr = explode("_",$filename_tmp);
						//$ext_tmp = explode(".",end($filename_arr));
						//$ext = end($ext_tmp);
						$filename = str_replace($filename_arr[0]."_", "", $filename_tmp);
						if ($filename_arr[0] == "eoinfo") {
							$filename = 'Euro-Office Info vom ' . date("d.m.Y") . '.pdf';
						} /* else {
							$filename .= "." . $ext;
						} */
						
						// einfache Version ohne Möglichkeit (Attachment-)Dateinamen zu beeinflussen
						// $objEmail->attachFile($filepath);
						// $filepath = TL_ROOT . '/' . $anlage; // alte Methode für Contao 3/4
						$filepath = $projectDir . '/' . $anlage;
						
						// Prüfen ob Datei existiert und E-Mail-Anhang hinzufügen
						if ($verstab_result->mail_art != 'p') {
							if (file_exists($filepath) && is_readable($filepath)) {
								$fileContent = file_get_contents($filepath);
								if ($fileContent !== false) {
									$objEmail->attachFileFromString($fileContent, $filename);
								} else {
									// Log-Eintrag für fehlgeschlagenes Lesen
									System::getContainer()->get('monolog.logger.contao')
										->log(
											LogLevel::WARNING,
											sprintf('Datei konnte nicht gelesen werden: %s', $filepath),
											['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
										);
								}
							} else {
								// Log-Eintrag für nicht existierende Datei
								System::getContainer()->get('monolog.logger.contao')
									->log(
										LogLevel::WARNING,
										sprintf('Anhang-Datei nicht gefunden: %s', $filepath),
										['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
									);
							}
						}
						// geht so nicht, da attach() von der Contao-Klasse nicht erkannt wird
						// $objEmail->attach(\Swift_Attachment::fromPath($filepath)->setFilename($filename));
					}
				}
				
				// eo: Ref: swiftmailer gibt Fehlermeldung, wenn Adresse = ''
				//$objEmail->sendCc($verstab_result->mail_adrcc);
				if ($verstab_result->mail_art == 'd') {
					empty($verstab_result->mail_adrcc) ? '' : $objEmail->sendcc($verstab_result->mail_adrcc);
					empty($verstab_result->mail_adrbcc) ? '' : $objEmail->sendBcc($verstab_result->mail_adrbcc);
				}
				if ($verstab_result->mail_art == 'v') {
					// empty($verstab_result->mail_adrcc) ? '' : $objEmail->sendcc($verstab_result->mail_adrcc);
					// empty($verstab_result->mail_adrbcc) ? '' : $objEmail->sendBcc($verstab_result->mail_adrbcc);
					if ($membertab_result->vert_notbcc == 1) {
						empty($verstab_result->mail_adrbcc) ? '' : $objEmail->sendcc($verstab_result->mail_adrbcc);
					} else {
						empty($verstab_result->mail_adrbcc) ? '' : $objEmail->sendBcc($verstab_result->mail_adrbcc);
					}
				}
				if ($verstab_result->mail_art == 'p') {
					empty($verstab_result->mail_adrcc) ? '' : $objEmail->sendcc($verstab_result->mail_adrcc);
				}
				// $objEmail->imageDir = TL_ROOT . '/' . "files/themes/EO-Intranet/"; // alte Methode für Contao 3/4
				$objEmail->imageDir = $projectDir . '/' . "files/themes/EO-Intranet/";
				$objEmail->embedImages = "true";
				$objEmail->sendTo($empf_adr);
				
				unset($objEmail);
				unset($t_abkrzen);
				unset($t_vollbez);
					
				if ($verstab_result->mail_art == 'd') {
					//$database->prepare('UPDATE tl_eo_be_versand SET status = "v", vers_zeit = '.time().' WHERE eoid = ' . $eoid . ' AND member_id = '. $verstab_result->member_id .' AND mail_art = "d" AND published = 1')->execute();
					$database->prepare('UPDATE tl_eo_be_versand SET status = "v", vers_zeit = '.time().' WHERE eoid = ' . $eoid . ' AND id = '. $verstab_result->id .' AND mail_art = "d" AND published = 1')->execute();
					// $this->log('EO-Info (ID: '.$eoid.') an ' . $verstab_result->mail_adr . ' versendet', __METHOD__, TL_CRON); // alte Logging-Methode auskommentiert
					System::getContainer()->get('monolog.logger.contao')
						->log(
							LogLevel::INFO,
							sprintf('EO-Info (ID: %s) an %s versendet', $eoid, $verstab_result->mail_adr),
							['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]
						);
				}
				if ($verstab_result->mail_art == 'v') {
					$database->prepare('UPDATE tl_eo_be_versand SET status = "v", vers_zeit = '.time().' WHERE eoid = ' . $eoid . ' AND id = '. $verstab_result->id .' AND mail_art = "v" AND published = 1')->execute();
					// $this->log('EO-Info (ID: '.$eoid.') an ' . $verstab_result->company . '-Verteiler gesendet', __METHOD__, TL_CRON); // alte Logging-Methode auskommentiert
					System::getContainer()->get('monolog.logger.contao')
						->log(
							LogLevel::INFO,
							sprintf('EO-Info (ID: %s) an %s-Verteiler gesendet', $eoid, $verstab_result->company),
							['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]
						);
				}
				if ($verstab_result->mail_art == 'p') {
					$database->prepare('UPDATE tl_eo_be_versand SET status = "v", vers_zeit = '.time().' WHERE eoid = ' . $eoid . ' AND id = '. $verstab_result->id .' AND mail_art = "p" AND published = 1')->execute();
					// $this->log('Verteilerprotokoll zur EO-Info (ID: '.$eoid.') an ' . $verstab_result->company . ' gesendet', __METHOD__, TL_CRON); // alte Logging-Methode auskommentiert
					System::getContainer()->get('monolog.logger.contao')
						->log(
							LogLevel::INFO,
							sprintf('Verteilerprotokoll zur EO-Info (ID: %s) an %s gesendet', $eoid, $verstab_result->company),
							['contao' => new ContaoContext(__METHOD__, ContaoContext::CRON)]
						);
				}
				
			} // ende if membertab_result lag vor
			
		} // ende while nächste versand-tab row vornehmen
		
		// Zurück zur Übersicht - nur bei manuellem Aufruf (Button-Klick), nicht bei Cronjob
        if (Input::get('vers_tstamp') > 0) {
            // Manueller Aufruf - Redirect zum Backend
            // \Contao\Controller::redirect('contao/main.php?do=versand'); // alte Methode auskommentiert
            Controller::redirect(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'versand']));
        }
        // Bei Cronjob-Aufruf: kein Redirect, Script läuft einfach durch
		
	}
	
	/**
	 * Hilfsmethode für Base64-Link-Encoding im Feedback-System
	 * @param string $string
	 * @return string
	 */
	private function b64link_encode($string): string
	{
		$string = base64_encode($string);
		$string = urlencode($string);
		return $string;
	}
}
