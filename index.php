<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Asset Upload Tool</title>
  <!-- Bootstrap 5.3.8 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm rounded-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Asset Upload Tool</h5>
        </div>
        <div class="card-body">
          <form id="uploadForm">
            <div class="mb-3">
              <label for="excelFile" class="form-label">Choose Excel File</label>
              <input class="form-control" type="file" id="excelFile" name="excelFile"
                     accept=".xls,.xlsx" required>
              <div class="form-text">Only Excel files (.xls, .xlsx) are allowed.</div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 25px; display: none;" id="progressContainer">
              <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                   role="progressbar" style="width: 0%">0%</div>
            </div>

            <button type="submit" class="btn btn-success w-100">Upload</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap 5.3.8 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("uploadForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const fileInput = document.getElementById("excelFile");
  if (!fileInput.files.length) {
    alert("Please select an Excel file first.");
    return;
  }

  const formData = new FormData();
  formData.append("excelFile", fileInput.files[0]);

  const xhr = new XMLHttpRequest();
  const progressContainer = document.getElementById("progressContainer");
  const progressBar = document.getElementById("progressBar");

  progressContainer.style.display = "block"; // show progress bar

  xhr.upload.addEventListener("progress", function(e) {
    if (e.lengthComputable) {
      const percentComplete = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percentComplete + "%";
      progressBar.textContent = percentComplete + "%";
    }
  });

  xhr.addEventListener("load", function() {
    progressBar.classList.remove("progress-bar-animated");
    progressBar.textContent = "Upload Complete!";
  });

  xhr.addEventListener("error", function() {
    progressBar.classList.add("bg-danger");
    progressBar.textContent = "Upload Failed!";
  });

  // backend upload endpoint
  xhr.open("POST", "/upload");
  xhr.send(formData);
});
</script>

</body>
</html>
