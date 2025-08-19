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

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;

/**
 * Table tl_eo_be_programme
 */
$GLOBALS['TL_DCA']['tl_eo_be_programme'] = array
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
			'mode'                    => 2,
			'fields'                  => array('title'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('title','status'),
			'format'                  => '%s <span style="color:gray;">[%s]</span>',
			// eo: Ref: mit showColumns Spalten erzeugen
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
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			/*'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),*/
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
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
		'__selector__'                => array(''),
		'default'                     => '{title_legend},title,status,beschr;'
		// eo: Ref: Möglichkeit, einfachen Text mit unterzbringen Teil 1
		//'default'                     => '{anleitung_legend},anleitung;{title_legend},title,status,beschr;'
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
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// eo: Ref: Möglichkeit, einfachen Text mit unterzbringen Teil 2
		//     gut: ohne sql keine DB Erweiterung
		/* 'anleitung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['anleitung'],
			'inputType'               => 'text',
			'input_field_callback'    => array('tl_eo_be_programme', 'createTextAnleitung')
		), */
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['title'],
			'exclude'                 => true,
			// eo: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['status'],
			'exclude'                 => true,
			// eo: 'filter auf true, sonst wird das im Panel nicht angezeigt'
			'filter'                  => true,
			'inputType'               => 'radio',
			'options'                 => array('alt', 'aktuell','zukunft'),
			'eval'                    => array('mandatory'=>true,'multiple'=> false),
			'sql'                     => "varchar(10) NOT NULL default 'aktuell'"
		),
		'beschr' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_programme']['beschr'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>1000),
			'sql'                     => "text NULL"
		)
	)
	
	
);	

// eo: Ref: Möglichkeit, einfachen Text mit unterzubringen Teil 3
//     <div> wichtig, sonst klappen die Text nicht mit der Legende zusammen weg
// class tl_eo_be_programme extends Backend
// {
// 	public function createTextAnleitung()
// 	{	
// 	$text = '<div><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus.</p></div>';
	
// 	return $text;
// 	}
// }
