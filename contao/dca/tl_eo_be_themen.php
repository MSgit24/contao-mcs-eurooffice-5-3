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

use Contao\DataContainer;
use Contao\DC_Table;

// use Contao\Backend;
// use Contao\DataContainer;
// use Contao\DC_Table;
// use Contao\Input;
// use Contao\Config;
 

/**
 * Table tl_eo_be_themen
 */
$GLOBALS['TL_DCA']['tl_eo_be_themen'] = array
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
			'panelLayout'             => 'search'
		),
		'label' => array
		(
			'fields'                  => array('id','title','kuerzel'),
			'format'                  => '%s: %s <span style="color:gray;">[%s]</span>'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			/*'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),*/
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['show'],
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
		'default'                     => '{title_legend},title,abkrz,kuerzel;'
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
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['title'],
			'exclude'                 => true,
			// eo: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>60, 'unique'=>true),
			'sql'                     => "varchar(60) NOT NULL default ''"
		),
		'abkrz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['abkrz'],
			'exclude'                 => true,
			// eo: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>20, 'unique'=>true),
			'sql'                     => "varchar(20) NOT NULL default ''"
		),
		'kuerzel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['kuerzel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>15, 'unique'=>true),
			'sql'                     => "varchar(15) NOT NULL default ''"
		)
	)
);
