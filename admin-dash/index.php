<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPDC Dashboard</title>
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      display: flex;
    }
    .sidebar {
      width: 200px;
      background: #444;
      color: white;
      height: 100vh;
      padding-top: 20px;
    }
    .sidebar h3 {
      color: #eee;
      margin-left: 20px;
    }
    .sidebar button {
      display: block;
      width: 100%;
      padding: 12px;
      margin: 5px 0;
      background: #5c7ca6;
      color: white;
      border: none;
      text-align: left;
      cursor: pointer;
    }
    .sidebar button.logout {
      background: #ccc;
      color: #444;
    }
    .main {
      flex: 1;
      padding: 20px;
      background: #f5f5f5;
    }
    .header {
      background: #3454d1;
      color: white;
      padding: 20px;
      font-size: 22px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .boxes {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
    }
    .box {
      flex: 1;
      min-width: 150px;
      text-align: center;
      padding: 20px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 28px;
      color: white;
    }
    .pending, .mbo, .client { background: #3b5fc4; }
    .approved { background: #f6c23e; color: #444; }
    .denied { background: #ff5a5a; }
    .cancelled { background: #555; }
    .view-table { margin-top: 30px; }
    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
    }
    th { background-color: #e0f0ff; }
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.3);
      z-index: 999;
    }
    .modal {
      display: none;
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 20px;
      border: 1px solid #ccc;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .modal input, .modal select {
      width: 100%;
      margin-bottom: 10px;
    }
    #loader {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(255, 255, 255, 0.85);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 2000;
    }
    .spinner {
      border: 8px solid #f3f3f3;
      border-top: 8px solid #3454d1;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
<div id="loader"><div class="spinner"></div></div>
<div class="sidebar">

  <button>DASH BOARD</button>
  <button class="logout" onclick="window.location='logout.php'">LOGOUT</button>
</div>
<div class="main">
  <div class="header">
    
    <div>
         <div id="datetime" style="font-size: 16px; margin-bottom: 5px;"></div>
        <strong>LGU- NEW WASHINGTON</strong><br>MPDC PROGRAM STATEMENT TRACKER</div>
    <div>
        <img src="LGU.png" alt="Logo" height="60">
        <img src="mpdc-logo.png" alt="Logo" height="60"></div>
  </div>
  <div class="boxes" id="statusBoxes">
    <div class="box pending" id="pendingBox">0<br><span>NEW REQUEST</span></div>
    <div class="box mbo" id="mboBox">0<br><span>FOR MBO APPROVAL</span></div>
    <div class="box client" id="clientBox">0<br><span>FOR CLIENT APPROVAL</span></div>
    <div class="box approved" id="approvedBox">0<br><span>APPROVED</span></div>
    <div class="box denied" id="deniedBox">0<br><span>DENIED</span></div>
    <div class="box cancelled" id="cancelledBox">0<br><span>CANCELLED</span></div>
  </div>
  <div class="view-table" id="viewTableSection">
    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
      <input type="text" id="searchInput" placeholder="Search..." oninput="filterTable()" style="padding:8px; width:300px;">
    </div>
    <table id="recordsTable">
      <thead>
        <tr>
          <th>Date Submitted</th>
          <th>Title</th>
          <th>Description</th>
          <th>Source of Fund</th>
          <th>Total Budget</th>
          <th>Date of Event</th>
          <th>Requesting Office</th>
          <th>Submitted by</th>
          <th>Status</th>
          <th>Budget Status</th>
          <th>Budget Remarks</th>
          <th>Client Approval</th>
          <th>File Link</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
  <div class="modal-overlay" id="overlay"></div>
  <div class="modal" id="editModal">
    <h3>Edit Fields</h3>
    <label>Status:
      <select id="editStatus">
        <option value="draft sent to client">Send Draft</option>
        <option value="done">Done</option>
        <option value="for approval of mbo">For Approval of MBO</option>
        <option value="Hold">Hold</option>
        <option value="Cancelled">Cancelled</option>
      </select>
    </label>
    <label>Client Approval:
      <select id="editClient">
        <option value="yes">Approved</option>
        <option value="processing">Revision Requested</option>
      </select>
    </label>
    <label>Budget Remarks:
      <input type="text" id="editBudget" />
    </label>
    <button onclick="saveEdit()">Save</button>
    <button onclick="closeModal()">Cancel</button>
    <button onclick="resetEdit()">Reset</button>
  </div>
</div>
<script>
  const GET_URL = "get-data.php", POST_URL = "proxy.php";
  const visibleFields = [0, 2, 3, 6, 4, 7, 10, 12, "status", "Budget Office Status", "Budget Office Remarks", "Client Approval Response", "Generated File ID"];
  let dataRows = [], headers = [], allRows = [], editRowIndex = null;

  function formatDate(isoString) {
    if (!isoString) return "";
    const date = new Date(isoString);
    return date.toLocaleDateString("en-PH", { year: "numeric", month: "long", day: "numeric" });
  }

  fetch(GET_URL)
    .then(res => res.json())
    .then(data => {
      document.getElementById("loader").style.display = "none";
      dataRows = data;
      headers = Object.keys(data[0]);
      const tableBody = document.querySelector("#recordsTable tbody");
      const statusCounts = { pending:0, mbo:0, client:0, approved:0, denied:0, cancelled:0 };
      data.forEach((row, index) => {
        const tr = document.createElement("tr");
        visibleFields.forEach(field => {
          const td = document.createElement("td");
          const key = typeof field === "number" ? headers[field] : field;
          if (field === 0) td.textContent = formatDate(row[key]);
          else if (key === "Generated File ID" && row[key]) td.innerHTML = `<a href="${row[key]}" target="_blank">View</a>`;
          else td.textContent = row[key] || "";
          tr.appendChild(td);
        });
        const td = document.createElement("td");
        td.innerHTML = `<button onclick="openModal(${index})">Edit</button>`;
        tr.appendChild(td);
        tableBody.appendChild(tr);
        allRows.push(tr);
        const s = (row["status"] || "").toLowerCase();
        const r = (row["Budget Office Remarks"] || "").toLowerCase();
        if (s === "pending") statusCounts.pending++;
        else if (s === "for approval of mbo") statusCounts.mbo++;
        else if (s === "draft sent to client") statusCounts.client++;
        if (s === "done") statusCounts.approved++;
        if (s === "cancelled") r.includes("denied") ? statusCounts.denied++ : statusCounts.cancelled++;
      });
      document.getElementById("pendingBox").innerHTML = `${statusCounts.pending}<br><span>NEW REQUEST</span>`;
      document.getElementById("mboBox").innerHTML = `${statusCounts.mbo}<br><span>FOR MBO APPROVAL</span>`;
      document.getElementById("clientBox").innerHTML = `${statusCounts.client}<br><span>FOR CLIENT APPROVAL</span>`;
      document.getElementById("approvedBox").innerHTML = `${statusCounts.approved}<br><span>APPROVED</span>`;
      document.getElementById("deniedBox").innerHTML = `${statusCounts.denied}<br><span>DENIED</span>`;
      document.getElementById("cancelledBox").innerHTML = `${statusCounts.cancelled}<br><span>CANCELLED</span>`;
    });

  function filterTable() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    allRows.forEach(row => {
      const match = Array.from(row.cells).some(c => c.textContent.toLowerCase().includes(search));
      row.style.display = match ? "" : "none";
    });
  }

  function openModal(index) {
    editRowIndex = index + 2;
    const row = dataRows[index];
    document.getElementById("editStatus").value = row["Status"] || "";
    document.getElementById("editClient").value = row["Client Approval Response"] || "";
    document.getElementById("editBudget").value = row["Budget Office Remarks"] || "";
    document.getElementById("overlay").style.display = "block";
    document.getElementById("editModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("overlay").style.display = "none";
    document.getElementById("editModal").style.display = "none";
  }

  function resetEdit() {
    closeModal();
  }

    function updateDateTime() {
    const now = new Date();
    const formatted = now.toLocaleString("en-PH", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit"
    });
    document.getElementById("datetime").textContent = formatted;
  }

  setInterval(updateDateTime, 1000);
  updateDateTime(); 


  function saveEdit() {
    const updates = {
      "Status": document.getElementById("editStatus").value.trim(),
      "Client Approval Response": document.getElementById("editClient").value.trim(),
      "Budget Office Remarks": document.getElementById("editBudget").value.trim()
    };
    fetch(POST_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ rowIndex: editRowIndex, updates })
    }).then(() => {
      alert("Updated!");
      location.reload();
    });
  }
</script>
</body>
</html>
