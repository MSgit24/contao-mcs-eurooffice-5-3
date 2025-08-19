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

use Contao\Backend;
use Contao\Image;
use Contao\Versions;
use Contao\StringUtil; // eo: für sicheres Escaping (ersetzt deprecated specialchars())
use Contao\System;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Contao\BackendUser;
use Contao\DC_Table;


/**
 * Table tl_eo_be_vertadr
 */
$GLOBALS['TL_DCA']['tl_eo_be_vertadr'] = array(

	// Config
	'config' => array(
		'dataContainer'               => DC_Table::class,
		'enableVersioning'            => true,
		'sql' => array(
			'keys' => array(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array(
		'sorting' => array(
			'mode'                    => 1,
			'fields'                  => array('company', 'name'),
			'flag'                    => 11,
			'panelLayout'             => 'filter;search,limit',
			// eo: Ref: Auflistung im Backend filtern
			// eo: nur die nicht auf "gelöscht" gesetzten Emmpfänger anzeigen
			'filter' => array(
				array('geloescht != ?', '1')
			)
		),
		'label' => array(
			'fields'                  => array('mail', 'name', 'comment'),
			'format'                  => '%s <span style="color:gray;">[%s]</span> %s'
		),
		'global_operations' => array(
			'all' => array(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array(
			'edit' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_themen']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			// eo: Ref: mit dem "grünen Auge" toggeln Teil 1
			'toggle' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['toggle'],
				'icon'                => 'visible.svg',
				'href'                => 'act=toggle&amp;field=published',
				'reverse'             => true,
				'button_callback'     => array('tl_eo_be_vertadr', 'toggleIcon')
			)
		)
	),

	// Select
	'select' => array(
		'buttons_callback' => array()
	),

	// Edit
	'edit' => array(
		'buttons_callback' => array()
	),

	// Palettes
	'palettes' => array(
		'__selector__'                => array(''),
		'default'                     => '{title_legend},company,mail,name,firstname,institution,comment,themen,published,published_comment;'
	),

	// Subpalettes
	'subpalettes' => array(
		''                            => ''
	),

	// Fields
	'fields' => array(
		'id' => array(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'mail' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['mail'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'name' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength' => 35),
			'sql'                     => "varchar(35) NOT NULL default 'N.N.'"
		),
		'firstname' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength' => 35),
			'sql'                     => "varchar(35) NOT NULL default ''"
		),
		'comment' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['comment'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength' => 255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'themen' => array(
			'label'					  => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['themen'],
			'exclude'				  => true,
			'inputType'				  => 'checkbox',
			// eo: Ref: alle Einträge aus einer Tab. mit foreignKey (Spalte angeben, in denen die Beschr. ist, id wird autom. eingesetzt)
			'foreignKey'			  => 'tl_eo_be_themen.title',
			'eval'					  => array('mandatory' => true, 'multiple' => true),
			'sql'                     => "text NULL"
		),
		'company' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['company'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory' => true, 'maxlength' => 255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'institution' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['institution'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory' => false, 'maxlength' => 255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		// eo: kein SQL-replace oder -update möglich, daher muss der urspr. Eintrag gelöscht werden
		//     beim Speichern wird wird ein save-callback/Hook ausgelöst, diesem muss übermittelt werden
		//     welcher Vorgängereintrag gelöscht werden muss, dieser Wert kommt in delete_id
		'delete_id' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['delete_id'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			//'eval'                    => array('mandatory'=>true, 'rgxp'=>'email', 'maxlength'=>255),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'geloescht' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['geloescht'],
			'exclude'                 => true,
			//'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// 20210315
		'published' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'toggle'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'published_comment' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_vertadr']['published_comment'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength' => 255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		)
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_eo_be_vertadr extends Backend
{

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
		if (!$user->isAdmin && !$user->hasAccess('tl_eo_be_vertadr::published', 'alexf')) {
			return '';
		}

		$href .= '&amp;id=' . $row['id'];
		
		if (!$row['published']) {
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
		$this->import('Versions');
        // Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_eo_be_vertadr::published', 'alexf')) {
            System::getContainer()->get('monolog.logger.contao')
                ->log(
                    LogLevel::ERROR,
                    sprintf('Not enough permissions to show/hide record ID "%s"', $intId),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );
			$this->redirect('contao/main.php?act=error');
		}

		$this->Versions->create('tl_eo_be_vertadr', $intId);

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_eo_be_vertadr']['fields']['published']['save_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_eo_be_vertadr']['fields']['published']['save_callback'] as $callback) {
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_eo_be_vertadr SET tstamp=" . time() . ", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
			->execute($intId);
		$this->Versions->create('tl_eo_be_vertadr', $intId);
	}
}
