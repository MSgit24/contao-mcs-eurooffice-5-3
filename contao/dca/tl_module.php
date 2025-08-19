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

use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\MusterFemodultypController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichtallgController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulFeedbackauswertung;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersprotokollmconController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersandstatistikController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVertsystemadrlisteController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulKundenmemberlisteController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVolltextsucheController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAuflistungeoinfosController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulVersandprotokolleobericht; 
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEoinfoanzeigeController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEodboutputController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulEodbformular;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAnlagenpereoinfoversendenController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichtanzahlvertempfaengerController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulBerichttabellenlkskverteilerController;
use Mcs\ContaoMcsEurooffice\Controller\FrontendModule\FemodulAuflistungaktualisiertereoinfosController;

/**
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][MusterFemodultypController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulBerichtallgController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulFeedbackauswertung::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulVersprotokollmconController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulVersandstatistikController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulVertsystemadrlisteController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulKundenmemberlisteController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulVolltextsucheController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulAuflistungeoinfosController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulVersandprotokolleobericht::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulEoinfoanzeigeController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulEodboutputController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulEodbformular::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulAnlagenpereoinfoversendenController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulBerichtanzahlvertempfaengerController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulBerichttabellenlkskverteilerController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][FemodulAuflistungaktualisiertereoinfosController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';