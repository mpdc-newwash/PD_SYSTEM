<?php include("auth.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Budget Dashboard</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
    th { background-color: #f4f4f4; }
    .modal, .modal-overlay {
      display: none;
      position: fixed;
    }
    .modal {
      top: 50%; left: 50%; transform: translate(-50%, -50%);
      background: #fff; padding: 20px; border: 1px solid #ccc;
      z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .modal-overlay {
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.3); z-index: 999;
    }
  </style>
</head>
<body>
  <h1>Budget Dashboard</h1>
  <a href="logout.php" style="float:right;"><button>Logout</button></a>
  <input type="text" id="searchInput" placeholder="Search..." style="width:300px; padding:5px; margin-bottom:10px;" oninput="filterTable()">

  <table id="recordsTable">
    <thead>
      <tr>
        <th>Date Submitted</th>
        <th>Title</th>
        <th>Budget Source</th>
        <th>Total Budget</th>
        <th>Budget Status</th>
        <th>Budget Remarks</th>
        <th>File Link</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div class="modal-overlay" id="overlay"></div>
  <div class="modal" id="editModal">
    <h3>Update Budget Info</h3>
    <label>Budget Status: <input id="editStatus" /></label><br />
    <label>Budget Remarks: <input id="editRemarks" /></label><br />
    <button onclick="saveEdit()">Save</button>
    <button onclick="closeModal()">Cancel</button>
  </div>

<script>
  const GET_URL = "get-data.php";
  const POST_URL = "proxy.php";
  let dataRows = [], allRows = [], editRowIndex = null;
  
  
  function formatDate(isoString) {
  if (!isoString) return "";
  const date = new Date(isoString);
  return date.toLocaleDateString("en-PH", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}


  fetch(GET_URL)
    .then(res => res.json())
    .then(data => {
      if (!data.length) return;
      dataRows = data;
      const tbody = document.querySelector("#recordsTable tbody");

      data.forEach((row, index) => {
        const tr = document.createElement("tr");
        const keys = Object.keys(row); // assumes stable sheet column order

        const fileLink = row[keys[14]] ? `<a href="${row[keys[14]]}" target="_blank">View</a>` : "";

        tr.innerHTML = `
          <td>${formatDate(row[keys[0]])}</td>  <!-- Timestamp -->
          <td>${row[keys[2]] || ""}</td>  <!-- Title -->
          <td>${row[keys[6]] || ""}</td>  <!-- Budget Source -->
          <td>${row[keys[4]] || ""}</td>  <!-- Total Budget -->
          <td>${row[keys[17]] || ""}</td> <!-- Budget Office Status -->
          <td>${row[keys[18]] || ""}</td> <!-- Budget Office Remarks -->
          <td>${fileLink}</td>
          <td><button onclick="openModal(${index})">Edit</button></td>
        `;

        tbody.appendChild(tr);
        allRows.push(tr);
      });
    });

  function openModal(index) {
    editRowIndex = index + 2;
    const row = dataRows[index];
    const keys = Object.keys(row);

    document.getElementById("editStatus").value = row[keys[16]] || "";
    document.getElementById("editRemarks").value = row[keys[17]] || "";

    document.getElementById("overlay").style.display = "block";
    document.getElementById("editModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("overlay").style.display = "none";
    document.getElementById("editModal").style.display = "none";
  }

  function saveEdit() {
    const updates = {
      "Budget Office Status": document.getElementById("editStatus").value.trim(),
      "Budget Office Remarks": document.getElementById("editRemarks").value.trim()
    };

    fetch(POST_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ rowIndex: editRowIndex, updates })
    })
    .then(res => res.text())
    .then(() => { alert("Updated!"); location.reload(); })
    .catch(() => { alert("Failed."); closeModal(); });
  }

  function filterTable() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    allRows.forEach(row => {
      const match = Array.from(row.cells).some(cell =>
        cell.textContent.toLowerCase().includes(searchTerm)
      );
      row.style.display = match ? "" : "none";
    });
  }
</script>



</body>
</html>
