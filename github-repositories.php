<?php
// Replace 'YOUR_GITHUB_TOKEN' with your actual GitHub Personal Access Token.
$token = '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';

$githubUsername = $_GET['username'] ?? 'kjagannathreddy';

try {
    // GitHub API endpoint to fetch user's repositories.
    $apiUrl = "https://api.github.com/users/{$githubUsername}/repos";

    // Set up cURL options for the API request.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token", // Include your GitHub token.
        "User-Agent: GitHub-Repo-Viewer" // Replace with your desired user-agent.
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        throw new Exception("cURL error: " . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 404) {
        throw new Exception("User {$githubUsername} not found.");
    } elseif ($httpCode == 403) {
        // Check for rate limit exceeded error.
        $limitReset = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        if (strpos($limitReset, 'X-RateLimit-Reset:') !== false) {
            $resetTime = intval(explode(': ', $limitReset)[1]);
            $resetTimeFormatted = date("Y-m-d H:i:s", $resetTime);
            throw new Exception("API rate limit exceeded. Try again after {$resetTimeFormatted}.");
        } else {
            throw new Exception("API rate limit exceeded.");
        }
    } elseif ($httpCode == 200) {
        // Decode the JSON response.
        $repositories = json_decode($response, true);

        if (empty($repositories)) {
            echo "No repositories found for {$githubUsername}.";
        } else {
            // Display the repositories in an HTML table.
            echo "<table border='1'>
                    <tr>
                        <th>Repository Name</th>
                        <th>Description</th>
                        <th>Last updated</th>
                    </tr>";

            foreach ($repositories as $repo) {
                
                echo "<tr>
                        <td><a href='{$repo['html_url']}' target='_blank'>{$repo['name']}</a></td>
                        <td>{$repo['description']}</td>
                        <td>" . convertTimeZone($repo['updated_at']) . "</td>
                      </tr>";
            }

            echo "</table>";
        }
    } else {
        throw new Exception("Unexpected error occurred. HTTP code: {$httpCode}");
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

function convertTimeZone($timestamp, $inputTimeZone = 'UTC', $outputTimeZone = 'Asia/Kolkata') {
    $dateTimeUTC = new DateTime($timestamp, new DateTimeZone($inputTimeZone));
    $dateTimeIST = $dateTimeUTC->setTimezone(new DateTimeZone($outputTimeZone));
    return $dateTimeIST->format("Y-m-d H:i:s");
}
?>