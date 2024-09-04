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
    <h2 class="text-2xl font-bold mb-6 text-center">Cēsu TIC - Viesu informācijas ievade</h2>
    
    <!-- Form Start -->
    <form id="visitorForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
        
        <!-- Origin Input -->
        <div>
            <label for="origin" class="block text-sm font-medium text-gray-700">Viesu valsts vai pilsēta (izcelsme):</label>
            <input type="text" name="origin" id="origin"  
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Destination Input -->
        <div>
            <label for="destination" class="block text-sm font-medium text-gray-700">Izraudzītais galamērķis vai aktivitāte:</label>
            <input type="text" name="destination" id="destination"  
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Arrival Method Select -->
        <div>
            <label for="arrivalMethod" class="block text-sm font-medium text-gray-700">Ierašanās veids:</label>
            <select name="arrivalMethod" id="arrivalMethod" 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="" selected hidden>Izvēlieties ierašanās veidu</option>
                <option value="auto">Auto</option>
                <option value="vilciens">Vilciens</option>
                <option value="autobuss">Autobuss</option>
                <option value="velo">velo</option>
                <option value="cits">cits</option>
            </select>
        </div>


        <!-- Info Channel Input -->
        <div>
            <label for="infoChannel" class="block text-sm font-medium text-gray-700">Informācijas iegūšanas kanāls:</label>
            <input type="text" name="infoChannel" id="infoChannel"  
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Interest Topic Input -->
        <div>
            <label for="interestTopic" class="block text-sm font-medium text-gray-700">Apmeklētāju interešu tēmas:</label>
            <input type="text" name="interestTopic" id="interestTopic"  
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Pievienot viesi
            </button>
        </div>
    </form>
</div>

    <!-- Visitor table to display the stored data -->
    <div class="max-w-full mx-auto mt-8 p-4 bg-white shadow-md rounded-lg overflow-x-auto">
    <h2 class="text-xl font-bold mb-4 text-center">Viesu Informācija</h2>

    <table id="visitorTable" class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izcelsmes valsts</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Galamērķis</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ierašanās veids</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informācijas kanāls</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interešu tēmas</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php
            // Loop through the result set and display each row in the table
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['origin']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['destination']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['arrivalMethod']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['infoChannel']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['interestTopic']) . "</td>";
                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>
                <form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>
                    <input type='hidden' name='delete_id' value='" . htmlspecialchars($row['id']) . "'>
                    <button type='submit' class='bg-red-500 text-white font-bold py-1 px-2 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50'>
                        Dzēst
                    </button>
                </form>
                </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
