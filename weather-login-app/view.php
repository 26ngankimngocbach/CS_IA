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
  <title>MeteoTrack - My Weather Logs</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Additional styles for view page */
    .table-container{max-height:500px; overflow-y:auto; border:1px solid #e1e5e9; border-radius:8px; background:white;}
    table{width:100%; border-collapse:collapse; font-size:14px; background: white;}
    th,td{border-bottom:1px solid #e1e5e9; padding:12px; text-align:left;}
    th{color:#666666; font-weight:600; background:#f8f9fa; position:sticky; top:0; z-index:10;}
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
    <div class="panel">
      <div style="text-align: center; margin-bottom: 20px; padding: 15px;">
        <h1 style="color: #0277bd; font-size: 28px; font-weight: 700; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Saved Weather Data</h1>
      </div>
      
      <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; text-align: center;">
          Weather data updated successfully!
        </div>
      <?php endif; ?>
      
      <div class="actions" style="margin-bottom: 20px;">
        <a href="index.php" class="button primary">➕ Add New Entry</a>
        <a href="logout.php" class="button">Logout</a>
      </div>
      <div class="table-container">
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
          <th>Actions</th>
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
            <a href="edit.php?id=<?= $r['id'] ?>" class="button" style="margin-right: 5px; padding: 5px 10px; font-size: 12px;">Edit</a>
            <form action="delete.php" method="POST" style="display:inline">
   	    <input type="hidden" name="id" value="<?= $r['id'] ?>">
	    <button onclick="return confirm('Delete this entry?')" style="padding: 5px 10px; font-size: 12px;">Delete</button>
            </form>
            </td>          
</tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="note">No entries yet. Add one on the "Add new entry" page.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
      </div>
  </div>

  <!-- Mean Calculation Section -->
  <div class="panel">
    <h3 style="margin:0 0 8px; color: #0277bd; text-align: center;">Statistical Analysis</h3>
    <div style="text-align: center; margin-bottom: 15px;">
      <button id="calculateMean" style="padding: 10px 20px; background: #0277bd; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
        Calculate Mean of Selected Records
      </button>
    </div>
    <div id="meanResults" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e1e5e9;">
      <h4 style="margin: 0 0 10px; color: #0277bd;">Mean Values of Selected Records:</h4>
      <div id="meanDisplay" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; text-align: center;">
        <!-- Mean values will be displayed here -->
      </div>
    </div>
  </div>

  <div class="panel">
    <h3 style="margin:0 0 8px;">Build your bar chart (pick up to 7 rows)</h3>
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
    // embed rows so JS can use them - W3Schools format
    var ROWS = <?php echo json_encode($rows, JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Initialize variables - W3Schools format
    var checkboxes = document.querySelectorAll('.pick');
    var metricSel = document.getElementById('metric');
    var drawBtn = document.getElementById('draw');
    var clearBtn = document.getElementById('clearSel');
    var chart;

    // limit to 7 selections - W3Schools format
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].addEventListener('change', function() {
        var picked = [];
        for (var j = 0; j < checkboxes.length; j++) {
          if (checkboxes[j].checked) {
            picked.push(checkboxes[j]);
          }
        }
        if (picked.length > 7) {
          this.checked = false;
          alert('You can select at most 7 rows for the chart.');
        }
      });
    }

    // Helper function to get row by ID - W3Schools format
    function getRowById(id) {
      for (var i = 0; i < ROWS.length; i++) {
        if (String(ROWS[i].id) === String(id)) {
          return ROWS[i];
        }
      }
      return null;
    }

    // Function to draw chart - W3Schools format
    function drawChart() {
      var picked = [];
      for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
          picked.push(checkboxes[i]);
        }
      }
      
      if (picked.length > 7) {
        picked = picked.slice(0, 7);
      }
      
      if (picked.length === 0) { 
        alert('Pick 1–7 rows first.'); 
        return; 
      }

      var metric = metricSel.value;
      var labels = [];
      var values = [];
      
      for (var i = 0; i < picked.length; i++) {
        var row = getRowById(picked[i].getAttribute('data-id'));
        labels.push(row.date + ' (' + row.location + ')');
        values.push(Number(row[metric]));
      }

      var ctx = document.getElementById('chart').getContext('2d');
      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{ label: metric.replace('_',' '), data: values }]
        },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    // Mean calculation function - W3Schools format
    function calculateMean() {
      // Get selected checkboxes
      var selectedCheckboxes = [];
      for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
          selectedCheckboxes.push(checkboxes[i]);
        }
      }
      
      // Validate selection
      if (selectedCheckboxes.length === 0) {
        alert("Please select at least one record to calculate mean values.");
        return false;
      }
      
      // Get selected rows data
      var selectedRows = [];
      for (var i = 0; i < selectedCheckboxes.length; i++) {
        var rowId = selectedCheckboxes[i].getAttribute('data-id');
        var rowData = getRowById(rowId);
        if (rowData) {
          selectedRows.push(rowData);
        }
      }
      
      // Calculate mean for each parameter
      var means = {
        temperature: calculateParameterMean(selectedRows, 'temperature'),
        humidity: calculateParameterMean(selectedRows, 'humidity'),
        wind_speed: calculateParameterMean(selectedRows, 'wind_speed'),
        precipitation: calculateParameterMean(selectedRows, 'precipitation'),
        pressure: calculateParameterMean(selectedRows, 'pressure')
      };
      
      // Display results
      displayMeanResults(means);
      
      return true;
    }
    
    // Helper function to calculate mean for a specific parameter - W3Schools format
    function calculateParameterMean(rows, parameter) {
      var sum = 0;
      var count = rows.length;
      
      for (var i = 0; i < count; i++) {
        sum += parseFloat(rows[i][parameter]);
      }
      
      return sum / count;
    }
    
    // Helper function to display mean results - W3Schools format
    function displayMeanResults(means) {
      var meanDisplay = document.getElementById('meanDisplay');
      var html = '';
      
      // Temperature
      html += '<div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e1e5e9; margin: 5px;">';
      html += '<strong>Temperature</strong><br>';
      html += '<span style="color: #0277bd; font-size: 18px; font-weight: bold;">' + means.temperature.toFixed(1) + '°C</span>';
      html += '</div>';
      
      // Humidity
      html += '<div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e1e5e9; margin: 5px;">';
      html += '<strong>Humidity</strong><br>';
      html += '<span style="color: #0277bd; font-size: 18px; font-weight: bold;">' + means.humidity.toFixed(1) + '%</span>';
      html += '</div>';
      
      // Wind Speed
      html += '<div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e1e5e9; margin: 5px;">';
      html += '<strong>Wind Speed</strong><br>';
      html += '<span style="color: #0277bd; font-size: 18px; font-weight: bold;">' + means.wind_speed.toFixed(1) + ' km/h</span>';
      html += '</div>';
      
      // Precipitation
      html += '<div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e1e5e9; margin: 5px;">';
      html += '<strong>Precipitation</strong><br>';
      html += '<span style="color: #0277bd; font-size: 18px; font-weight: bold;">' + means.precipitation.toFixed(1) + ' mm</span>';
      html += '</div>';
      
      // Pressure
      html += '<div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e1e5e9; margin: 5px;">';
      html += '<strong>Pressure</strong><br>';
      html += '<span style="color: #0277bd; font-size: 18px; font-weight: bold;">' + means.pressure.toFixed(1) + ' hPa</span>';
      html += '</div>';
      
      meanDisplay.innerHTML = html;
      
      // Show the results panel
      document.getElementById('meanResults').style.display = 'block';
    }

    // Event listeners - W3Schools format
    drawBtn.addEventListener('click', drawChart);
    clearBtn.addEventListener('click', clearSelections);
    document.getElementById('calculateMean').addEventListener('click', calculateMean);
    
    // Clear selections function - W3Schools format
    function clearSelections() {
      for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
      }
      if (chart) {
        chart.destroy();
      }
    }
  </script>
  </div>
</body>
</html>
