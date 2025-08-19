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

namespace Mcs\ContaoMcsEurooffice\Controller\BackendModule;

// EoPdf-Klasse zur Überschreibung der Footer-Methode
class EoPdf extends \TCPDF
{
    // Überschreiben der Footer-Methode von TCPDF
    public function Footer()
    {
        // Position 15mm vom unteren Rand
        $this->SetY(-15);
        // Schriftart setzen
        $this->SetFont('helvetica', 'I', 7);
        // Hinweistext ausgeben
        $this->Cell(0, 10, "Hinweis: Wenn Sie diese Inhalte oder Teile davon in Veröffentlichungen oder auf Websites weiterverwenden, weisen Sie bitte als Quelle 'www.eurooffice.de' aus.", 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
}
