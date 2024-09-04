<?php

use PHPUnit\Framework\TestCase;

class FormValidationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = new SQLite3(':memory:'); // Use an in-memory database for testing
        $this->db->exec("CREATE TABLE visitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            origin TEXT,
            destination TEXT,
            arrivalMethod TEXT,
            infoChannel TEXT,
            interestTopic TEXT
        )");
    }

    public function testInvalidCharacters()
    {
        $data = [
            'origin' => "City123", // Invalid: contains numbers
            'destination' => "Destination!",
            'arrivalMethod' => "auto",
            'infoChannel' => "Channel@",
            'interestTopic' => "Topic#"
        ];

        $errors = [];

        // Validate each field
        if (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $data['origin'])) {
            $errors[] = "Izcelsmes valsts vai pilsēta drīkst saturēt tikai burtus un atstarpes.";
        }

        if (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $data['destination'])) {
            $errors[] = "Galamērķis drīkst saturēt tikai burtus un atstarpes.";
        }

        if (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $data['infoChannel'])) {
            $errors[] = "Informācijas kanāls drīkst saturēt tikai burtus un atstarpes.";
        }

        if (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $data['interestTopic'])) {
            $errors[] = "Interešu tēmas drīkst saturēt tikai burtus un atstarpes.";
        }

        $this->assertCount(4, $errors); // Expect 4 errors for invalid input
    }
}
