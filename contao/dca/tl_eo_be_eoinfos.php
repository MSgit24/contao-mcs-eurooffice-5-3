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

// PHP 8.3 Fix: Use statements für Contao-Klassen hinzufügen um "Undefined type" Fehler zu beheben
use Contao\Backend;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\BackendUser;
use Contao\Config;
use Contao\Input;
use Contao\Image;
use Contao\System;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Contao\DC_Table;
 
// use eoModul\EoBeKlasseGetSelects;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
// use eoModul\EoFeKlasseEoinfoPdf;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseEoinfoPdf;

// Custom RTE-Configuration for News Text
$GLOBALS['TL_DCA']['tl_eo_be_eoinfos']['fields']['textarea']['eval']['rte'] = 'tinyMCEeoinfo'; 

/**
 * Table tl_eo_be_eoinfos
 */
$GLOBALS['TL_DCA']['tl_eo_be_eoinfos'] = array
(
 
	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('datum'),
			// eo: Sortierung absteigend nach Datum (= flag 6)
			//     dass Datum lesbar sowie Gruppierung nach Monat oder Jahr, s.u. ('datum' => array...)
			'flag'                    => 6,
			'panelLayout'             => 'filter;search,limit',
			//'child_record_callback'   => array('tl_eo_be_eoinfos', 'listEvents'),
		),
		'label' => array
		(
			'fields'                  => array('datum','title'),
			'format'                  => '<span style="color:gray;">[%s]</span> %s',
			// eo: mit showColumns Spalten erzeugen
			//'showColumns'             => true,
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			/* eo: Infos kopieren verhindern
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			), */
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			// eo: Ref: mit dem "grünen Auge" toggeln Teil 1
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['toggle'],
				'icon'                => 'visible.svg',
				'href'                => 'act=toggle&amp;field=published',
				'reverse'             => true,
				'button_callback'     => array('tl_eo_be_eoinfos', 'toggleIcon')
			)
		)
	),

	// Select
	'select' => array
	(
		'buttons_callback' => array()
	),

	// Edit
	'edit' => array
	(
		'buttons_callback' => array()
	),

	// Palettes
	'palettes' => array
	(
		/********
		// eo: erster Anlauf wg. Anlagen - mittels Enclosure wie bei Events
		//     dabei gehen aber nur einzelne Dateien, wenn auch über versch. Verz.
		'__selector__'                => array('addEnclosure'), // + Eintrag in Subpalettes
		********/
		'__selector__'                => array('vers_einst', 'vers_prep'),
		
		// eo: TODO: ";{zuordnung_legend},programme,schlagworte"
		// eo: in tl_content.php hinter multiSRC noch ",sortBy,metaIgnore" vorhanden ...
		//     geht aber auch so, incl. Sortierung
		// eo: wenn Enclosure, dann noch ;{enclosure_legend},addEnclosure
		// 'default'                     => '{title_legend},title,(alias:hidden);{zuordnungen_legend:hide},datum,author,programme,schlagworte,prgschlweoid;{hauptinhalte_legend},infotext,multiSRC;{publish_legend:hide},previewlink,pdfdatum,pdflink,pdflink2,published,generatepdf1,generatepdf2;{versand_legend},msg_versstatus,vers_einst,vers_prep;'
		'default'                     => '{title_legend},title,(alias:hidden);{zuordnungen_legend:hide},datum,author,schlagworte,programme,prgschlweoid,vers_themen,publ4WE,publ4LG,publ4Koord;{teaser:hide},teaser_frist,teaser_berechtigt,teaser_fmgeber,teaser_themen,teaser_hinweis,teaser_aktualisierung,teaser_aktualdatum;{hauptinhalte_legend},infotext,multiSRC;{publish_legend:hide},pdfdatum,published,generatepdf1;{versand_legend},msg_versstatus,vers_einst,vers_prep;'
		//vers_start_d,vers_start_z,aktiv
		
	),

	// Subpalettes
	'subpalettes' => array
	(
		// eo: s.o. wg. Enclosure-Ansatz
		//'addEnclosure'                => 'enclosure',
		// 'vers_einst'                   => 'vers_themen, vers_empfgrp, vers_anlagen, vers_zeit', /* vers_themen nach oben gesetzt wg. Einbau in Teaser */
		'vers_einst'                   => 'vers_empfgrp, vers_anlagen, vers_zeit',
		'vers_prep'                   => 'create_verstab, create_eopdfs',
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment",
			//'sorting'                 => 12
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['title'],
			'exclude'                 => true,
			// eo: Ref: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'search'                  => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['alias'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128),
			'save_callback' => array
			(
				array('tl_eo_be_eoinfos', 'generateAlias')
			),
			'sql'                     => "varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL default ''"
		),
		'datum' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['datum'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			'flag'                    => 8,
			'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['author'],
			'default'                 => BackendUser::getInstance()->id,
			'exclude'                 => true,
			// eo: Autor wird als Suchfeld angeboten, hier unnötig
			//'search'                  => true,
			// eo: Ref: im Panel nach Autor filtern können ...
			'filter'                  => true,
			//     ... dieses Select-Pulldown auch sortiert
			'sorting'                 => true,
			//     ... 11 = auf-, 12 = absteigend
			'flag'                    => 11,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'hasOne', 'load'=>'eager')
		),
		'programme' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['programme'],
			'exclude'   => true,
			'inputType' => 'select',
			// eo: Ref: normaler callback, keine Param-Übergabe möglich
			//'options_callback' => array('EoBeKlasseGetSelects', 'getEOPrgArray'),
			// eo: Ref: callback mit closure, erlaubt Übergabe von Parametern
			'options_callback' => function(){
				// getEOPrgArray: ohne Param alle, mit Param. entspr. eingeschränkt 
				// eo: ToDo: bei Bedarf alle zurückgeben lassen, gruppiert nach Status
				// return EoBeKlasseGetSelects::getEOPrgArray("aktuell");
				return EoBeKlasseGetSelects::getEOPrgArray("neu_und_zukunft");
			},
			//'eval'      => array('multiple'=> true,'tl_class'=>'w50 clr','size'=>15),
			'eval'      => array('chosen'=>true,'multiple'=> true,'tl_class'=>'w50','size'=>15),
			//'sql'       => "varchar(255) NOT NULL default ''"
			'sql'                     => "text NULL"
		),
		'schlagworte' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['schlagworte'],
			'exclude'   => true,
			'filter'                  => true,
			'inputType' => 'select',
			// obsolet, s.n.Zeile 'options_callback' => array('EoBeKlasseGetSelects', 'getEOSchlwArray'),
			// eo: Ref: alle Einträge aus einer Tab. mit foreignKey (Spalte angeben, in denen die Beschr. ist, id wird autom. eingesetzt)
			'foreignKey' => 'tl_eo_be_schlagworte.title',
			'eval'      => array('chosen'=>true,'multiple'=> true,'tl_class'=>'w50 clr','size'=>15),
			//'sql'       => "varchar(150) NOT NULL default 'ohne Schlw.'"
			'sql'                     => "text NULL"
		),
		// eo: Vorauswahl an Zuordnungen (Prg./Schlw.) vornehmen per ID einer alten Info
		'prgschlweoid' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['prgschlweoid'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'natural', 'mandatory'=>false,'tl_class'=>'clr', 'doNotCopy'=>true), //, 'submitOnChange'=>true
			'save_callback'           => array(array('tl_eo_be_eoinfos', 'copyPrgSchlw')),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		/* vers_themen nach oben gesetzt wg. Einbau in Teaser */
		'vers_themen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_themen'],
			'exclude'                 => true,
			// 'inputType'               => 'checkbox',
			'inputType'               => 'select',
			'foreignKey'              => 'tl_eo_be_themen.title',
			// alt // 'eval'                    => array('mandatory'=>true, 'multiple'=> true, 'csv'=>',', 'tl_class'=>'w50 clr'), // , 'submitOnChange'=>true
			// 'eval'                    => array('mandatory'=>true, 'multiple'=> true, 'tl_class'=>'w50 clr'), // , 'submitOnChange'=>true
			'eval'                    => array('chosen'=>true,'mandatory'=>true, 'multiple'=> true,'size'=>15),
			'sql'                     => "text NULL"
		),
		
		// =========================================
		
		'publ4WE' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4WE'],
			'exclude'                 => true,
			// 'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'publ4LG' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4LG'],
			'exclude'                 => true,
			// 'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'publ4Koord' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['publ4Koord'],
			'exclude'                 => true,
			// 'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default '0'"
		),	
		// =========================================
		
		'teaser_frist' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_frist'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_berechtigt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_berechtigt'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_fmgeber' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_fmgeber'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_themen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_themen'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_hinweis' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_hinweis'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_aktualisierung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['teaser_aktualisierung'],
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser_aktualdatum' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['aktualdatum'],
			'default'                 => time(),
			'exclude'                 => true,
			// 'filter'                  => true,
			'inputType'               => 'text',
			// eo: Ref: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			'flag'                    => 8,
			'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'infotext' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['infotext'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			// eo: eigene angepasste tinyMCE Version via Eintrag IN dcaconfig.php SOWIE eigener tinyMCE_custom.php in system/config
			//'eval'                    => array('rte'=>'tinyMCE'),
			'eval'                    => array('rte'=>'tinyMCEeoinfo'),
			'sql'                     => "text NULL"
		),
		/********
		// eo: s.o. wg. Enclosure-Ansatz
		'addEnclosure' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['addEnclosure'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'enclosure' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['enclosure'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'filesOnly'=>false, 'isDownloads'=>true, 'extensions'=>Config::get('allowedDownload'), 'mandatory'=>true),
			'sql'                     => "blob NULL"
		),
		********/
		// eo: Dateianlagen ermöglichen wie bei CE downloads:
		'multiSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['multiSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			//'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderSRC', 'files'=>true, 'mandatory'=>false),
			'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>false),
			'sql'                     => "blob NULL",
			'load_callback' => array
			(
				array('tl_eo_be_eoinfos', 'setMultiSrcFlags')
			)
		),
		// eo: Bedarf an diesem Feld ergibt sich aus multSRC-Array, darin "'orderField'=>'orderSRC'" im Abschnitt eval()
		//     rausgenommen, da Sortierung der EO-Anlage per Nr. im Dateinamen
		//     und außerdem der dominante Hinweis auf Sortierbarkeit stört
		/*'orderSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['orderSRC'],
			'sql'                     => "blob NULL"
		),*/
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'toggle'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		),
		/* wird nicht verwendet ...
		'previewlink' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['previewlink'],
			'exclude'                 => true,
			//'filter'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('style' => 'width: 100%'),
			'input_field_callback'    => array('tl_eo_be_eoinfos', 'createPreviewLink'),
			//'sql'                     => "varchar(255) NOT NULL default ''"
		),
		*/
		/* wird nicht verwendet ...
		'pdflink' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['pdflink'],
			'exclude'                 => true,
			//'filter'                  => true,
			'inputType'               => 'text',
			//'eval'                    =>  array('tl_class'=>'w50'), //array('style' => 'width: 100%'),
			'input_field_callback'    => array('tl_eo_be_eoinfos', 'createPdfLink'),
			//'sql'                     => "varchar(255) NOT NULL default ''"
		),
		*/
		/* wird nicht verwendet ...
		'pdflink2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['pdflink2'],
			'exclude'                 => true,
			//'filter'                  => true,
			'inputType'               => 'text',
			//'eval'                    =>  array('tl_class'=>'w50'), //array('style' => 'width: 100%'),
			'input_field_callback'    => array('tl_eo_be_eoinfos', 'createPdfLink2'),
			//'sql'                     => "varchar(255) NOT NULL default ''"
		),
		*/
		'generatepdf1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['generatepdf1'],
			'exclude'                 => false,
			//'filter'                  => true,
			'eval'                    => array('submitOnChange'=>false),
			'save_callback'           => array(array('tl_eo_be_eoinfos', 'createPdf1')),
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		),
		/* wird nicht verwendet ...
		'generatepdf2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['generatepdf2'],
			'exclude'                 => false,
			//'filter'                  => true,
			'eval'                    => array('submitOnChange'=>false),
			'save_callback'           => array(array('tl_eo_be_eoinfos', 'createPdf2')),
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		),
		*/
		'pdfdatum' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['pdfdatum'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: Ref: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			'flag'                    => 8,
			'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'wizard w510'),
			'sql'                     => "int(10) unsigned NULL"
		),
		// eo: Ref: nur Text erzeugen, braucht kein reelles DB-Feld
		'msg_versstatus' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['msg_versstatus'],
			'exclude'                 => true,
			//'filter'                  => true,
			'inputType'               => 'text',
			//'eval'                    =>  array('tl_class'=>'w50'), //array('style' => 'width: 100%'),
			'input_field_callback'    => array('tl_eo_be_eoinfos', 'createMsgVersStatus'),
		),
		// eo: checkbox für Angaben zum Versand (wg. __selector__)
		'vers_einst' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_einst'],
			'exclude'                 => true,
			//'load_callback'           => array(array('tl_eo_be_eoinfos', 'checkVersStatus')),
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		/* vers_themen nach oben gesetzt wg. Einbau in Teaser */
		// 'vers_themen' => array
		// (
			// 'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_themen'],
			// 'exclude'                 => true,
			// 'inputType'               => 'checkbox',
			// 'foreignKey'              => 'tl_eo_be_themen.title',
			// // 'eval'                    => array('mandatory'=>true, 'multiple'=> true, 'csv'=>',', 'tl_class'=>'w50 clr'), // , 'submitOnChange'=>true
			// 'eval'                    => array('mandatory'=>true, 'multiple'=> true, 'tl_class'=>'w50 clr'), // , 'submitOnChange'=>true
			// 'sql'                     => "text NULL"
		// ),
		'vers_empfgrp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_empfgrp'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			//'options'                 => array(9 => 'LG-Landkreise', 12 => 'WE-Landkreise/kreisfr. Städte', 8 => 'EO-Küstenlandkreise', 10 => 'MCON Regionalmanager', 11 => 'Testverteiler'),
			'options'                 => array(9 => 'LG-Landkreise', 12 => 'WE-Landkreise/kreisfr. Städte', 16 => 'GRW-Landkreise/kreisfr. Städte', 8 => 'EO-Küstenlandkreise', 10 => 'MCON Regionalmanager', 11 => 'Testverteiler'),
			// eo: Ref: value = label
			//'options'                 => array(9, 7, 13),
			// eo: Ref: Ausgabe aller Gruppen
			//'foreignKey'              => 'tl_member_group.name',
			// eo: Ref: checkbox selected Vorauswahl (WE & LG)
			'default'                 => array(),  // war 9, 12, 10 
			'eval'                    => array('mandatory'=>true,'multiple'=> true, 'tl_class'=>'w50 clr'), // , 'submitOnChange'=>true
			'sql'                     => "varchar(200) NOT NULL default ''"
		),
		'vers_anlagen' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_anlagen'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			//'options_callback'   => array('tl_eo_be_eoinfos', 'getListeVersAnlagen'),
			// eo: Ref: callback mit closure, erlaubt Übergabe von Parametern
			// PHP 8.3 Fix: $objDC ist in Closure-Kontext nicht verfügbar
			/*'options_callback' => function(){
				// getEOPrgArray: ohne Param alle, mit entspr. ausgewählt 
				// eo: ToDo: bei Bedarf alle zurückgeben lassen, gruppiert nach Status
				return EoBeKlasseGetSelects::getEoInfoAnlagen($objDC->id);
			},*/
			'options_callback'   => array('tl_eo_be_eoinfos', 'getListeVersAnlagen'),
			'eval'      => array('multiple'=> true, 'csv'=>',', 'tl_class'=>'clr'), // , 'submitOnChange'=>true
			'sql'       => "text NULL"
		),
		'vers_zeit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_zeit'],
			'default'                 => time()+3600,
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			//'flag'                    => 8,
			'eval'                    => array('rgxp'=>'datim', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		// eo: checkbox für Vorbereitung zum Versand : klappt den nächsten Bereich aus und löst damit createVersTab aus
		'vers_prep' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['vers_prep'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// eo: Versandtabelle erstellen
		'create_verstab' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_eoinfos']['create_verstab'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('submitOnChange'=>true), //array('style' => 'width: 100%'),
			'input_field_callback'    => array('tl_eo_be_eoinfos', 'createVersTab')
		),
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_eo_be_eoinfos extends Backend
{
	/**
	 * Auto-generate the event alias if it has not been set yet
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = StringUtil::generateAlias($dc->activeRecord->title);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_eo_be_eoinfos WHERE alias=?")
								   ->execute($varValue);

		// Check whether the alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '-' . $dc->id;
		}

		return $varValue;
	}
	// eo: Ref: gehört noch zum Downloads-Element
	//     - zu CE download (also nur eine Datei) gehört die function setSingleSrcFlags (s. tl_content.php)
	// kann ggf. weg, scheinbar "nur" Sicherheitsabgleich mit erlaubten Dateitypen
	/**
	 * Dynamically add flags to the "multiSRC" field
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return mixed
	 */
	 // eo: Ref:
	public function setMultiSrcFlags($varValue, DataContainer $dc)
	{
		
		if ($dc->activeRecord)
		{
			switch ($dc->activeRecord->type)
			{
				case 'gallery':
					$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isGallery'] = true;
					$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('validImageTypes');
					break;

				case 'downloads':
					$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isDownloads'] = true;
					$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
					break;
			}
		}
		return $varValue;
	}
	// eo: Ref: mit dem "grünen Auge" toggeln Teil 2
	/**
     * Ändert das Aussehen des Toggle-Buttons.
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        // Check permissions
        $user = BackendUser::getInstance();
        if (!$user->isAdmin && !$user->hasAccess('tl_eo_be_eoinfos::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;id=' . $row['id'];
        
        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '" onclick="Backend.getScrollOffset();return AjaxRequest.toggleField(this,true)">' . Image::getHtml($icon, $label, 'data-icon="' . Image::getPath('visible.svg') . '" data-icon-disabled="' . Image::getPath('invisible.svg') . '" data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }
	
	// eo: Ref: mit dem "grünen Auge" toggeln Teil 3
	/**
	 * Toggle the visibility of an element
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnPublished)
	{
		// Check permissions to publish
		// PHP 8.3 Fix: $this->User durch BackendUser::getInstance() ersetzt
		$user = BackendUser::getInstance();
		if (!$user->isAdmin && !$user->hasAccess('tl_eo_be_eoinfos::published', 'alexf'))
		{
			// $this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_eo_be_eoinfos toggleVisibility', TL_ERROR);
			System::getContainer()->get('monolog.logger.contao')
				->log(
					LogLevel::ERROR,
					sprintf('Not enough permissions to show/hide record ID "%s"', $intId),
					['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
				);
			$this->redirect('contao/main.php?act=error');
		}

		$this->Versions::create('tl_eo_be_eoinfos', $intId);

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_eo_be_eoinfos']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_eo_be_eoinfos']['fields']['published']['save_callback'] as $callback)
			{
				// PHP 8.3 Fix: $this->import() durch direkte Klasseninstanziierung ersetzt
				// $this->import($callback[0]);
				// $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
				
				if (class_exists($callback[0])) {
					$callbackClass = new $callback[0]();
					if (method_exists($callbackClass, $callback[1])) {
						$blnPublished = $callbackClass->$callback[1]($blnPublished, $this);
					}
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_eo_be_eoinfos SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
			->execute($intId);
		$this->Versions::create('tl_eo_be_eoinfos', $intId);
	}
	
	// z.Zt. nicht verwendet
	public function createPreviewLink($objDC, $strLabel)
	{	
	// $eopreviewlink = '<div class="clr"><br /><a href="index.php/eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'" target="_blank" style="color:darkblue;">Vorschau dieser EO-Info '. $objDC->title .'</a> (vorher speichern und im Intranet anmelden)</divp>';
	$eopreviewlink = '<div class="clr"><br /><a href="eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'" target="_blank" style="color:darkblue;">Vorschau dieser EO-Info '. $objDC->title .'</a> (vorher speichern und im Intranet anmelden)</divp>';
	//$tescht .= '<pre>'.print_r($objDC).'</pre>';
	return $eopreviewlink;
	}
	// z.Zt. nicht verwendet
	public function createPdfLink($objDC)
	{	
	// $pdflink = '<div class="w501"><br /><a href="index.php/eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=allg" target="_blank" style="color:darkblue;">allg. PDF der EO-Info erzeugen</a> (vorher speichern, falls nicht eben schon ...)</div>';
	$pdflink = '<div class="w501"><br /><a href="eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=allg" target="_blank" style="color:darkblue;">allg. PDF der EO-Info erzeugen</a> (vorher speichern, falls nicht eben schon ...)</div>';
	return $pdflink;
	}
	// z.Zt. nicht verwendet
	public function createPdfLink2($objDC)
	{	
	// dump($objDC->id);
	// exit;
	// $pdflink2 = '<div class="w501"><br /><a href="index.php/eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=mail" target="_blank" style="color:darkblue;">PDF-Vorschau Versandversion</a> (dito)</div>';
	$pdflink2 = '<div class="w501"><br /><a href="eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=mail" target="_blank" style="color:darkblue;">PDF-Vorschau Versandversion</a> (dito)</div>';
	return $pdflink2;
	}
	
	public function createPdf1($a, $obj)
	{
	// dump($a);
	// dump($b);
	// dump($obj->id);
	// exit;
		EoBeKlasseEoinfoPdf::generateEoPdf($obj->id, 0, 'allg');
		EoBeKlasseEoinfoPdf::generateEoPdf($obj->id, 126, 'mail');
		// $pdflink2 = '<div class="w501"><br /><a href="index.php/eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=mail" target="_blank" style="color:darkblue;">PDF-Vorschau Versandversion</a> (dito)</div>';
		return;
	}
	// z.Zt. nicht verwendet
	public function createPdf2($a, $obj)
	{
	// dump($a);
	// dump($b);
	// dump($obj->id);
	// exit;
		EoBeKlasseEoinfoPdf::generateEoPdf($obj->id, 126, 'mail');
		// $pdflink2 = '<div class="w501"><br /><a href="index.php/eo-cms-info-anzeigen.html?eoidneu='. $objDC->id .'&pdf_pv=mail" target="_blank" style="color:darkblue;">PDF-Vorschau Versandversion</a> (dito)</div>';
		return;
	}
	
	public function copyPrgSchlw($eoid, $objDC)
	{
		if ($eoid > 0) {
			$result = $this->Database->prepare("SELECT programme, schlagworte FROM tl_eo_be_eoinfos WHERE id=?")->execute($eoid); 
			$db_erg = $result->fetchAllAssoc();
			$zuordnung = $db_erg[0];
			//echo "<pre>". print_r($zuordnung)."</pre>";
			$this->Database->prepare("UPDATE tl_eo_be_eoinfos SET programme=?, schlagworte=? WHERE id=?")->execute($zuordnung['programme'],$zuordnung['schlagworte'],$objDC->id);
		}
		return;
	}

	//
	public function createMsgVersStatus($objDC)
	{	
		$result = $this->Database->prepare('SELECT DISTINCT status FROM tl_eo_be_versand WHERE eoid = ?')->execute($objDC->id); 

		if ($result->numRows > 0) {
			if ($result->status == "v") {
				$msg = '<div style="margin: 1rem 0.9rem;"><img src="files/themes/accept.png">&nbsp;Diese EO-Info wurde schon versendet.</div>';
				return $msg;
				exit;
			} else if ($result->status == "w") {
				$msg = '<div><br /><br>Versandtabelle wurde schon erstellt ...</div>';
				return $msg;
				exit;
			}
			return;
		}
	}
	/*public function checkVersStatus($objDC)
	{	
	//exit;
		$result = $this->Database->prepare('SELECT DISTINCT status FROM tl_eo_be_versand WHERE eoid = ?')->execute($objDC->id); 

		if ($result->numRows > 0) {
			$vers_einst = "";
			return $vers_einst;
		}

	} */
	
	public function getListeVersAnlagen(\Contao\DataContainer $dc): array
	{	
		// eo: Einfache ID-Ermittlung wie in der funktionierenden alten Version
		$eoid = $dc->id ?? null;
		
		if (!$eoid) {
			return array(); // Keine ID verfügbar
		}
		
		// eo: Statischer Aufruf da die Methode als static definiert ist
		$infoanlagen = EoBeKlasseGetSelects::getEoInfoAnlagen($eoid); 
		
		// eo: Original-Code aus der funktionierenden Version wieder aktivieren
		$versandanlagen = array(); // Array initialisieren

		// echo '<pre>infoanlagen: ' . print_r($infoanlagen, true);
		// exit;

		foreach ($infoanlagen as $art => $arten) {
			// Überprüfen, ob die Art nicht "Euro-Office-Info PDF-Version" ist
			if ($art !== "Euro-Office-Info PDF-Version") {
				foreach ($arten as $datei) {
					$versandanlagen[$datei] = $datei;
				}
			}
		}
		
		return $versandanlagen;
	}
	/**
	// Vorbereitung des Versands der EO-Info
	// - Eintrag der betroffenen Direktempfänger und deren Mailadressen sowie
	//   der betroffenen Empfänger gem. Verteiler
	// - Vorbereitung Protokollmails
	*/
	// eo: Ref: "$objDC->activeRecord->vers_empfgrp" liefert Feldwerte (serialisiert) von vers_empfgrp (nach speichern)
	public function createVersTab($objDC)
	{
		// die ausgewählten Empfängergruppen als Array
		$groups_tmp = StringUtil::deserialize($objDC->activeRecord->vers_empfgrp);
		// die ausgewählten Themen als Array
		// eo: Ref: deserialize() möglich, obwohl die Themen(nummern) als csv in DB eingetragen sind
		$themen_tmp = StringUtil::deserialize($objDC->activeRecord->vers_themen);
		
		// falls checkbox "Versand vorbereiten & aktivieren" vor den Versand-Einstellungen bzw. vorm Speichern gesetzt wird ==========
		if (empty($groups_tmp) || empty($themen_tmp)) {
			$msg = "<p><br>Ohne Auswahl von Empfänger(gruppen) <u>und</u> Themen keine Versandtabelle ...</p>";
			return $msg;
			exit;
		}
		
		// prüfen, ob zur EO-Info schon Einträge in der Versandtabelle existieren ====================================================
		$result = $this->Database->prepare('SELECT DISTINCT status FROM tl_eo_be_versand WHERE eoid = ?')->execute($objDC->id); 

		if ($result->numRows > 0) {
			$this->Database->prepare("UPDATE tl_eo_be_eoinfos SET vers_einst = '', vers_prep = '' WHERE id = ?")->execute($objDC->id); 
			if ($result->status == "v") {
				$msg = "<div style='margin: 1rem 0.9rem;'><br>Die EO-Info wurde schon versendet, keine Versandtabelle erstellt.</div>";
				return $msg;
				exit;
			} else if ($result->status == "w") {
				$msg = '<p><br>Versandtabelle wurde schon erstellt, bei Bedarf unter "Versand" manuell löschen.<br>(Ggf. auch die schon erstellten individuellen PDF-Versionen der EO-Info via Dateiverwaltung.)</p>';
				return $msg;
				exit;
			}
		}
		
		// Zusammenstellung der Direktempfänger ======================================================================================
		
		// PHP 8.3 Fix: Variable $vers_aufteiler muss initialisiert werden
		$vers_aufteiler = 0;
		
		// die Members (Gruppenmitglieder) ermitteln, die Direktempfänger sind (nur bei Direktempf. sind auch die notwendigen Daten eingetragen (Pflichtfelder))
		foreach($groups_tmp as $key => $group_nr) {
			// PHP 8.3/MySQL Fix: 'groups' ist ein reserviertes Wort in MySQL und muss in Backticks eingeschlossen werden
			$sql = 'SELECT id, company, mail_adr, mail_adrcc, mail_adrbcc FROM tl_member WHERE mail_direktempf = 1 AND `groups` LIKE \'%"'.$group_nr.'"%\' ORDER BY company'; //LIKE "%'.$group_nr.'%"';
			//echo '<p>'.$sql;
			//exit;
			//$result = $this->Database->prepare($sql)->execute(); 
			$result = $this->Database->query($sql); 
			while ($result->next()) {
				$member_ids[] = $result->id;
				$company[$result->id] = $result->company;
				$mail_adr[$result->id] = $result->mail_adr;
				$mail_adrcc[$result->id] = $result->mail_adrcc;
				$mail_adrbcc[$result->id] = $result->mail_adrbcc;
				/*if ($result->vert_maillog == 1) {
					$member_vertlog_ids[] = $result->id;
				}*/
			}	
		}
			$member_ids = array_unique($member_ids);
			//sort($member_ids);
			
			$retText = "<p><br><b>Folgende Direkt-Empfänger wurden zum Versand vorbereitet:</b></p>";
			$retText .= "<ul>";
			foreach ($member_ids as $member_id) {
				unset($sql);
				// nur wenn Empf.mailadresse vorliegt ...
				if (!empty($mail_adr[$member_id])) {
					// eo: Array mit User-ID (=Autor-ID) und entspr. Name/Mail für Versender der EO-Info
					$objUserName = new EoBeKlasseGetSelects();
					$UserName_arr = $objUserName->getUserNameArray(); 
				
					$sql = 'INSERT INTO tl_eo_be_versand (tstamp, title, status, mail_art, vers_zeit, eoid, member_id, author, sender_adr, company, mail_adr, mail_adrcc, mail_adrbcc, vers_anlagen, vers_themen, published) ';
					// tstamp, title, status, mail_art, vers_zeit, eoid
					$sql .= 'VALUES ('.time().',"'.StringUtil::specialchars($objDC->activeRecord->title).'","w","d","'.$objDC->activeRecord->vers_zeit.'",'.$objDC->id.', ';
					// member_id, author, sender_adr, company
					// eo: Name Autor im Klartext eintragen lassen
					$sql .= $member_id.',"'.$UserName_arr[$objDC->activeRecord->author]['name'].'","'.$UserName_arr[$objDC->activeRecord->author]['email'].'","'.$company[$member_id].'", "';
					// mail_adr, mail_adrcc, mail_adrbcc
					$sql .= $mail_adr[$member_id].'","'.$mail_adrcc[$member_id].'","'.$mail_adrbcc[$member_id].'", "';
					// vers_anlagen, vers_themen, published (damit einzelne Empf. ausgetoggelt werden können)
					// $sql .= implode(",", $objDC->activeRecord->vers_anlagen).',files/EO-Intranet/EO-Infos/EO-PDFs_Versandversionen/eoinfo_ot-'.$objDC->id.'-'.$member_id.'.pdf","'.implode(",", $themen_tmp).'",1)';
					// eo: Ref: auf serialize($themen_tmp) anstatt csv, da sonst in der Infobox im BE nur das erste Thema anzgezeigt wird
					//          \' anstatt " für SQL-string dabei nehmen
					// falls ohne Vers.anlagen ...
					if ($objDC->activeRecord->vers_anlagen <> "") {
						$vers_eoanlagen = implode(",", $objDC->activeRecord->vers_anlagen);
					} else {
						$vers_eoanlagen = '';
					}
					// $sql .= implode(",", $objDC->activeRecord->vers_anlagen).',files/EO-Intranet/EO-CMS_Infos/EO-PDFs_Versandversionen/eoinfo_ot-'.$objDC->id.'-'.$member_id.'.pdf",\''.serialize($themen_tmp).'\',1)';
					$sql .= $vers_eoanlagen .',files/EO-Intranet/EO-CMS_Infos/EO-PDFs_Versandversionen/eoinfo_ot-'.$objDC->id.'-'.$member_id.'.pdf",\''.serialize($themen_tmp).'\',1)';
					// $result = $this->Database->prepare($sql)->execute(); 
					$result = $this->Database->query($sql);

					// im Dateinamen der individuellen EO-Info-PDF eo-id und member-id mit eintragen
					$pfad_name = TL_ROOT . '/files/EO-Intranet/EO-CMS_Infos/EO-PDFs_Versandversionen/eoinfo_ot-'.$objDC->id.'-'.$member_id.'.pdf';
					// wenn PDF schon vorhanden, nicht neu erstellen (können/sollen manuell mit der Dateiverwaltung gelöscht werden)
					if (!file_exists($pfad_name)) {
						// EoFeKlasseEoinfoPdf::generateEoPdf($objDC->id, $member_id, "eopdf");
						EoBeKlasseEoinfoPdf::generateEoPdf($objDC->id, $member_id);
					} 
					$retText .= '<li style="list-style:circle; margin-left:12px;">'.$company[$member_id]." &rArr; ".$mail_adr[$member_id]."</li>";
				}
			}
			$retText .= "</ul>";
			
			$retText .= "<p><br><b>Folgende Verteiler-Empfänger wurden vorbereitet:</b></p>";
			$retText .= "<ul>";
			
			
			// Zusammenstellung der Verteilerempfänger ===============================================================================
			
			// Verteilerempf. nur für die gewählten Direktempfänger-Gruppen zusammenstellen
			
			$company = array_unique($company);
			foreach ($company as $ckey => $sqlcompany) {

				// die Verteilerempfänger der einzelnen Themen und LK/SK/... ermitteln
				foreach($themen_tmp as $key => $themen_nr) {
					/* Muster: SELECT mail FROM tl_eo_be_vertadr WHERE geloescht != 1 AND company = "Landkreis Osnabrück" AND themen LIKE '%"12%' ORDER BY name, mail */
					
					// Eintrag bis Berücksichtigung der Ausgeblendeten (published 1 bzw. '')
					// $result = $this->Database->prepare('SELECT mail FROM tl_eo_be_vertadr WHERE geloescht != 1 AND company = ? AND themen LIKE ? ORDER BY name, mail')->execute($sqlcompany, '%"'.$themen_nr.'"%');
					
					$result = $this->Database->prepare('SELECT mail FROM tl_eo_be_vertadr WHERE published = 1 AND geloescht != 1 AND company = ? AND themen LIKE ? ORDER BY name, mail')->execute($sqlcompany, '%"'.$themen_nr.'"%'); 
					while ($result->next()) {
						//$vert_companyempf_tmp[$result->company][] = $result->mail;
						$vert_companyempf_tmp[$sqlcompany][] = $result->mail;
					}
				}
			}
				if (!is_null($vert_companyempf_tmp)) {
					// doppelte Einträge pro LK/SK/... entfernen
					foreach ($vert_companyempf_tmp as $comp => $cadr_arr) {
						$cadr_arr = array_unique($cadr_arr);
						$vert_companyempf[$comp] = $cadr_arr;
					}
					
					foreach ($vert_companyempf as $comp_lfd => $cadr_arr) {

						// eo: die Angaben des Verteilerabsenders aus dem Mitliederbereich abfragen
						$sql = 'SELECT id, company, mail_globempf, mail_abs, vert_verzh, mail_replyto, vert_maillog FROM tl_member WHERE mail_verteilerabs = 1 AND company = "'.$comp_lfd.'"';
						$result = $this->Database->prepare($sql)->limit(1)->execute();
						
						// nur wenn es einen Verteilerabsender (mit einer Member-ID) gibt ...
						if ($result->id >0) {
							// eo: sender_adr oben Mailadr. des Autors, hier mail_abs
							$vert_sql = 'INSERT INTO tl_eo_be_versand (tstamp, title, status, mail_art, vers_zeit, eoid, member_id, author, sender_adr, company, mail_adr, mail_adrcc, mail_adrbcc, vers_anlagen, vers_themen, published) ';
							$logmail_sql = $vert_sql;
							
							// tstamp, title, status, mail_art, vers_zeit, eoid
							// $versZeit_vert = $objDC->activeRecord->vers_zeit + $result->vert_verzh * 60 * 60;
							$versZeit_vert = $objDC->activeRecord->vers_zeit + $result->vert_verzh * 60 * 60 + $vers_aufteiler;
							$vert_sql .= 'VALUES ('.time().',"'.StringUtil::specialchars($objDC->activeRecord->title).'","w","v","'.$versZeit_vert.'",'.$objDC->id.', ';
							// mit "p" in mail_art
							$logmail_sql .= 'VALUES ('.time().',"'.StringUtil::specialchars($objDC->activeRecord->title).'","w","p","'.$versZeit_vert.'",'.$objDC->id.', ';
							
							// member_id, author, sender_adr, company
							// eo: Name Autor im Klartext eintragen lassen
							$vert_sql .= $result->id.',"'.$UserName_arr[$objDC->activeRecord->author]['name'].'","'.$result->mail_abs.'","'.$result->company.'", "';
							// Abs. der Protokollmail ist das Verteilersystem
							$logmail_sql .= $result->id.',"'.$UserName_arr[$objDC->activeRecord->author]['name'].'","verteiler@eurooffice.de","'.$result->company.'", "';
							
							// mail_adr, mail_adrcc, mail_adrbcc
							$vert_sql .= $result->mail_globempf.'","","'.implode(",",$cadr_arr).'", "';
							// eo: Protokollmail in cc an schepke@eurooffice.de
							//     in bcc bleiben die Vert.empf, diese werden für die Protokollmail aus diesem Feld ausgelesen
							// Erweiterung um Berücksichtigung, ob MailLog gewünscht ist:
							// $logmail_sql .= $result->mail_replyto.'","schepke@eurooffice.de","'.implode(",",$cadr_arr).'", "';
							if ($result->vert_maillog == "1") {
								$logmail_sql .= $result->mail_replyto.'","schepke@eurooffice.de","'.implode(",",$cadr_arr).'", "';
							} else {
								$logmail_sql .= 'schepke@eurooffice.de","","'.implode(",",$cadr_arr).'", "';
							}
							
							// vers_anlagen, vers_themen, published (damit einzelne Empf. ausgetoggelt werden können)
							// falls ohne Vers.anlagen ...
							if ($objDC->activeRecord->vers_anlagen <> "") {
								$vers_eoanlagen = implode(",", $objDC->activeRecord->vers_anlagen);
							} else {
								$vers_eoanlagen = '';
							}
							$vert_sql .= $vers_eoanlagen.',files/EO-Intranet/EO-CMS_Infos/EO-PDFs_Versandversionen/eoinfo_ot-'.$objDC->id.'-'.$result->id.'.pdf",\''.serialize($themen_tmp).'\',1)';
							$logmail_sql .= '",\''.serialize($themen_tmp).'\',1)';
							// echo "<pre>".$logmail_sql."</pre>";
							// exit;
							//$result = $this->Database->prepare($sql)->execute();
							$result = $this->Database->query($vert_sql);
							$result = $this->Database->query($logmail_sql);

							//$retText .= '<li style="list-style:circle; margin-left:12px;">'.$comp_lfd." &rArr; ".implode(",",$cadr_arr)."</li>";
							$retText .= '<li style="list-style:circle; margin-left:12px;">'.$comp_lfd." &rArr; ".count($cadr_arr)." Empfänger</li>";
							//$retText .= "<li>{{link::$pfad_name}}</li>";
							//EoFeKlasseEoinfoPdf::generateEoPdf($objDC->id, $member_id);
							
							// eo: ToDo: ggf. individuelles PDF erstellen lassen, falls Vert.versender != Direktempfänger !!!
						}
						// Versand dauert z.T. länger als 10 Minuten, durch Überschneidung mit nächstem Auslöser, gehen dann Mails dann doppelt raus
						// zw. Verteiler-Mails 2 Minuten Abstand ...
						$vers_aufteiler = $vers_aufteiler + 120;
					}
				} else {
					$retText .= '<p><br><span style="color:red;">Es konnten keine passenden Empfänger zu den gewählten Themen ermittelt werden.</span></p>';
				}
			
			$retText .= "</ul>";
			
			// noch eine ToDo-Erinnerung einblenden ... :
			$retText .= '<div style="background-color: yellow; padding-left: 1rem; margin: 1rem 1rem;">';
			$retText .= '<p>&nbsp;</p><p><b>Noch zur Erinnerung, bitte:</b></p><ul>';
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">Programmunterlagen ablegen' . "</li>";
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">WORD-Datei der EO-Info ablegen' . "</li>";
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">Ggf. Fristen eintragen?' . "</li>";
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">Ggf. RL-Folie aktualisieren?' . "</li>";
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">Ggf. Übersicht anpassen oder Notiz im Ordner abspeichern? <br>(O:&#92;Euro-Office-Infodienst&#92;JAHR&#92;F&uuml;r Aktualisierung Förder&uuml;bersichten)' . "</li>";
			$retText .= '<li style="margin-left:1rem; list-style-type: square;">Verlinkung zu Euro-Office-Infos einfügen' . "</li>";
			$retText .= "</ul><p>&nbsp;</p>";
			$retText .= '</div>';
			
			// eo: Häckchen für die Versandeinstellungen raus nehmen, damit eingeklappt, wenn EO-Info-BE-Formular noch einmal aufgerufen wird
			$this->Database->prepare("UPDATE tl_eo_be_eoinfos SET vers_einst = '', vers_prep = '' WHERE id = ?")->execute($objDC->id); 
			
			// $retText enthält nun Auflistung der Direktempfänger sowie die Verteilerempfänger
			return $retText;
		
		return $tescht;
	}
}
