<?php



/** ================================================
 * 
 * ab hier die alten DCA Einstellungen
 * 
 * nicht löschen, sondern nur auskommentieren
 * 
 * ================================================ */

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   eoModul
 * @author    Markus Schepke
 * @license   MIT
 * @copyright MCON 2012
 

/** eo Übersetzungen
/**
 * Back end modules
 */

// ----
$GLOBALS['TL_LANG']['tl_member']['eoinfo_legend'] = 'Angaben für die Mail mit der EO-Info (bspw. an die EU-Koordinatoren, ReM, ...)';

$GLOBALS['TL_LANG']['tl_member']['mail_direktempf'] = array('Mitglied ist Direktempfänger der EO-Info.','<span style="color:red;">Wenn ja, dann auch unten einer Direktempfängergruppe zuordnen!</span>');
$GLOBALS['TL_LANG']['tl_member']['pdf_anrkopf'] = array('Anrede 1 im PDF (oben unter LK/SK)','Herrn/Frau + Name');
$GLOBALS['TL_LANG']['tl_member']['pdf_anrtext'] = array('Anrede 2 im PDF (vor dem eigentlichen Text)','gesamte Zeile: Sehr geehrte(r) Frau / Herr + Name + "!"');
$GLOBALS['TL_LANG']['tl_member']['mail_anr'] = array('Anrede in der Mail','wer erhält die Mail konkret (wie PDF-Anr. 2, vor Mailtext (meist = EO-Infotext))');
$GLOBALS['TL_LANG']['tl_member']['mail_adr'] = array('Mailadresse(n)','Mailadresse EU-Koordinator oder des Sekretariats, Komma getrennt mehrere möglich)');
$GLOBALS['TL_LANG']['tl_member']['mail_adrcc'] = array('cc-Mailadresse(n)','Komma getrennt mehrere möglich');
$GLOBALS['TL_LANG']['tl_member']['mail_adrbcc'] = array('bcc-Mailadresse(n)','Komma getrennt mehrere möglich');
$GLOBALS['TL_LANG']['tl_member']['mail_txtextra'] = array('Text der Mail (Standard: leer lassen)','Falls jemand hier nicht den Text der EO-Info haben will. (dynamisch ergänzt: #datum#, #themen#, #eotitel#)');
$GLOBALS['TL_LANG']['tl_member']['mail_txterkl'] = array('Erklärungstext unter dem Mailtext','');
$GLOBALS['TL_LANG']['tl_member']['mail_monatsliste'] = array('Empfänger will eine Übersicht der eingestellten EO-Infos erhalten (am 1. eines Monats).','');
$GLOBALS['TL_LANG']['tl_member']['note'] = array('Notizen','interne Notizen, bspw. Zwischenablage von Adressen oder Logeinträge.');

// ----
$GLOBALS['TL_LANG']['tl_member']['verteiler_legend'] = 'Angaben für das Verteilersystem';
$GLOBALS['TL_LANG']['tl_member']['mail_verteilerabs'] = array('Mitglied ist Absender beim Verteilersystem. (Nur <span style="color:red;">1x</span> pro LK/SK/Einr. vergeben.)','<span style="color:red;">Wenn ja, dann auch unten als "EO-Verteiler-Absender" zuordnen!</span>');
$GLOBALS['TL_LANG']['tl_member']['mail_globempf'] = array('Mailadresse, an den der Verteiler geschickt wird','Standard: verteiler@eurooffice.de, die einzelnen Empfänger sind in bcc');
$GLOBALS['TL_LANG']['tl_member']['vert_notbcc'] = array('Empfänger sollen sich sehen können (also in cc statt in bcc)','Standard: nicht angewählt (vorerst nur auf Wunsch EL/Ludden)');
$GLOBALS['TL_LANG']['tl_member']['mail_abs'] = array('Mailadresse, die als Absender eingetragen wird.','Standard: verteiler@eurooffice.de; die originalen Adressen gehen meist aus Sicherheitsgründen nicht ...');
$GLOBALS['TL_LANG']['tl_member']['mail_replyto'] = array('Hier dann die (originale/reply-to) Adresse, an die die Antwort geht.','Workaround wg. der o.g. Sicherheitseinstellungen ...');
$GLOBALS['TL_LANG']['tl_member']['mail_betreff'] = array('Betreff','Standard: "WG: Euro-Office Info"');
$GLOBALS['TL_LANG']['tl_member']['mail_verttext'] = array('Mailtext','"xy" wird durch den Titel der EO-Info ersetzt.');
$GLOBALS['TL_LANG']['tl_member']['vert_verzh'] = array('Verzögerung in Stunden (1 für "keine" Verz., 8 für einen Tag)','Verteiler wird ... Stunden nach Versand der EO-Info bedient (an Werktagen & -stunden).');
$GLOBALS['TL_LANG']['tl_member']['vert_maillog'] = array('Absender will eine Protokollmail erhalten.','');

$GLOBALS['TL_LANG']['tl_member']['pwklar'] = array('NEU Passwort im Klartext','Bei Rückfragen ...');