<?php
// view.php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$conn = new mysqli("localhost", "root", "", "weatherdb");
$user_id = $_SESSION['user_id'];

// get all rows for this user (newest first)
$res = $conn->query("SELECT id, date, location, temperature, humidity, wind_speed, precipitation, pressure, timestamp
                     FROM weather_logs
                     WHERE user_id = $user_id
                     ORDER BY date DESC, id DESC");

$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Weather Logs</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Additional styles for view page */
    table{width:100%; border-collapse:collapse; font-size:14px; background: white;}
    th,td{border-bottom:1px solid #e1e5e9; padding:12px; text-align:left;}
    th{color:#666666; font-weight:600; background:#f8f9fa;}
    td{color:#1a1a1a;}
    .controls{display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin:8px 0 12px;}
    select{padding:8px 10px; border-radius:8px; border:1px solid #e1e5e9; background:white; color:#1a1a1a;}
    .note{color:#666666; font-size:12px;}
    .datebox{display:flex; flex-wrap:wrap; gap:8px; max-height:120px; overflow:auto; padding:8px; border:1px solid #e1e5e9; border-radius:8px; background:white;}
    .chart-wrap{background:white; border:1px solid #e1e5e9; border-radius:10px; padding:12px;}
    .pick{margin:0;}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
    <title>View Saved Data</title>
      <h1>My Weather Logs</h1>
      <p class="subtitle">View and manage your weather data</p>
    </div>

    <div class="panel">
      <div class="actions">
        <a href="index.php" class="button primary">➕ Add New Entry</a>
        <a href="logout.php" class="button">Logout</a>
      </div>
    </div>

    <div class="panel">
      <div style="text-align: center; margin-bottom: 20px; padding: 15px;">
        <h1 style="color: #0277bd; font-size: 28px; font-weight: 700; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Saved Weather Data</h1>
      </div>
      <table>
      <thead>
        <tr>
          <th>Select</th>
          <th>Date</th>
          <th>Location</th>
          <th>Temperature (°C)</th>
          <th>Humidity (%)</th>
          <th>Wind (km/h)</th>
          <th>Precip (mm)</th>
          <th>Pressure (hPa)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><input type="checkbox" class="pick" data-id="<?= htmlspecialchars($r['id']) ?>"></td>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td><?= htmlspecialchars($r['location']) ?></td>
            <td><?= htmlspecialchars($r['temperature']) ?></td>
            <td><?= htmlspecialchars($r['humidity']) ?></td>
            <td><?= htmlspecialchars($r['wind_speed']) ?></td>
            <td><?= htmlspecialchars($r['precipitation']) ?></td>
            <td><?= htmlspecialchars($r['pressure']) ?></td>
	    <td>
            <form action="delete.php" method="POST" style="display:inline">
   	    <input type="hidden" name="id" value="<?= $r['id'] ?>">
	    <button onclick="return confirm('Delete this entry?')">Delete</button>
            </form>
            </td>          
</tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" class="note">No entries yet. Add one on the “Add new entry” page.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="panel">
    <h3 style="margin:0 0 8px;">Build a bar chart (pick up to 7 rows)</h3>
    <div class="controls">
      <label>Metric:
        <select id="metric">
          <option value="temperature">Temperature (°C)</option>
          <option value="humidity">Humidity (%)</option>
          <option value="wind_speed">Wind (km/h)</option>
          <option value="precipitation">Precipitation (mm)</option>
          <option value="pressure">Pressure (hPa)</option>
        </select>
      </label>
      <span class="note">Select up to 7 checkboxes in the table above, then click “Draw chart”.</span>
      <button id="draw">Draw chart</button>
      <button id="clearSel">Clear selections</button>
    </div>
    <div class="chart-wrap">
      <canvas id="chart" height="220"></canvas>
    </div>
  </div>

  <script>
    // embed rows so JS can use them
    const ROWS = <?php echo json_encode($rows, JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const checkboxes = Array.from(document.querySelectorAll('.pick'));
    const metricSel = document.getElementById('metric');
    const drawBtn = document.getElementById('draw');
    const clearBtn = document.getElementById('clearSel');
    let chart;

    // limit to 7 selections
    checkboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        const picked = checkboxes.filter(x => x.checked);
        if (picked.length > 7) {
          cb.checked = false;
          alert('You can select at most 7 rows for the chart.');
        }
      });
    });

    function getRowById(id) {
      return ROWS.find(r => String(r.id) === String(id));
    }

    function drawChart() {
      const picked = checkboxes.filter(x => x.checked).slice(0, 7);
      if (picked.length === 0) { alert('Pick 1–7 rows first.'); return; }

      const metric = metricSel.value;
      const labels = [];
      const values = [];
      picked.forEach(cb => {
        const row = getRowById(cb.dataset.id);
        labels.push(`${row.date} (${row.location})`);
        values.push(Number(row[metric]));
      });

      const ctx = document.getElementById('chart').getContext('2d');
      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{ label: metric.replace('_',' '), data: values }]
        },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    drawBtn.addEventListener('click', drawChart);
    clearBtn.addEventListener('click', () => { checkboxes.forEach(c => c.checked = false); if (chart) chart.destroy(); });
  </script>
  </div>
</body>
</html>
