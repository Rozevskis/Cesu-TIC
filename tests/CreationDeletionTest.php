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
        $this->assertEquals($origin, $row['origin']);
        $this->assertEquals($destination, $row['destination']);
        $this->assertEquals($arrivalMethod, $row['arrivalMethod']);
        $this->assertEquals($infoChannel, $row['infoChannel']);
        $this->assertEquals($interestTopic, $row['interestTopic']);
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

        $this->assertEmpty($row);
    }
}
