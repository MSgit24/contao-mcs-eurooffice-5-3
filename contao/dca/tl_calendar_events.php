<?php

/*
Anpassungen von DCAs direkt von Contao in einem eigenen Bundle: in diesem Fall musst du auch sicherstellen, dass dein Bundle nach dem entsprechenden Bundle geladen wird, dessen DCA du erweitern willst. Im Fall von tl_news wäre das das ContaoNewsBundle. 
Das machst du über das BundlePluginInterface des Contao Manager Plugins. 
/mcs/contao-mcs-eurooffice/src/ContaoManager/Plugin.php
*/

use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseGetSelects;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
// ->removeField('published', 'publish_legend')
->addField('prgkat', 'teaser', PaletteManipulator::POSITION_AFTER)
->applyToPalette('default', 'tl_calendar_events');

// Hinzufügen der Feld-Konfigurationen von prgkat
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['prgkat'] = array
(
		'label'     => &$GLOBALS['TL_LANG']['tl_calendar_events']['prgkat'],
		'exclude'   => true,
		'inputType' => 'select',
		//'options_callback' => array('tl_calendar_events_eo', 'getEOPrgArray'),
			'options_callback' => function(){
				// getEOPrgArray: ohne Param alle, mit entspr. ausgewählt 
				// eo: ToDo: bei Bedarf alle zurückgeben lassen, gruppiert nach Status
				return EoBeKlasseGetSelects::getEOPrgArray("aktuell");
			},
		'eval'      => array('multiple'=> false,'tl_class'=>'w50 clr','size'=>15),
		'sql'       => "varchar(150) NOT NULL default 'ohne Prg.'"
);

?>
