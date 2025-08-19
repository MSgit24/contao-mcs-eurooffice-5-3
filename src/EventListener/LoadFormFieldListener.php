<?php

// src/EventListener/LoadFormFieldListener.php
// namespace App\EventListener;
namespace Mcs\ContaoMcsEurooffice\EventListener;


use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\Widget;
// use Contao\System;
// use Contao\CoreBundle\Monolog\ContaoContext;
// use Psr\Log\LogLevel;
use Contao\FrontendUser;
use Contao\Database;
use Contao\Input;

/*
 * hiermit Contao Formular nicht nur zum Eingeben, sondern auch zum Ändern von Daten nutzen
 * 
 * Klasse LoadFormFieldListener
 * - Reagiert auf den 'loadFormField'-Hook in Contao
 * - Wird für jedes Formularfeld einzeln ausgelöst, wenn ein Formular geladen wird
 */

#[AsHook('loadFormField')]
class LoadFormFieldListener
{
	public function __invoke(Widget $widget, string $formId, array $formData, Form $form): Widget
	{
		/**
		 * in Contao verwirrend gemacht: identifizieren des Formulars mit $formId greift auf
		 * das Feld von Formulareigenschaften "Formular-ID" zurück (hier "vertEmpfForm"), hier 
		 * in der Abfrage muss aber ein "auto_" vorangestellt werden, um das Formular zu identifizieren
		 * deshalb besser auf die Nr. bzw. ID des Formulars selbst zurückgreifen
		 */
		// echo '<pre>formId: ' . $formId . '</pre>'; exit;
		// echo '<pre>form id: ' . $form->id . '</pre>'; exit;

		// if ('auto_vertEmpfForm' === $formId) {
		if ($form->id === 3) {

			// zu viel Log-Ausgabe, da für jedes Formularfeld einzeln ausgelöst wird
				/*
				$logger = System::getContainer()->get('monolog.logger.contao');
				$logger->log(
					LogLevel::INFO,
					'LoadFormFieldListener durch ' . $formId . ' ausgelöst',
					['contao' => new ContaoContext(__METHOD__, 'LOAD_FORM_FIELD')]
				);
				*/
			
			// hier auch die Angaben aus $form
				// echo '<pre>';
				// print_r($formData);
				// echo '</pre>';
				// exit;

			$member = FrontendUser::getInstance();
			$vertadr_id = Input::get('id');

				$database = Database::getInstance();

				// company via FrontendUser zur Sicherheit
				$result = $database->prepare('SELECT * FROM tl_eo_be_vertadr WHERE id = ? AND company = "' . $member->company . '"')->limit(1)->execute($vertadr_id);

				// eo: hidden-field company (des eingeloggten Members)
				// eo: wichtig, wg. Abgrenzung zw. den LK/SK
				if ($widget->id == 8) {
					$widget->value = $member->company;
				}
				// eo: Feld mit Mailadresse
				// if ($widget->id == 9) {
				if ($widget->name == "mail") {
					$widget->value = $result->mail;
				}
				// eo: Feld mit Nachname
				// if ($widget->id == 12) {
				if ($widget->name == "name") {
					$widget->value = $result->name;
				}
				// eo: Feld mit Vorname
				// if ($widget->id == 49) {
				if ($widget->name == "firstname") {
					$widget->value = $result->firstname;
				}
				// if ($widget->id == 13) {
				if ($widget->name == "comment") {
					$widget->value = $result->comment;
				}
				// if ($widget->id == 50) {
				if ($widget->name == "institution") {
					$widget->value = $result->institution;
				}
				// eo: Ref: direkt den Feldnamen nehmen
				if ($widget->name == "themen") {
					$widget->value = $result->themen; // a:3:{i:0;s:1:"2";i:1;s:1:"3";i:2;s:1:"4";}    
				}

				// da Contao eigenes Formular, geht nur speichern, daher muss der urspr. Eintrag gelöscht werden
				// beim Speichern wird wird ein save-callback/Hook ausgelöst (s. StoreFormDataListener), 
				// diesem muss übermittelt werden welcher Vorgängereintrag gelöscht werden muss, dieser 
				// Wert kommt in delete_id
				if ($widget->name == "delete_id") {
					$widget->value = $vertadr_id;
				}

				// if ($widget->id == 70)
				if ($widget->name == "published") {
					$widget->value = $result->published;
				}

				// if ($widget->id == 69)
				if ($widget->name == "published_comment") {
					$widget->value = $result->published_comment;
				}
				if ($widget->name == "geloescht") {
					$widget->value = $result->geloescht;
				}

		}
		return $widget;
	}
}
