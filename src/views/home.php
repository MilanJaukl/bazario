<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bazos Scraper Form</title>
</head>

<body>
    <form method="POST" action="">
        <label for="searchQueries">Search Queries (comma separated, max 5):</label><br>
        <input type="text" id="searchQueries" name="searchQueries" required><br><br>

        <label for="minPrice">Minimum Price:</label><br>
        <input type="number" id="minPrice" name="minPrice"><br><br>

        <label for="maxPrice">Maximum Price:</label><br>
        <input type="number" id="maxPrice" name="maxPrice"><br><br>

        <label for="postalCode">Postal Code:</label><br>
        <input type="number" id="postalCode" name="postalCode"><br><br>

        <label for="distance">Distance (min 26 km):</label><br>
        <input type="number" id="distance" name="distance"><br><br>

        <label for="maxRequestsPerCrawl">Max Requests per Crawl:</label><br>
        <input type="number" id="maxRequestsPerCrawl" name="maxRequestsPerCrawl" value="100"><br><br>

        <input type="submit" value="Scrape Listings">
    </form>
</body>

</html>

<?php

require 'vendor/autoload.php'; // Load Guzzle via Composer

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input from the form
    $searchQueries = explode(',', $_POST['searchQueries']);
    $minPrice = !empty($_POST['minPrice']) ? (int)$_POST['minPrice'] : null;
    $maxPrice = !empty($_POST['maxPrice']) ? (int)$_POST['maxPrice'] : null;
    $postalCode = !empty($_POST['postalCode']) ? (int)$_POST['postalCode'] : null;
    $distance = !empty($_POST['distance']) ? (int)$_POST['distance'] : null;
    $maxRequestsPerCrawl = !empty($_POST['maxRequestsPerCrawl']) ? (int)$_POST['maxRequestsPerCrawl'] : 100;

    // Prepare the input data for the API
    $inputData = [
        "searchQueries" => $searchQueries,
        "maxRequestsPerCrawl" => $maxRequestsPerCrawl,
    ];

    if ($minPrice !== null) $inputData["minPrice"] = $minPrice;
    if ($maxPrice !== null) $inputData["maxPrice"] = $maxPrice;
    if ($postalCode !== null) $inputData["postalCode"] = $postalCode;
    if ($distance !== null) $inputData["distance"] = $distance;

    // Initialize Guzzle Client
    $client = new Client([
        'base_uri' => 'https://api.apify.com/v2/',
    ]);

    try {

        // Send request to run the Bazos scraper
        $response = $client->post("acts/vzahajsky~bazos-scraper/run-sync-get-dataset-items?token={$apiKey}", [
            'json' => $inputData,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        // Get the response body and decode it to an array
        $scrapedData = json_decode($response->getBody(), true);

        // Display the scraped data in a table
        echo "<h1>Scraped Listings</h1>";
        echo "<table border='1'>";
        echo "<tr><th>Title</th><th>Price</th><th>Link</th></tr>";

        foreach ($scrapedData as $listing) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($listing['title']) . "</td>";
            echo "<td>" . htmlspecialchars($listing['price']) . "</td>";
            echo "<td><a href='" . htmlspecialchars($listing['url']) . "' target='_blank'>View Listing</a></td>";
            echo "</tr>";
        }

        echo "</table>";
    } catch (RequestException $e) {
        // Handle any errors that occur during the request
        echo "An error occurred: " . $e->getMessage();
    }
}
?>