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

use Contao\Backend;
use Contao\StringUtil;
use Contao\Image;
use Contao\System;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Contao\BackendUser;

use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
 
/**
 * Table tl_eo_be_versand
 */
$GLOBALS['TL_DCA']['tl_eo_be_versand'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		//'ptable'                      => 'tl_eo_be_eoinfos',
		//'ctable'                      => array('tl_member','tl_eo_be_themen'),
		// "Activates the 'save and edit' button when a new record is added (sorting mode 4 only)."
		//'switchToEdit'                => true,
		// eo: Einträge in Versandtabelle nicht manuell zulassen
		'notCreatable'                  => true,
		//'enableVersioning'            => true,
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
			'fields'                  => array('status','mail_art','vers_zeit','company'),
			'flag'                    => 11,
			'panelLayout'             => 'filter;sort;search,limit',
			//'headerFields'            => array('title','vers_zeit'),
		),
		'label' => array
		(
			'fields'                  => array('status','mail_art','company','vers_zeit','title'),
			'format'                  => '%s | %s | %s | %s <br> %s',
			'label_callback'          => array('tl_eo_be_versand', 'formatLabel') 
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
			/* eo: bearbeiten verhindern
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			), */
			/* eo: kopieren verhindern
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			), */
			/* 'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				//'button_callback'     => array('tl_eo_be_versand', 'deleteVersandSerie')
			),	*/
			'panik' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['panik'],
				//'href'                => 'table=tl_eo_be_versand&amp;key=deleteVersandSerie',
				// eo: Ref: key !!! anstatt act
				// eo: Ref: Methode muss in config.php eingetragen werden
				//          verweist dort auf EoBeKlasseEoOperations -> deleteVersandSerie
				'href'                => 'key=deleteVersandSerie',
				//'icon'                => 'system/modules/eurooffice/assets/time_delete.png',
				'attributes'          => 'onclick="if(!confirm(\'Versand dieser EO-Info verhindern!\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_eo_be_versand', 'buttonDeleteVersandSerie')
			), 
			// eo: Ref: mit dem "grünen Auge" toggeln Teil 1
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['toggle'],
				'icon'                => 'visible.svg',
				'href'                => 'act=toggle&amp;field=published',
				'reverse'             => true,
				'button_callback'     => array('tl_eo_be_versand', 'toggleIcon')
			),
			'delmail' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['delmail'],
				//'href'                => 'table=tl_eo_be_versand&amp;key=deleteVersandSerie',
				// eo: Ref: key !!! anstatt act
				// eo: Ref: Methode muss in config.php eingetragen werden
				//          verweist dort auf EoBeKlasseEoOperations -> deleteVersandSerie
				'href'                => 'key=deleteEinzelVersand',
				//'icon'                => 'system/modules/eurooffice/assets/time_delete.png',
				'attributes'          => 'onclick="if(!confirm(\'Versand dieser Mail verhindern!\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_eo_be_versand', 'buttonDeleteEinzelVersand')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'send' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['send'],
				//'href'                => 'table=tl_eo_be_versand&amp;key=deleteVersandSerie',
				'href'                => 'key=sendVersandSerie',
				//'icon'                => 'system/modules/eurooffice/assets/email_go.png',
				'attributes'          => 'onclick="if(!confirm(\'Versand aller Mails dieser EO-Info?\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_eo_be_versand', 'buttonSendVersandSerie')
			),
		)
	),

	/* Select
	'select' => array
	(
		'buttons_callback' => array()
	),*/

	/* Edit
	'edit' => array
	(
		'buttons_callback' => array()
	),*/
	

	// Palettes
	'palettes' => array
	(
		//'__selector__'                => array('vers_einst', 'vers_prep'),
		
		'default'                     => '{title_legend},title;{mail_legend:hide},status,mail_art,vers_zeit,eoid,member_id,company,mail_adr,mail_adrcc,mail_adrbcc,vers_anlagen,vers_themen;'
	), 

	// Subpalettes
	'subpalettes' => array
	(
		//'vers_prep'                   => 'create_verstab, create_eopdfs',
	),

	// Fields
	// eo: Tabelle wird nur über BE-Funktionen gespeist, nicht manuell, daher sind auch keine Übersetzungen angelegt
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
        /* 'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ), */
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['title'],
			'exclude'                 => true,
			// eo: 'search auf true, sonst wird das im Panel nicht angezeigt'
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['status'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 2,
			'options'                 => array('w', 'v'),
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "varchar(1) NOT NULL default ''"
		),
		'mail_art' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['mail_art'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'select',
			'options'                 => array('d', 'v'),
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "varchar(1) NOT NULL default ''"
		),
		'vers_zeit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['vers_zeit'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'sorting'                 => true,
			// eo: "flag" bewirkt lesbare Datumsausgabe sowie Gruppierung nach Monat (8) oder Jahr (10)
			'flag'                    => 6,
			'eval'                    => array('rgxp'=>'datim', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'eoid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL"
		),
		'member_id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL"
		),
		/*'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['author'],
			'filter'                  => true,
			'sorting'                 => true,
			//     ... 11 = auf-, 12 = absteigend
			'flag'                    => 11,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>true, 'includeBlankOption'=>true),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'hasOne', 'load'=>'eager')
		),*/
		'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['author'],
			'filter'                  => true,
			'sorting'                 => true,
			//     ... 11 = auf-, 12 = absteigend
			'flag'                    => 11,
			'inputType'               => 'text',
			//'foreignKey'              => 'tl_user.name',
			//'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>true, 'includeBlankOption'=>true),
			'sql'                     => "varchar(50) NOT NULL default ''",
		),
		'sender_adr' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['sender_adr'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'email', 'maxlength'=>255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['company'],
			'exclude'                 => true,
			'sorting'                 => true,
			'filter'                  => true,
			'inputType'               => 'text',
			// eo: Titelfeld über die komplette Breite: in eval 'style' => 'width: 100%'
			'eval'                    => array('mandatory'=>true, 'maxlength'=>50),
			'sql'                     => "varchar(50) NOT NULL default ''"
		),
		'mail_adr' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['mail_adr'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'emails', 'maxlength'=>255),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'mail_adrcc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['mail_adrcc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'emails'),
			'sql'                     => "text NULL"
		),
		'mail_adrbcc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['mail_adrbcc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'emails'),
			'sql'                     => "text NULL"
		),
		'vers_anlagen' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['vers_anlagen'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'options_callback' => function($objDC){
				return EoBeKlasseGetSelects::getEoInfoAnlagen($objDC->id);
			},
			// eo: hier nicht serialisiert ablegen, sondern als csv (u.a. wg. Hinzfügen von EO-Infos, etc.)
			'eval'      => array('multiple'=> true, 'csv'=>',', 'tl_class'=>'clr'), // , 'submitOnChange'=>true
			'sql'       => "text NULL"
		),
		'vers_themen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['vers_themen'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_eo_be_themen.title',
			'eval'                    => array('mandatory'=>true,'multiple'=> true), // , 'submitOnChange'=>true
			//'sql'                     => "varchar(200) NOT NULL default ''"
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['published'],
			'exclude'                 => true,
			'toggle'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		),
		/*'infotext' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_eo_be_versand']['infotext'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			// eo: eigene angepasste tinyMCE Version via Eintrag in dcaconfig.php sowie eigener tinyMCE_custom.php in system/config
			//'eval'                    => array('rte'=>'tinyMCE'),
			'sql'                     => "text NULL"
		)*/ 
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_eo_be_versand extends Backend
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
        if (!$user->isAdmin && !$user->hasAccess('tl_eo_be_versand::published', 'alexf'))
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
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_eo_be_versand::published', 'alexf'))
        {
            System::getContainer()->get('monolog.logger.contao')
                ->log(
                    LogLevel::ERROR,
                    sprintf('Not enough permissions to show/hide record ID "%s"', $intId),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );
            $this->redirect('contao/main.php?act=error');
        }

		$this->Versions::create('tl_eo_be_versand', $intId);

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_eo_be_versand']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_eo_be_versand']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_eo_be_versand SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
			->execute($intId);
		$this->Versions::create('tl_eo_be_versand', $intId);
	}
	
    public function buttonDeleteVersandSerie($objDC, $href, $label, $title, $icon, $attributes) 
	{
		//echo "<pre>".dump($objDC)."</pre>";
		//echo "<pre>".$objDC['status']."</pre>";
		
		if ($objDC['status'] == "v") {
			// eo: versendete bekommen o.k.-Icon
			// $icon = 'system/modules/eurooffice/assets/accept.png';
			// return '<span style="margin-right:2px;">'.$this->generateImage($icon, $label).'</span>';
			return '<span style="margin-right:2px;">'.Image::getHtml('files/themes/accept.png', $label, 'style="width:14px;height:14px;"').'</span>';
		} else {
			// eo: nur wartende lassen sich löschen
			 // $icon = 'system/modules/eurooffice/assets/cancel.png';
			 // return '<span style="margin-right:4px;"><a href="'.$this->addToUrl($href).'&key=deleteVersandSerie&eoid='.$objDC['eoid'].'" title="Panik: kompl. Versandreihe löschen"'.$attributes.'>'.$this->generateImage($icon, $label).'</a></span>';
			return '<span style="margin-right:4px;"><a href="'.$this->addToUrl($href).'&key=deleteVersandSerie&eoid='.$objDC['eoid'].'" title="Panik: kompl. Versandreihe löschen"'.$attributes.'>'.Image::getHtml('files/themes/cancel.png', $label, 'style="width:14px;height:14px;"').'</a></span>';
		}
	}
	
    public function buttonDeleteEinzelVersand($objDC, $href, $label, $title, $icon, $attributes) 
	{
		//echo "<pre>".dump($objDC)."</pre>";
		//echo "<pre>".$objDC['status']."</pre>";
		
		if ($objDC['status'] == "v") {
			// eo: versendete bekommen kein Icon
			return '';
		} else {
			// eo: nur wartende lassen sich löschen
			// $icon = 'system/modules/eurooffice/assets/email_delete.png';
			return '<span style="margin-right:4px;"><a href="'.$this->addToUrl($href).'&key=deleteEinzelVersand&vers_id='.$objDC['id'].'" title="Mail an diesen Empfänger löschen"'.$attributes.'>'.Image::getHtml('files/themes/email_delete.png', $label, 'style="width:14px;height:14px;"').'</a></span>';
		}
	}
	
	//
    public function buttonSendVersandSerie($objDC, $href, $label, $title, $icon, $attributes) 
	{
		//echo "<pre>".dump($objDC)."</pre>";
		//echo "<pre>".$objDC['status']."</pre>";
		
		if ($objDC['status'] == "v") {
			// eo: versendete bekommen kein Icon
			return '';
		} else {
			// eo: nur wartende Direktmails lassen sich senden
			if ($objDC['mail_art'] == "d") {
			// Test if ($objDC['mail_art'] != "z") {
				// $icon = 'system/modules/eurooffice/assets/email_go.png';
				return '<span style="margin-right:2px;"><a href="'.$this->addToUrl($href).'&key=sendVersandSerie&vers_tstamp=' .time().'&eoid='.$objDC['eoid'].'" title="Versand an alle Direktempf. auslösen"'.$attributes.'>'.Image::getHtml('files/themes/email_go.png', $label, 'style="width:14px;height:14px;"').'</a></span>';
			} else {
				return '';
			}			
		}
	}
	// Als erstes Argument bekommt der die aktuelle Zeile aus der Datenbank, als zweites Argument den Label, wie er weiter oben im DCA-Record definiert wurde, hier also mit dem Platzhaltern #name# und #section_colour#.
	public function formatLabel($row, $label) 
	{
		if ($row['mail_art'] == "d") {
			$label = '<span style="color:darkgreen;">'.$label.'</span>';
		}
		if ($row['mail_art'] == "v") {
			$label = '<span style="color:darkblue;">'.$label.'</span>';
		}
		if ($row['mail_art'] == "p") {
			$label = '<span style="color:darkgrey;">'.$label.'</span>';
		}
		return $label;
	} 
}
