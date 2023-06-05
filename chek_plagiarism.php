<?php
require 'vendor/autoload.php'; // Include the Composer autoloader

use PhpOffice\PhpWord\IOFactory;

// Function to search Google using Custom Search API
function searchGoogle($query, $apiKey, $cx) {
    $url = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&cx={$cx}&q=" . urlencode($query);
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Function to compare content and calculate similarity percentage
function compareContent($content, $searchResults) {
    $totalMatches = 0;
    
    foreach ($searchResults['items'] as $result) {
        $snippet = $result['snippet'];
        $matches = substr_count(strtolower($content), strtolower($snippet));
        $totalMatches += $matches;
    }
    
    $similarityPercentage = ($totalMatches / str_word_count($content)) * 100;
    
    return $similarityPercentage;
}

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
        $content = "";
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $content .= $element->getText() . " ";
            }
        }

        // Search Google and get the search results
        $query = urlencode($content);
        $apiKey = "YOUR_API_KEY"; // Replace with your Google API key
        $cx = "YOUR_SEARCH_ENGINE_ID"; // Replace with your Custom Search Engine ID
        $searchResults = searchGoogle($query, $apiKey, $cx);

        // Compare content and calculate similarity percentage
        $similarityPercentage = compareContent($content, $searchResults);

        // Display the plagiarism results
        echo "Similarity Percentage: " . round($similarityPercentage, 2) . "%<br>";
        echo "<h3>Similar Phrases:</h3>";
        
        foreach ($searchResults['items'] as $result) {
            $snippet = $result['snippet'];
            echo "- " . $snippet . "<br>";
            echo "Original URL: <a href='" . $result['link'] . "' target='_blank'>" . $result['link'] . "</a><br><br>";
        }
    } else {
        echo "Error uploading the file.";
        exit;
    }
}
?>
