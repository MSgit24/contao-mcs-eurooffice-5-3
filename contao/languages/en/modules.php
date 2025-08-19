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

use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\MusterFemodultypController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichtallgController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulFeedbackauswertung;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersprotokollmconController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersandstatistikController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVertsystemadrlisteController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulKundenmemberlisteController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVolltextsucheController;   
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAuflistungeoinfosController;   
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersandprotokolleobericht;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEoinfoanzeigeController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEodboutputController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEodbformular;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAnlagenpereoinfoversendenController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichtanzahlvertempfaengerController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichttabellenlkskverteilerController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAuflistungaktualisiertereoinfosController;

/**
 * Backend modules
 */
// $GLOBALS['TL_LANG']['MOD']['muster_bemodulkategorie'] = 'Muster BE-Modul-Kategorie';
// $GLOBALS['TL_LANG']['MOD']['muster_bemodultyp'] = ['muster_bemodultyp_translation', 'Beschreibung der muster_bemodultyp_translation'];

$GLOBALS['TL_LANG']['MOD']['be_eurooffice'] = 'Euro-Office Backend'; // Kategorie ==============================
$GLOBALS['TL_LANG']['MOD']['versand'] = ['Versand', 'Überblick über die Versandoperationen'];
$GLOBALS['TL_LANG']['MOD']['themen'] = ['Themen', 'Überblick über die Themen'];
$GLOBALS['TL_LANG']['MOD']['eoinfos'] = ['EO-Infos', 'EO-Infos anlegen'];
$GLOBALS['TL_LANG']['MOD']['programme'] = ['Programme', 'Programme verwalten'];
$GLOBALS['TL_LANG']['MOD']['schlagworte'] = ['Schlagworte', 'Schlagworte verwalten'];
$GLOBALS['TL_LANG']['MOD']['anlagearten'] = ['Anlagearten', 'Dateitypen verwalten'];
$GLOBALS['TL_LANG']['MOD']['vertadr'] = ['Verteileradressen', 'Verteilersystem - Adressen der LK/SK'];
// $GLOBALS['TL_LANG']['MOD']['dbalt_file'] = ['DB-alt Hpt.tabelle', 'DB-alt Haupttabelle verwalten'];
// $GLOBALS['TL_LANG']['MOD']['dbalt_filefile'] = ['DB-alt file:file', 'DB-alt Dateizuordnungen verwalten'];
// $GLOBALS['TL_LANG']['MOD']['dbalt_fileprg'] = ['DB-alt file:prg', 'DB-alt Prg.zuordnung vornehmen'];
// $GLOBALS['TL_LANG']['MOD']['dbalt_fileschlw'] = ['DB-alt file:schlw', 'DB-alt Schlw.zuordnungen vornehmen'];


/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['muster_femodulkategorie'] = 'Muster FE-Modul-Kategorie'; // ==============================

$GLOBALS['TL_LANG']['FMD'][MusterFemodultypController::TYPE] = ['Bez. des MusterFemodultypController', 'Beschreibung der muster_femodultyp_translation'];


$GLOBALS['TL_LANG']['FMD']['eurooffice_neu'] = 'EO-Module neue Version'; // ==============================

$GLOBALS['TL_LANG']['FMD'][FemodulBerichtallgController::TYPE] = [
    'FE-Modul: EO-Bericht allg. NEU', 
    'lfd. Auslesen von Angaben für den EO-Bericht',
];
$GLOBALS['TL_LANG']['FMD'][FemodulFeedbackauswertung::TYPE] = [
    'FE-Modul: Feedbackauswertung NEU', 
    'Auswertung der Rückmeldungen',
];

$GLOBALS['TL_LANG']['FMD'][FemodulVersprotokollmconController::TYPE] = [
    'FE-Modul: Versandprotokoll Mcon NEU', 
    'Anzeige der Versandprotokolle',
];

$GLOBALS['TL_LANG']['FMD'][FemodulVersandstatistikController::TYPE] = [
    'FE-Modul: Versandstatistik NEU', 
    'Anzeige der Versandstatistik',
];

$GLOBALS['TL_LANG']['FMD'][FemodulVertsystemadrlisteController::TYPE] = [
    'FE-Modul: Verteilersystem Adressliste NEU', 
    'Anzeige der jeweiligenVerteiler-Empfänger',
];

$GLOBALS['TL_LANG']['FMD'][FemodulKundenmemberlisteController::TYPE] = [
    'FE-Modul: Kundenmemberliste NEU', 
    'Anzeige der Kundenmitglieder',
];

$GLOBALS['TL_LANG']['FMD'][FemodulVolltextsucheController::TYPE] = [
    'FE-Modul: Volltextsuche NEU', 
    'Anzeige der Volltextsuche',
];

$GLOBALS['TL_LANG']['FMD'][FemodulAuflistungeoinfosController::TYPE] = [
    'FE-Modul: Auflistung EO-Infos NEU', 
    'Auflistung der EO-Infos',
];

$GLOBALS['TL_LANG']['FMD'][FemodulVersandprotokolleobericht::TYPE] = [
    'FE-Modul: Versandprotokoll EO-Bericht NEU', 
    'Anzeige der Versandprotokolle für den EO-Bericht',
];

$GLOBALS['TL_LANG']['FMD'][FemodulEoinfoanzeigeController::TYPE] = [
    'FE-Modul: EO-Info Anzeige NEU', 
    'Anzeige der ausgewählten EO-Info',
];

$GLOBALS['TL_LANG']['FMD'][FemodulEodboutputController::TYPE] = [
    'FE-Modul: EO-DB Output NEU', 
    'Anzeige von EO-Infos nach Programm oder Schlagwort',
];

$GLOBALS['TL_LANG']['FMD'][FemodulEodbformular::TYPE] = [
    'FE-Modul: EO-DB Formular NEU', 
    'EO-DB Formular für Prg. & Schlw.',
];

$GLOBALS['TL_LANG']['FMD'][FemodulAnlagenpereoinfoversendenController::TYPE] = [
    'FE-Modul: Anlagen per EO-Info versenden NEU', 
    'Anlagen per EO-Info versenden',
];

$GLOBALS['TL_LANG']['FMD'][FemodulBerichtanzahlvertempfaengerController::TYPE] = [
    'FE-Modul: Bericht Anzahl Verteilersystem-Empfänger NEU', 
    'Für Bericht Anzahl Mails pro LK/SK und Jahr (Verteilersystem)',
];

$GLOBALS['TL_LANG']['FMD'][FemodulBerichttabellenlkskverteilerController::TYPE] = [
    'FE-Modul: Bericht Tabellen LK/SK-Verteiler NEU', 
    'Für die EO-Berichte die Verteilertabellen erstellen',
];

$GLOBALS['TL_LANG']['FMD'][FemodulAuflistungaktualisiertereoinfosController::TYPE] = [
    'FE-Modul: Auflistung aktualisierter EO-Infos NEU', 
    'Auflistung von nachträglich aktualisierten EO-Infos',
];