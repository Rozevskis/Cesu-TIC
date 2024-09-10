<!DOCTYPE html>
<html lang="lv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cēsu TIC - Viesu informācijas ievade</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #545a66;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

    <?php
    // Connect to SQLite database or create it if it doesn't exist
    $db = new SQLite3('visitors.db');

    // Create the visitors table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        origin TEXT,
        destination TEXT,
        arrivalMethod TEXT,
        infoChannel TEXT,
        interestTopic TEXT
    )");

    // Initialize variables for validation error messages
    $errors = [];
    $success = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['delete_id'])) {
            // Handle delete request
            $delete_id = intval($_POST['delete_id']);
            $deleteStmt = $db->prepare("DELETE FROM visitors WHERE id = :id");
            $deleteStmt->bindValue(':id', $delete_id, SQLITE3_INTEGER);
            $deleteStmt->execute();
        } elseif (isset($_POST['update_id'])) {
            // Handle update request
            $update_id = intval($_POST['update_id']);
            $origin = htmlspecialchars(trim($_POST['origin']));
            $destination = htmlspecialchars(trim($_POST['destination']));
            $arrivalMethod = htmlspecialchars(trim($_POST['arrivalMethod']));
            $infoChannel = htmlspecialchars(trim($_POST['infoChannel']));
            $interestTopic = htmlspecialchars(trim($_POST['interestTopic']));

            // Update the visitor record
            $updateStmt = $db->prepare("UPDATE visitors SET origin = :origin, destination = :destination, arrivalMethod = :arrivalMethod, infoChannel = :infoChannel, interestTopic = :interestTopic WHERE id = :id");
            $updateStmt->bindValue(':origin', $origin, SQLITE3_TEXT);
            $updateStmt->bindValue(':destination', $destination, SQLITE3_TEXT);
            $updateStmt->bindValue(':arrivalMethod', $arrivalMethod, SQLITE3_TEXT);
            $updateStmt->bindValue(':infoChannel', $infoChannel, SQLITE3_TEXT);
            $updateStmt->bindValue(':interestTopic', $interestTopic, SQLITE3_TEXT);
            $updateStmt->bindValue(':id', $update_id, SQLITE3_INTEGER);

            // Execute the update statement
            if ($updateStmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Kļūda, saglabājot izmaiņas.";
            }
        } elseif (isset($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $editResult = $db->query("SELECT * FROM visitors WHERE id = $edit_id");
            $editRow = $editResult->fetchArray(SQLITE3_ASSOC);
        } else {
            // Retrieve and sanitize form data
            $origin = htmlspecialchars(trim($_POST['origin']));
            $destination = htmlspecialchars(trim($_POST['destination']));
            $arrivalMethod = htmlspecialchars(trim($_POST['arrivalMethod']));
            $infoChannel = htmlspecialchars(trim($_POST['infoChannel']));
            $interestTopic = htmlspecialchars(trim($_POST['interestTopic']));

            // Validation: Check if required fields are not empty
            if (empty($origin)) {
                $errors[] = "Lūdzu, ievadiet izcelsmes valsti vai pilsētu.";
            } elseif (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $origin)) {
                $errors[] = "Izcelsmes valsts vai pilsēta drīkst saturēt tikai burtus un atstarpes.";
            }

            if (empty($destination)) {
                $errors[] = "Lūdzu, ievadiet galamērķi.";
            } elseif (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $destination)) {
                $errors[] = "Galamērķis drīkst saturēt tikai burtus un atstarpes.";
            }

            if (empty($arrivalMethod)) {
                $errors[] = "Lūdzu, izvēlieties ierašanās veidu.";
            }

            if (empty($infoChannel)) {
                $errors[] = "Lūdzu, ievadiet informācijas kanālu.";
            } elseif (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $infoChannel)) {
                $errors[] = "Informācijas kanāls drīkst saturēt tikai burtus un atstarpes.";
            }

            if (empty($interestTopic)) {
                $errors[] = "Lūdzu, ievadiet interešu tēmas.";
            } elseif (!preg_match("/^[a-zA-ZāčēģīķļņōŗśšūžĀČĒĢĪĶĻŅŌŖŚŠŪŽ\s]+$/u", $interestTopic)) {
                $errors[] = "Interešu tēmas drīkst saturēt tikai burtus un atstarpes.";
            }

            // If no validation errors, proceed with inserting data
            if (empty($errors)) {
                // Prepare an SQL statement to insert data
                $stmt = $db->prepare("INSERT INTO visitors (origin, destination, arrivalMethod, infoChannel, interestTopic) 
                                    VALUES (:origin, :destination, :arrivalMethod, :infoChannel, :interestTopic)");
                $stmt->bindValue(':origin', $origin, SQLITE3_TEXT);
                $stmt->bindValue(':destination', $destination, SQLITE3_TEXT);
                $stmt->bindValue(':arrivalMethod', $arrivalMethod, SQLITE3_TEXT);
                $stmt->bindValue(':infoChannel', $infoChannel, SQLITE3_TEXT);
                $stmt->bindValue(':interestTopic', $interestTopic, SQLITE3_TEXT);

                // Execute the prepared statement
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $errors[] = "Kļūda, pievienojot datus.";
                }
            }
        }
    }

    // Fetch all data from the visitors table
    $result = $db->query("SELECT * FROM visitors");
    ?>

    <?php if (!empty($errors)): ?>
        <div class="max-w-lg mx-auto bg-red-100 text-red-700 p-4 rounded mb-4">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Display success message -->
    <?php if ($success): ?>
        <div class="max-w-lg mx-auto bg-green-100 text-green-700 p-4 rounded mb-4">
            <p>Viesis veiksmīgi pievienots!</p>
        </div>
    <?php endif; ?>

    <!-- HTML form for visitor input -->
    <div class="max-w-lg mx-auto p-8 bg-white shadow-md rounded-lg m-[20px]">
    <h2 class="text-2xl font-bold mb-6 text-center">
        <?php echo isset($editRow) ? 'Viesu rediģēšana' : 'Cēsu TIC - Viesu informācijas ievade'; ?>
    </h2>

        <!-- Form Start -->
        <form id="visitorForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">

            <!-- Origin Input -->
            <div>
                <label for="origin" class="block text-sm font-medium text-gray-700">Viesu valsts vai pilsēta (izcelsme):</label>
                <input type="text" name="origin" id="origin" value="<?php echo isset($editRow) ? htmlspecialchars($editRow['origin']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Destination Input -->
            <div>
                <label for="destination" class="block text-sm font-medium text-gray-700">Izraudzītais galamērķis vai aktivitāte:</label>
                <input type="text" name="destination" id="destination" value="<?php echo isset($editRow) ? htmlspecialchars($editRow['destination']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Arrival Method Select -->
            <div>
                <label for="arrivalMethod" class="block text-sm font-medium text-gray-700">Ierašanās veids:</label>
                <select name="arrivalMethod" id="arrivalMethod"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Izvēlēties...</option>
                    <option value="auto" <?php echo isset($editRow) && $editRow['arrivalMethod'] == 'auto' ? 'selected' : ''; ?>>Auto</option>
                    <option value="sabiedriskais transports" <?php echo isset($editRow) && $editRow['arrivalMethod'] == 'sabiedriskais transports' ? 'selected' : ''; ?>>Sabiedriskais transports</option>
                    <option value="velosipēds" <?php echo isset($editRow) && $editRow['arrivalMethod'] == 'velosipēds' ? 'selected' : ''; ?>>Velosipēds</option>
                    <option value="kājām" <?php echo isset($editRow) && $editRow['arrivalMethod'] == 'kājām' ? 'selected' : ''; ?>>Kājām</option>
                </select>
            </div>

            <!-- Info Channel Input -->
            <div>
                <label for="infoChannel" class="block text-sm font-medium text-gray-700">Informācijas kanāls (Kā jūs uzzinājāt par Cēsu TIC?):</label>
                <input type="text" name="infoChannel" id="infoChannel" value="<?php echo isset($editRow) ? htmlspecialchars($editRow['infoChannel']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Interest Topic Input -->
            <div>
                <label for="interestTopic" class="block text-sm font-medium text-gray-700">Interešu tēmas (Ko jūs plānojat darīt Cēsīs?):</label>
                <input type="text" name="interestTopic" id="interestTopic" value="<?php echo isset($editRow) ? htmlspecialchars($editRow['interestTopic']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Update Button and Hidden Field for ID -->
            <?php if (isset($editRow)): ?>
                <input type="hidden" name="update_id" value="<?php echo $editRow['id']; ?>">
                <!-- Yellow Button for Editing -->
                <button type="submit" class="w-full bg-yellow-500 text-white py-2 px-4 rounded">Saglabāt izmaiņas</button>
            <?php else: ?>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded">Pievienot viesi</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Display all visitors in a table -->
    <div class="w-1/2 max-w-full mx-auto mt-8">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">ID</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Izcelsme</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Galamērķis</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Ierašanās veids</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Informācijas kanāls</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Interešu tēmas</th>
                    <th class="w-1/5 py-3 px-4 uppercase font-semibold text-sm">Darbības</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['origin']); ?></td>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['destination']); ?></td>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['arrivalMethod']); ?></td>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['infoChannel']); ?></td>
                        <td class="w-1/5 py-3 px-4"><?php echo htmlspecialchars($row['interestTopic']); ?></td>
                        <td class="w-1/5 py-3 px-4">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="text-blue-500 hover:text-blue-700">Rediģēt</button>
                            </form>
                            <form onsubmit="return confirm('Vai tiešām vēlaties dzēst viesi?')" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">Dzēst</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>