<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar View Tasks</title>
    <!-- Include CSS stylesheets and scripts as needed -->
</head>
<body>
    <h1>Tasks Done on Selected Date</h1>

    <div id="calendar">
        <h2>Calendar</h2>
        <?php
        // Ensure date parameter is set
        if (isset($_GET['date'])) {
            $selectedDate = $_GET['date'];

            // Generate a simple calendar for the selected month
            $dateComponents = getdate(strtotime($selectedDate));
            $month = $dateComponents['mon'];
            $year = $dateComponents['year'];
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
            $dayOfWeek = date('w', $firstDayOfMonth);
            $monthName = $dateComponents['month'];

            echo "<table>";
            echo "<caption>$monthName $year</caption>";
            echo "<tr>";
            $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($daysOfWeek as $day) {
                echo "<th>$day</th>";
            }
            echo "</tr><tr>";
            if ($dayOfWeek > 0) {
                for ($k = 0; $k < $dayOfWeek; $k++) {
                    echo "<td></td>";
                }
            }
            $currentDay = 1;

            while ($currentDay <= $daysInMonth) {
                if ($dayOfWeek == 7) {
                    $dayOfWeek = 0;
                    echo "</tr><tr>";
                }
                $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
                $date = "$year-$month-$currentDayRel";
                echo "<td><a href='calendar_view_tasks.php?date=$date'>$currentDay</a></td>";   
                $currentDay++;
                $dayOfWeek++;
            }
            if ($dayOfWeek != 7) {
                $remainingDays = 7 - $dayOfWeek;
                for ($i = 0; $i < $remainingDays; $i++) {
                    echo "<td></td>";
                }
            }
            echo "</tr>";
            echo "</table>";
        } else {
            echo "<p>No date selected.</p>";
        }
        ?>
    </div>

    <div id="tasks">
        <h2>Tasks Done on <?php echo isset($selectedDate) ? date('F d, Y', strtotime($selectedDate)) : 'Selected Date'; ?></h2>
        <?php
        // Include database connection
      require_once '../dbconnection.php';


        if (isset($selectedDate)) {
            // Query tasks done on selected date
            $sql = "SELECT * FROM productivity WHERE status='done' AND DATE(created_at)='$selectedDate' ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Output tasks
                echo "<ul>";
                while ($row = $result->fetch_assoc()) {
                    echo "<li>{$row['task']} - {$row['created_at']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No tasks finished/ done on $selectedDate</p>";
            }
        }

        $conn->close();
        ?>
    </div>

    <div>
        <a href="productivity_plan.php">Back to Calendar</a>
    </div>

    <!-- Include JavaScript for interaction -->
</body>
</html>
