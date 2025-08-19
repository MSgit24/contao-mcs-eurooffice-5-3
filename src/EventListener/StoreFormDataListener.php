<?php
// src/EventListener/StoreFormDataListener.php
// namespace App\EventListener;
namespace Mcs\ContaoMcsEurooffice\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FrontendUser;
use Doctrine\DBAL\Connection;
// use Contao\System;
// use Contao\CoreBundle\Monolog\ContaoContext;
// use Psr\Log\LogLevel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Contao\Database;

/**
 * Klasse StoreFormDataListener
 * - Reagiert auf den 'storeFormData'-Hook in Contao
 * - Wird immer ausgelöst, wenn ein Formular gespeichert wird
 * 
 * => Contao Formular geht nur zum Erfassen von Daten
 * hierüber, wenn das Formular für die Verteilerempfänger ausgelöst wurde, den alten Eintrag löschen
 */
#[AsHook('storeFormData')]
class StoreFormDataListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function __invoke(array $data, Form $form): array
    {

        // ermitteln von Variableninhalten ---------------
            // echo '<pre>';
            // print_r($form->id); // funktioniert
            // print_r oder var_export($form); // Führt zu Internal Server Error bei komplexen Objekten

            // funktioniert:
            // echo "Form ID: " . $form->id . "\n"; // 3
            // echo "Form Title: " . $form->title . "\n"; // EO-Verteiler_FE-EmpfBearb (Titel des Formulars in Eigenschaften)
            // echo "Form Target Table: " . $form->targetTable . "\n"; // tl_eo_be_vertadr
            // echo "Form Method: " . $form->method . "\n"; // POST
            // echo "Form Action: " . $form->action . "\n"; // /
            // exit;

            // Abruf Formulardaten funktioniert hiermit:
            // echo "Daten Array:\n";
            // print_r($data);
            // echo '</pre>'; exit;

            /*  Array
                    (
                        [tstamp] => 1755146485
                        [delete_id] => 4224
                        [company] => Dieter Meyer Consulting GmbH
                        [mail] => bruns@mcon-consulting.de
                        [name] => Bruns
                        [firstname] => Axel
                        [institution] => 
                        [comment] => REM
                        [themen] => Array
                            (
                                [0] => 1
                            )

                        [published] => 1
                        [published_comment] => 
                        [geloescht] => 
                    )
                */
        // Ende - ermitteln von Variableninhalten ---------------

        // Formular 3 ist das Formular zum Ändern von Verteilerempfängern
        if ($form->id === 3) {
            // echo '<pre>form id: ' . $form->id . '</pre>'; exit;

            // Formulardaten sind in $data
            $vertadr_id = $data['delete_id'];

            $database = Database::getInstance();
            $sql = 'DELETE FROM tl_eo_be_vertadr WHERE id = ' . $vertadr_id . ' AND company = "' . $data['company'] . '"';
            $database->prepare($sql)->execute();
        }


        // Codebeispiel aus der Referenz:
            // $data['member'] = 0;

            // $user = $this->tokenStorage->getToken()?->getUser();

            // if (!$user instanceof FrontendUser) {
            //     return $data;
            // }   

            // if (!$this->columnExistsInTable('member', $form->targetTable)) {
            //     return $data;
            // }

            // // Also store the member ID who submitted the form
            // $data['member'] = $user->id;
        // Ende - Codebeispiel aus der Referenz:

        return $data;
    }

    // Codebeispiel aus der Referenz:
    // private function columnExistsInTable(string $columnName, string $tableName): bool
    // {
    //     $columns = $this->connection->getSchemaManager()->listTableColumns($tableName);

    //     foreach ($columns as $column) {
    //         if ($column->getName() === $columnName) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }
}
