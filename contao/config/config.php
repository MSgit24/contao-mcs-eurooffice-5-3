<?php

/*
 * This file is part of eurooffice.
 *
 * (c) MS 2025 <schepke@mcon-consulting.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/mcs/contao-mcs-eurooffice
 */

use Mcs\ContaoMcsEurooffice\Model\MusterModel;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseEoMailer;
use Mcs\ContaoMcsEurooffice\Controller\BackendModule\EoBeKlasseEoOperations;

/**
 * Backend modules
 */
// $GLOBALS['BE_MOD']['muster_bemodulkategorie']['muster_bemodultyp'] = array(
//     'tables' => array('tl_muster')
// );


/* funktioniert */
 
// Backend-Module für den Euro-Office Bereich ==============================================
// diese Reihenfolge ist wichtig, da die Module so im Backend angezeigt werden

$GLOBALS['BE_MOD']['be_eurooffice']['eoinfos'] = array(
    'tables' => array('tl_eo_be_eoinfos')
);

$GLOBALS['BE_MOD']['be_eurooffice']['versand'] = array(
    'tables' => array('tl_eo_be_versand'),
    'sendVersandSerie' => array(EoBeKlasseEoMailer::class, 'sendVersandSerie'),
    'deleteVersandSerie' => array(EoBeKlasseEoOperations::class, 'deleteVersandSerie'),
    'deleteEinzelVersand' => array(EoBeKlasseEoOperations::class, 'deleteEinzelVersand')
);

$GLOBALS['BE_MOD']['be_eurooffice']['vertadr'] = array(
    'tables' => array('tl_eo_be_vertadr')
);

$GLOBALS['BE_MOD']['be_eurooffice']['themen'] = array(
    'tables' => array('tl_eo_be_themen')
);

$GLOBALS['BE_MOD']['be_eurooffice']['programme'] = array(
    'tables' => array('tl_eo_be_programme')
);

$GLOBALS['BE_MOD']['be_eurooffice']['schlagworte'] = array(
    'tables' => array('tl_eo_be_schlagworte')
);

$GLOBALS['BE_MOD']['be_eurooffice']['anlagearten'] = array(
    'tables' => array('tl_eo_be_anlagearten')
);


 
/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_muster'] = MusterModel::class;

/**
 * Cron jobs
 */
$GLOBALS['TL_CRON']['minutely'][] = array(EoBeKlasseEoMailer::class, 'sendVersandSerie');
