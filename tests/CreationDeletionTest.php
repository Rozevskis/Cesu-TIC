<?php

use PHPUnit\Framework\TestCase;

class CreationDeletionTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Create an in-memory SQLite database for testing
        $this->db = new SQLite3(':memory:');

        // Create the visitors table
        $this->db->exec("CREATE TABLE visitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            origin TEXT,
            destination TEXT,
            arrivalMethod TEXT,
            infoChannel TEXT,
            interestTopic TEXT
        )");
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    public function testCreateEntry()
    {
        // Prepare the data to insert
        $origin = 'Riga';
        $destination = 'Cesis';
        $arrivalMethod = 'auto';
        $infoChannel = 'internet';
        $interestTopic = 'history';

        // Prepare the SQL statement
        $stmt = $this->db->prepare("INSERT INTO visitors (origin, destination, arrivalMethod, infoChannel, interestTopic) 
                                    VALUES (:origin, :destination, :arrivalMethod, :infoChannel, :interestTopic)");
        $stmt->bindValue(':origin', $origin, SQLITE3_TEXT);
        $stmt->bindValue(':destination', $destination, SQLITE3_TEXT);
        $stmt->bindValue(':arrivalMethod', $arrivalMethod, SQLITE3_TEXT);
        $stmt->bindValue(':infoChannel', $infoChannel, SQLITE3_TEXT);
        $stmt->bindValue(':interestTopic', $interestTopic, SQLITE3_TEXT);

        // Execute the prepared statement
        $result = $stmt->execute();

        // Check if the row was inserted successfully
        $this->assertTrue($result !== false);

        // Verify the data
        $query = $this->db->query("SELECT * FROM visitors WHERE origin = 'Riga'");
        $row = $query->fetchArray(SQLITE3_ASSOC);

        $this->assertNotEmpty($row);

        // Print out the expected and actual results
        echo "Expected origin: $origin, Actual origin: " . $row['origin'] . PHP_EOL;
        echo "Expected destination: $destination, Actual destination: " . $row['destination'] . PHP_EOL;
        echo "Expected arrivalMethod: $arrivalMethod, Actual arrivalMethod: " . $row['arrivalMethod'] . PHP_EOL;
        echo "Expected infoChannel: $infoChannel, Actual infoChannel: " . $row['infoChannel'] . PHP_EOL;
        echo "Expected interestTopic: $interestTopic, Actual interestTopic: " . $row['interestTopic'] . PHP_EOL;

        $this->assertEquals($origin, $row['origin']);
        $this->assertEquals($destination, $row['destination']);
        $this->assertEquals($arrivalMethod, $row['arrivalMethod']);
        $this->assertEquals($infoChannel, $row['infoChannel']);
        $this->assertEquals($interestTopic, $row['interestTopic']);
    }

    public function testEditEntry()
    {
        // Insert a sample entry first
        $this->testCreateEntry();

        // Retrieve the ID of the last inserted row
        $lastId = $this->db->lastInsertRowID();

        // New data for update
        $newOrigin = 'Vilnius';
        $newDestination = 'Sigulda';
        $newArrivalMethod = 'train';
        $newInfoChannel = 'magazine';
        $newInterestTopic = 'nature';

        // Prepare the update statement
        $updateStmt = $this->db->prepare("UPDATE visitors SET origin = :origin, destination = :destination, arrivalMethod = :arrivalMethod, infoChannel = :infoChannel, interestTopic = :interestTopic WHERE id = :id");
        $updateStmt->bindValue(':origin', $newOrigin, SQLITE3_TEXT);
        $updateStmt->bindValue(':destination', $newDestination, SQLITE3_TEXT);
        $updateStmt->bindValue(':arrivalMethod', $newArrivalMethod, SQLITE3_TEXT);
        $updateStmt->bindValue(':infoChannel', $newInfoChannel, SQLITE3_TEXT);
        $updateStmt->bindValue(':interestTopic', $newInterestTopic, SQLITE3_TEXT);
        $updateStmt->bindValue(':id', $lastId, SQLITE3_INTEGER);

        // Execute the update statement
        $updateResult = $updateStmt->execute();
        $this->assertTrue($updateResult !== false);

        // Verify the data was updated
        $query = $this->db->query("SELECT * FROM visitors WHERE id = $lastId");
        $row = $query->fetchArray(SQLITE3_ASSOC);

        $this->assertNotEmpty($row);

        // Print out the expected and actual results
        echo "Expected new origin: $newOrigin, Actual origin: " . $row['origin'] . PHP_EOL;
        echo "Expected new destination: $newDestination, Actual destination: " . $row['destination'] . PHP_EOL;
        echo "Expected new arrivalMethod: $newArrivalMethod, Actual arrivalMethod: " . $row['arrivalMethod'] . PHP_EOL;
        echo "Expected new infoChannel: $newInfoChannel, Actual infoChannel: " . $row['infoChannel'] . PHP_EOL;
        echo "Expected new interestTopic: $newInterestTopic, Actual interestTopic: " . $row['interestTopic'] . PHP_EOL;

        $this->assertEquals($newOrigin, $row['origin']);
        $this->assertEquals($newDestination, $row['destination']);
        $this->assertEquals($newArrivalMethod, $row['arrivalMethod']);
        $this->assertEquals($newInfoChannel, $row['infoChannel']);
        $this->assertEquals($newInterestTopic, $row['interestTopic']);
    }

    public function testDeleteEntry()
    {
        // Insert a sample entry first
        $this->testCreateEntry();

        // Retrieve the ID of the last inserted row
        $lastId = $this->db->lastInsertRowID();

        // Prepare the delete statement
        $deleteStmt = $this->db->prepare("DELETE FROM visitors WHERE id = :id");
        $deleteStmt->bindValue(':id', $lastId, SQLITE3_INTEGER);
        $deleteStmt->execute();

        // Verify the data was deleted
        $query = $this->db->query("SELECT * FROM visitors WHERE id = $lastId");
        $row = $query->fetchArray(SQLITE3_ASSOC);

        // Print out the expected and actual results
        echo "Expected no result for ID $lastId, Actual result: " . (empty($row) ? 'None' : json_encode($row)) . PHP_EOL;

        $this->assertEmpty($row);
    }
}
