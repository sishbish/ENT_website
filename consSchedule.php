<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Consultant Availability</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f0f0;
      font-size: 18px;
    }

    header {
      background-color: #7aadff;
      color: white;
      padding: 6rem 2rem;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      position: relative;
    }

    .header-container {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .header-container img.image-logo {
      position: absolute;
      left: 0;
      height: 180px;
      margin-left: 250px;
      margin-top: -80px;
    }

    .header-container h1 {
      margin: 10 auto;
    }

    .menu-bar {
      position: fixed;
      top: 0;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      padding: 15px 0px;
      z-index: 1000;
    }

    .menu-bar ul {
      display: flex;
      justify-content: center;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
      gap: 30px;
    }

    .menu-bar li a {
      color: white;
      text-decoration: none;
      font-size: 18px;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1.5rem 2rem;
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    #calendar {
      max-width: 100%;
      margin-top: 2rem;
    }

    .available {
      background-color: #007bff !important;
      border: none;
      color: white;
      font-weight: bold;
    }

    .booked {
      background-color: #dc3545 !important;
      border: none;
      color: white;
      font-weight: bold;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 10px;
    }


  </style>
</head>
<body>

<header>
  <div class="header-container">
    <a href="enthub.php">
      <img class="image-logo" src="Ent.png" alt="Logo">
    </a>
    <h1>Consultant Availability</h1>
  </div>
</header>

<nav class="menu-bar">
  <ul>
    <li><a href="enthub.php">Home</a></li>
    <li><a href="clinics.php">Clinics</a></li>
    <li><a href="consultants.php">Consultants</a></li>
  </ul>
</nav>

<div class="container">
  <?php
    if (isset($_GET['name'])) {
      $consultantName = $_GET['name'];
      echo "<h2>Available dates for $consultantName</h2>";

      $servername = "sci-mysql"; 
      $username = "coa123edb"; 
      $password = "E4XujVcLcNPhwfBjx-"; 
      $dbname = "coa123edb";

      $conn = mysqli_connect($servername, $username, $password, $dbname);
      if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
      }

      $sql = "SELECT consultant_schedule.weekday AS day FROM consultants 
              JOIN consultant_schedule ON consultants.id = consultant_schedule.consultant_id 
              WHERE consultants.name = '$consultantName';";
      $result = mysqli_query($conn, $sql);

      $times = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $times[] = $row;
      }

      $sql = "SELECT bookings.booking_date AS bookings FROM consultants 
              JOIN bookings ON bookings.consultant_id = consultants.id 
              WHERE consultants.name = '$consultantName';";
      $result = mysqli_query($conn, $sql);

      $bookings = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
      }
    }
  ?>

  <div id="calendar"></div>
</div>

<footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const availableDays = <?php echo json_encode($times); ?>;
  let days = availableDays.map(day => (parseInt(day.day) + 1) % 7);
  let daysOffset = days.map(d => (d + 1) % 7);

  const bookedDates = <?php echo json_encode($bookings); ?>;
  console.log(bookedDates)
  const bookedSet = new Set(bookedDates.map(date => date.bookings));

  const unavailableDates = Array.from(bookedSet).map(dateStr => ({
    title: 'Unavailable',
    start: dateStr,
    className: 'booked'
  }));

  const availableDates = [
    { title: 'Available', daysOfWeek: days, startRecur: '2025-01-01', endRecur: '2025-03-30' },
    { title: 'Available', daysOfWeek: daysOffset, startRecur: '2025-03-30', endRecur: '2025-10-27' },
    { title: 'Weekly Class', daysOfWeek: days, startRecur: '2025-10-27', endRecur: '2026-01-01' }
  ];

  const isOverridden = (dateStr) => unavailableDates.some(e => e.start === dateStr);

  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    timeZone: 'Europe/London',
    events: function(fetchInfo, successCallback) {
      const start = new Date(fetchInfo.startStr);
      const end = new Date(fetchInfo.endStr);
      const events = [...unavailableDates];

      for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dateStr = d.toISOString().slice(0, 10);
        const day = d.getDay();

        availableDates.forEach(re => {
          const startRecur = new Date(re.startRecur);
          const endRecur = new Date(re.endRecur);
          if (re.daysOfWeek.includes(day) && d >= startRecur && d <= endRecur && !isOverridden(dateStr)) {
            events.push({
              title: re.title,
              start: dateStr,
              className: 'available'
            });
          }
        });
      }

      successCallback(events);
    }
  });

  calendar.render();
});
</script>

</body>
</html>