<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   eoModul
 * @author    Markus Schepke
 * @license   MIT
 * @copyright MCON 2015
 */

 /** eo: Feldbezeichungen BE-Modul EO-Infos **/
 
/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['new']    = array('Neue EO-Info', 'Anlegen einer weiteren EO-Info.');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['show']   = array(' details', 'Show the details of  ID %s');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['edit']   = array('Edit ', 'Edit  ID %s');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['cut']    = array('Move ', 'Move  ID %s');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['copy']   = array('Duplicate ', 'Duplicate  ID %s');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['delete'] = array('Delete ', 'Delete  ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['title_legend'] = 'EO-Info anlegen/bearbeiten';
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['zuordnungen_legend'] = 'Zuordnungen festlegen';
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser'] = 'Teaser';
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['hauptinhalte_legend'] = 'Text und Anlagen';
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publish_legend'] = 'EO-Info: Voransicht, PDF erstellen, freischalten';
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['versand_legend'] = 'Versandeinstellungen';
/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['title'] = array('Titel der EO-Info','max. 255 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['alias'] = array('Alias für Darstellung als Website','Im Zweifel leer lassen bzw. nicht ändern!');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['datum'] = array('Datum','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['author'] = array('Autor/Versender','Zuständig und Absender der Info.');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['programme'] = array('Programme (z.Zt. aktuelle und zukünftige)','Mehrfachauswahl möglich.');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['schlagworte'] = array('Schlagworte','Mehrfachauswahl möglich.'); 
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['prgschlweoid'] = array('EO-ID eingeben für Vorauswahl einer Prg.-/Schlw.-Zuordnung.','Auslösen durch "Speichern". Ein Eintrag hier überschreibt die Auswahl oben.');


$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4WE'] = array('sichtbar für EO-Weser-Ems','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4LG'] = array('sichtbar für EO-Lüneburg','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4Koord'] = array('nur für EO-Koordinatoren','zusätzl. WE und/oder LG auswählen');

$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_frist'] = array('Antragsfrist','leer lassen, wenn unzutreffend; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_berechtigt'] = array('Antragsberechtigte','leer lassen, wenn unzutreffend; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_fmgeber'] = array('Zuwendungsgeber','leer lassen, wenn unzutreffend; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_themen'] = array('Thema','leer lassen, wenn unzutreffend; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_hinweis'] = array('Hinweis','was sich oben nicht zuordnen lässt; sonst leer lassen; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_aktualisierung'] = array('Aktualisiert wegen:','Hinweis auf Aktualisierung der Info; sonst leer lassen; max. 250 Zeichen');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_aktualdatum'] = array('Datum der Aktualisierung','Ohne Datum hier werden Aktualisierungen nicht angezeigt.');

// $GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['infotext'] = array('Textteil der EO-Info','Links auf andere EO-Infos mit "index.php/eo-cms-info-anzeigen.html?eoidneu=EO-ID_der_EO-Info"');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['infotext'] = array('Textteil der EO-Info','Link: "eo-cms-info-anzeigen.html?eoidneu=EO-ID" / Abstände via &lt;XY style="margin-top:20px;"&gt; / Umbruch  &lt;!-- pagebreak --&gt;');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['multiSRC'] = array('Anlagen zur EO-Info','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['published'] = array('im EO-Intranet anzeigen','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['generatepdf1'] = array('PDF-Voransicht erzeugen','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['generatepdf2'] = array('Versand-PDF erzeugen','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['pdfdatum'] = array('Datum für PDF-Erstellung vorgeben.',''); 
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_einst'] = array('Versandeinstellungen','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_empfgrp'] = array('EO-Info Direktempfänger(-gruppen)','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_themen'] = array('Euro-Office-Verteiler','');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_anlagen'] = array('Anlagen zur EO-Info (es werden nur gültig benannte Verzeichnisse und Dateien angezeigt)','PDF-Version der EO-Info selbst wird individuell erstellt und angehängt.');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_zeit'] = array('Tag und Zeit, wann Versand ausgelöst wird.','<span style="color:red;">Ein Zeitpunkt in der Vergangenheit löst sofort aus ... !</span>');
$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_prep'] = array('PDFs erstellen, Versand vorbereiten & <u>aktivieren</u>','<span style="color:red;"><b><u>Vor</u></b> dem Anwählen: alle Eingaben oben müssen gespeichert worden sein.</span>');

