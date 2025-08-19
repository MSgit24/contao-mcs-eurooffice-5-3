<?php

use Contao\Config;
// use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Anpassung der Filter
// eo: nicht benötigte Filter rausnehmen
$GLOBALS['TL_DCA']['tl_member']['fields']['city']['filter'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['filter'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['language']['filter'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['login']['filter'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['company']['filter'] = true;

$GLOBALS['TL_DCA']['tl_member']['fields']['login']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['street']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['postal']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['city']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['phone']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['mobile']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['fax']['search'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['website']['search'] = false;


/*
	PaletteManipulator::create()
		// remove the field "custom_field" from the "name_legend" - i.S.v. ausblenden
		->removeField('dateOfBirth', 'personal_legend')
		->removeField('gender', 'personal_legend')
		->removeField('street', 'address_legend')
		->removeField('postal', 'address_legend')
		->removeField('city', 'address_legend')
		->removeField('state', 'address_legend')
		->removeField('country', 'address_legend')
		->removeField('phone', 'contact_legend')
		->removeField('mobile', 'contact_legend')
		->removeField('fax', 'contact_legend')
		->removeField('website', 'contact_legend')
		->removeField('language', 'contact_legend')

		// again, the change is registered in the PaletteManipulator
		// but it still has to be applied to the globals:
		->applyToPalette('default', 'tl_member')
	;

	// pwklar ================================================
	$GLOBALS['TL_DCA']['tl_member']['fields']['pwklar'] = array(
		'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pwklar'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'eval'                    => array('maxlength' => 25, 'tl_class' => 'w50', 'style' => 'width:35%'),
		'sql'                     => "varchar(25) NOT NULL default ''"
	);

	PaletteManipulator::create()
		// apply the field "custom_field" after the field "username"
		// ->addField('custom_field', 'username')
		->addField('pwklar', 'password')
		// now the field is registered in the PaletteManipulator
		// but it still has to be registered in the globals array:
		// ->applyToPalette('login', 'tl_member') ;
		// applying the new configuration to the "addImage" subpalette
		->applyToSubpalette('login', 'tl_member');



	// mail_direktempf & pdf_anrtext ================================================

	// eo: checkbox für weitere Angaben zum Empfänger der EO-Infos
	//     - bewirkt via __selector__ ein ein-/ausklappen (s. auch oben)
	//     - notwendig, da Pflichtfelder auch von nicht betroffenen ausgefüllt werden müssten

	$GLOBALS['TL_DCA']['tl_member']['fields']['mail_direktempf'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_direktempf'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'                    => array('submitOnChange'=>true),
		'sql'                     => "char(1) NOT NULL default ''"
	);

	$GLOBALS['TL_DCA']['tl_member']['fields']['pdf_anrkopf'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pdf_anrkopf'],
		'exclude'                 => true,
		'inputType'               => 'text',
		//'eval'                    => array('mandatory'=>true, 'rgxp'=>'alnum', 'maxlength'=>50, 'tl_class'=>'w50'),
		// eo: mandatory rausgenommen, da im PDF Kopf sonst Hr. Kipp auftauchen würde (Sehr geehrte ... passt da nicht)
		'eval'                    => array('mandatory'=>false, 'rgxp'=>'alnum', 'maxlength'=>50, 'tl_class'=>'w50'),
		'sql'                     => "varchar(50) NOT NULL default ''"
	);

	$GLOBALS['TL_DCA']['tl_member']['fields']['pdf_anrtext'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pdf_anrtext'],
		'exclude'                 => true,
		'inputType'               => 'text',
		//  'rgxp'=>'alnum' verhindert Eingabe von "!"
		'eval'                    => array('mandatory'=>true, 'maxlength'=>50, 'tl_class'=>'w50'),
		'sql'                     => "varchar(50) NOT NULL default ''"
	);


	PaletteManipulator::create()
		// Neue Legend "mail_direktempf" nach "personal_legend" hinzufügen
		->addLegend('eoinfo_legend', 'personal_legend', PaletteManipulator::POSITION_APPEND)
		// Felder zur neuen Legend hinzufügen
		->addField('mail_direktempf', 'eoinfo_legend', PaletteManipulator::POSITION_APPEND)
		->addField('pdf_anrkopf', 'eoinfo_legend', PaletteManipulator::POSITION_APPEND)
		->addField('pdf_anrtext', 'eoinfo_legend', PaletteManipulator::POSITION_APPEND)
		// Auf die Palette anwenden
		->applyToPalette('default', 'tl_member');

*/

// Anpassung der Palette
// eo: Ref: an Stelle des str_replace-Ansatzes zum Ergänzen der Palette
//     original Einträge kopieren und hier anpassen
$GLOBALS['TL_DCA']['tl_member']['palettes'] = array(
	'__selector__'  => array('login', 'assignDir', 'mail_direktempf', 'mail_verteilerabs'),
	'default'       => '{personal_legend},firstname,lastname,dateOfBirth,gender;{address_legend:hide},company;{contact_legend},email;{eoinfo_legend},mail_direktempf;{verteiler_legend},mail_verteilerabs;{groups_legend},groups;{login_legend},login;{account_legend},disable,start,stop',
);

$GLOBALS['TL_DCA']['tl_member']['subpalettes'] = array(
	'login'             => 'username,pwklar,password',
	'mail_direktempf'   => 'pdf_anrkopf, pdf_anrtext, mail_anr, mail_adr, mail_adrcc, mail_adrbcc, mail_txtextra, mail_txterkl, mail_monatsliste, note',
	'mail_verteilerabs' => 'mail_globempf, vert_notbcc, mail_abs, mail_replyto, mail_betreff, mail_verttext, vert_verzh, vert_maillog',
);

// Hinzufügen von Felde
// eo: eval ergänzen um w50 bzw. clr, damit Ausrichtung stimmt, jew. letzter Eintrag an Orig. angehängt
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval'] = array('mandatory' => true, 'unique' => true, 'nullIfEmpty' => true, 'rgxp' => 'extnd', 'nospace' => true, 'maxlength' => 64, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'login', 'tl_class' => 'w50');

$GLOBALS['TL_DCA']['tl_member']['fields']['password']['eval'] = array('mandatory' => true, 'preserveTags' => true, 'minlength' => Config::get('minPasswordLength'), 'feEditable' => true, 'feGroup' => 'login', 'tl_class' => 'clr');

// eo: checkbox für weitere Angaben zum Empfänger der EO-Infos
//     - bewirkt via __selector__ ein ein-/ausklappen (s. auch oben)
//     - notwendig, da Pflichtfelder auch von nicht betroffenen ausgefüllt werden müssten
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_direktempf'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_direktempf'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange' => true),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['pdf_anrkopf'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pdf_anrkopf'],
	'exclude'                 => true,
	'inputType'               => 'text',
	//'eval'                    => array('mandatory'=>true, 'rgxp'=>'alnum', 'maxlength'=>50, 'tl_class'=>'w50'),
	// eo: mandatory rausgenommen, da im PDF Kopf sonst Hr. Kipp auftauchen würde (Sehr geehrte ... passt da nicht)
	'eval'                    => array('mandatory' => false, 'rgxp' => 'alnum', 'maxlength' => 50, 'tl_class' => 'w50'),
	'sql'                     => "varchar(50) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['pdf_anrtext'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pdf_anrtext'],
	'exclude'                 => true,
	'inputType'               => 'text',
	//  'rgxp'=>'alnum' verhindert Eingabe von "!"
	'eval'                    => array('mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50'),
	'sql'                     => "varchar(50) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_anr'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_anr'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'maxlength' => 50),
	'sql'                     => "varchar(50) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_adr'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_adr'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'rgxp' => 'emails', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_adrcc'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_adrcc'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp' => 'emails', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_adrbcc'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_adrbcc'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp' => 'emails', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_txtextra'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_txtextra'],
	'exclude'                 => true,
	'inputType'               => 'textarea',
	'eval'                    => array('rte' => 'tinyMCE'),
	'sql'                     => "text NULL"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_txterkl'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_txterkl'],
	'exclude'                 => true,
	'inputType'               => 'textarea',
	'eval'                    => array('allowHtml' => 'true'),
	// eo: z.Zt. gehen mehrere tinyMCE auf einer Seite nicht, wenn eingeklappt per __selector__
	// eo: ToDo: soll mit späteren tinyMCE-Updates behoben werden
	//'eval'                    => array('rte'=>'tinyMCE'),
	'sql'                     => "text NULL"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_monatsliste'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_monatsliste'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['note'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['note'],
	'exclude'                 => true,
	'inputType'               => 'textarea',
	'eval'                    => array('class' => 'monospace'),
	'sql'                     => "text NULL"
);
// eo: checkbox für Angaben zum Verteiler (wg. __selector__)
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_verteilerabs'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_verteilerabs'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange' => true),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_globempf'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_globempf'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(50) NOT NULL default 'verteiler@eurooffice.de'"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['vert_notbcc'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['vert_notbcc'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_abs'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_abs'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(50) NOT NULL default 'verteiler@eurooffice.de'"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_replyto'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_replyto'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'rgxp' => 'email', 'maxlength' => 255, 'style' => 'width:100%'),
	'sql'                     => "varchar(50) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_betreff'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_betreff'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => true, 'maxlength' => 255),
	'sql'                     => "varchar(255) NOT NULL default 'WG: Euro-Office Info'"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['mail_verttext'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mail_verttext'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'textarea',
	'eval'                    => array('rte' => 'tinyMCE'),
	'sql'                     => "text NULL"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['vert_verzh'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['vert_verzh'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp' => 'natural', 'mandatory' => true, 'doNotCopy' => true, 'tl_class' => 'm12'),
	'sql'                     => "int(10) unsigned NULL"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['vert_maillog'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['vert_maillog'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['pwklar'] = array(
	'label'                   => &$GLOBALS['TL_LANG']['tl_member']['pwklar'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength' => 25, 'tl_class' => 'w50', 'style' => 'width:35%'),
	'sql'                     => "varchar(25) NOT NULL default ''"
);