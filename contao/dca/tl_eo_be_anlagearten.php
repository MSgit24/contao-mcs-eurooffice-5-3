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
// use Contao\DC_Table;
// use Contao\Input;
// use Contao\Config;
use Contao\DC_Table;

/**
 * Table tl_eo_be_anlagearten
 */
$GLOBALS['TL_DCA']['tl_eo_be_anlagearten'] = array
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
			'fields'                  => array('title','kuerzel'),
			'format'                  => '%s <span style="color:gray;">[%s]</span>'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			/*'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),*/
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['show'],
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
		'default'                     => '{title_legend},title,kuerzel;'
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['title'],
			'exclude'                 => true,
			// eo: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>35),
			'sql'                     => "varchar(35) NOT NULL default ''"
		),
		'kuerzel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_anlagearten']['kuerzel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'unique'=>true),
			'sql'                     => "varchar(3) NOT NULL default ''"
		)
	)
);
