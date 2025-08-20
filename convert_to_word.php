<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

// Lire le contenu du fichier Markdown
$markdownContent = file_get_contents('Rapport_Tests_BTP_Manager.md');

// Créer un nouveau document Word
$phpWord = new PhpWord();

// Styles pour les titres
$phpWord->addTitleStyle(1, array('bold' => true, 'size' => 18, 'color' => '2E86AB'));
$phpWord->addTitleStyle(2, array('bold' => true, 'size' => 16, 'color' => 'A23B72'));
$phpWord->addTitleStyle(3, array('bold' => true, 'size' => 14, 'color' => 'F18F01'));

// Style pour le code
$codeStyle = array('name' => 'Courier New', 'size' => 10, 'bgColor' => 'F5F5F5');

// Style pour les tableaux
$tableStyle = array('borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80);
$firstRowStyle = array('bgColor' => 'E6E6E6', 'bold' => true);

// Diviser le contenu en lignes
$lines = explode("\n", $markdownContent);

$section = $phpWord->addSection();

foreach ($lines as $line) {
    $line = trim($line);
    
    if (empty($line)) {
        continue;
    }
    
    // Titres
    if (preg_match('/^# (.+)$/', $line, $matches)) {
        $section->addTitle($matches[1], 1);
    } elseif (preg_match('/^## (.+)$/', $line, $matches)) {
        $section->addTitle($matches[1], 2);
    } elseif (preg_match('/^### (.+)$/', $line, $matches)) {
        $section->addTitle($matches[1], 3);
    }
    // Code blocks
    elseif (preg_match('/^```(.+)?$/', $line)) {
        // Ignorer les délimiteurs de code
        continue;
    }
    // Tableaux
    elseif (preg_match('/^\|(.+)\|$/', $line)) {
        $cells = explode('|', $line);
        array_shift($cells); // Supprimer le premier élément vide
        array_pop($cells);   // Supprimer le dernier élément vide
        
        $table = $section->addTable($tableStyle);
        $row = $table->addRow();
        
        foreach ($cells as $cell) {
            $cell = trim($cell);
            $row->addCell(2000)->addText($cell, array('bold' => true));
        }
    }
    // Lignes de séparation
    elseif (preg_match('/^---+$/', $line)) {
        $section->addTextBreak();
    }
    // Texte normal
    else {
        // Détecter les éléments en gras
        $line = preg_replace('/\*\*(.+?)\*\*/', '$1', $line);
        
        // Détecter les éléments en italique
        $line = preg_replace('/\*(.+?)\*/', '$1', $line);
        
        // Détecter les listes
        if (preg_match('/^-\s(.+)$/', $line, $matches)) {
            $section->addListItem($matches[1], 0);
        } elseif (preg_match('/^\d+\.\s(.+)$/', $line, $matches)) {
            $section->addListItem($matches[1], 1);
        } else {
            $section->addText($line);
        }
    }
}

// Sauvegarder le document
$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('Rapport_Tests_BTP_Manager.docx');

echo "Rapport Word généré avec succès : Rapport_Tests_BTP_Manager.docx\n";
