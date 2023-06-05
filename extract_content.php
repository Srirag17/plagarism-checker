<?php
require 'vendor/autoload.php'; // Include the Composer autoloader

use PhpOffice\PhpWord\IOFactory;

// Check if the file is uploaded and valid
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["file"])) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["file"]["name"]);
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the file is a DOCX file
    if ($fileType !== "docx") {
        echo "Only DOCX files are allowed.";
        exit;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        // Extract content from the DOCX file
        $phpWord = IOFactory::load($targetFile);
        $text = "";
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $element->getText() . " ";
            }
        }

        // Display the extracted content
        echo $text;
    } else {
        echo "Error uploading the file.";
        exit;
    }
}
?>
