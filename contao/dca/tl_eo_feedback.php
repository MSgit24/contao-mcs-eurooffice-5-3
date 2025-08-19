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

// use Contao\Backend;
// use Contao\DataContainer;
// use Contao\StringUtil;
// use Contao\BackendUser;
// use Contao\Config;
// use Contao\Input;
// use Contao\Image;
// use Contao\System;
// use Contao\CoreBundle\Monolog\ContaoContext;
// use Psr\Log\LogLevel;
use Contao\DC_Table;
 
// // use eoModul\EoBeKlasseGetSelects;
// use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
// // use eoModul\EoFeKlasseEoinfoPdf;
// use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseEoinfoPdf;
 

/**
 * Table tl_eo_feedback
 */
$GLOBALS['TL_DCA']['tl_eo_feedback'] = array
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
			'fields'                  => array('company', 'title'),
			'flag'                    => 11,
			'panelLayout'             => 'filter;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('company','title'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_feedback']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			/* eo: Infos kopieren verhindern
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_feedback']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			), */
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_feedback']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_feedback']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
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
		'__selector__'                => array(''),
		'default'                     => '{title_legend},title,company,user_name,fb_wert;'
	),

	// Subpalettes
	'subpalettes' => array
	(
		''                            => ''
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment",
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			// eo: Ref: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'style' => 'width: 100%'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'eo_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['eo_id'],
			'exclude'                 => true,
			'sorting'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'natural', 'mandatory'=>true, 'doNotCopy'=>true), 
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'eo_tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['eo_tstamp'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			//'flag'                    => 8,
			'eval'                    => array('rgxp'=>'datim', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'user_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['user_id'],
			'exclude'                 => true,
			'sorting'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'natural', 'mandatory'=>true, 'doNotCopy'=>true), 
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'user_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['user_name'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>100),
			'sql'                     => "varchar(35) NOT NULL default 'N.N.'"
		),
		'user_company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['user_company'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'fb_wert' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['fb_wert'],
			'exclude'                 => true,
			'sorting'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'natural', 'mandatory'=>true, 'doNotCopy'=>true), 
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'fb_tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['fb_tstamp'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			// eo: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			//'flag'                    => 8,
			'eval'                    => array('rgxp'=>'datim', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'fb_src' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_feedback']['fb_src'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>5),
			'sql'                     => "varchar(5) NOT NULL default ''"
		),
	)
);

