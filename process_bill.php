<?php
// process_bill.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php"); // adjust path if necessary

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Prevent undefined variable notice
$billText = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['billFile'])) {
    die("No file uploaded. Use the upload form (method=POST, input name='billFile').");
}
$tesseractPath = '"C:\\Program Files\\Tesseract-OCR\\tesseract.exe"';
$cmd = "$tesseractPath " . escapeshellarg($imagePath) . " " . escapeshellarg($outputPath);
exec($cmd, $output, $returnVar);

// Basic upload handling
$file = $_FILES['billFile'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    die("Upload error code: " . $file['error']);
}

$origName = basename($file['name']);
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$targetName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
$targetPath = $uploadDir . $targetName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    die("Failed to move uploaded file.");
}

// --- OCR / text extraction ---
$ocrOutput = '';
if ($ext === 'pdf') {
    // Path to pdftotext.exe in your project OR rely on system pdftotext in PATH
    $pdftotext_local = __DIR__ . '/pdftotext.exe'; // if you placed pdftotext.exe here
    if (file_exists($pdftotext_local)) {
        $cmd = "\"$pdftotext_local\" \"$targetPath\" \"{$targetPath}.txt\"";
    } else {
        $cmd = "pdftotext " . escapeshellarg($targetPath) . ' ' . escapeshellarg($targetPath . '.txt');
    }
    exec($cmd . " 2>&1", $out, $ret);
    $ocrOutput = implode("\n", $out);
    if (file_exists($targetPath . '.txt')) {
        $billText = file_get_contents($targetPath . '.txt');
    }
} else {
    // image file - use Tesseract
    // Set tesseract path if installed in default location on Windows
    $tesseract_local = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';
    if (file_exists($tesseract_local)) {
        $tessCmd = "\"$tesseract_local\"";
    } else {
        // Assume 'tesseract' is in PATH
        $tessCmd = "tesseract";
    }
    // Output base (tesseract appends .txt)
    $outputBase = $targetPath . '_ocr';
    $cmd = $tessCmd . ' ' . escapeshellarg($targetPath) . ' ' . escapeshellarg($outputBase);
    exec($cmd . " 2>&1", $out, $ret);
    $ocrOutput = implode("\n", $out);
    if (file_exists($outputBase . '.txt')) {
        $billText = file_get_contents($outputBase . '.txt');
    }
}

// Debug: show OCR raw output and any OCR tool output (helpful if conversion failed)
echo "<h2>OCR tool output (debug)</h2><pre>" . htmlspecialchars($ocrOutput) . "</pre>";

// If no text extracted, show helpful message + uploaded file info
if (trim($billText) === '') {
    echo "<h2>Raw OCR Text (empty)</h2>";
    echo "<p>No text extracted. Possible reasons:</p>";
    echo "<ul>
            <li>pdftotext / tesseract not installed or not in path</li>
            <li>PDF is scanned image and pdftotext failed (use tesseract on extracted images)</li>
            <li>OCR had low accuracy</li>
          </ul>";
    echo "<p>Uploaded file: " . htmlspecialchars($targetName) . " (size: " . filesize($targetPath) . " bytes)</p>";
    exit;
}

// Show raw OCR text (for debugging)
echo "<h2>Raw OCR Text</h2><pre>" . htmlspecialchars($billText) . "</pre>";

// --- Parse items from text (flexible patterns) ---
$orderItems = [];
$lines = preg_split("/\r\n|\n|\r/", $billText);

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    // Try multiple patterns (common invoice/bill formats)
    $matches = null;

    // Pattern: "1. Product Name - Qty: 2" or "Product Name - Qty 2"
    if (preg_match('/^(?:\d+\.\s*)?(.+?)\s*[-–:]\s*Qty\s*[:\-]?\s*(\d+)/i', $line, $matches)
        || preg_match('/^(?:\d+\.\s*)?(.+?)\s+Qty\s*[:\-]?\s*(\d+)/i', $line, $matches)
        || preg_match('/^(?:\d+\.\s*)?(.+?)\s*[-–]\s*(\d+)\s*pcs?/i', $line, $matches)
        || preg_match('/^(?:\d+\.\s*)?(.+?)\s+(\d+)\s*pcs?/i', $line, $matches)
        || preg_match('/^(?:\d+\.\s*)?(.+?)\s+(\d+)\s*$/i', $line, $matches)
    ) {
        $pname = trim($matches[1]);
        $qty = (int)$matches[2];
        if ($qty > 0 && strlen($pname) > 1) {
            $orderItems[] = ['name' => $pname, 'qty' => $qty];
        }
    }
}

// Debug parsed items
echo "<h2>Parsed Items</h2><pre>" . htmlspecialchars(print_r($orderItems, true)) . "</pre>";

if (empty($orderItems)) {
    echo "<p>No items parsed from OCR text. Try with a clearer invoice or paste OCR text here so we can customize parsing rules.</p>";
    exit;
}

// --- Check stock in DB using prepared statements (searching 'actual_stock') ---
echo "<h2>Stock Check Result</h2>";

$stmt = $conn->prepare("SELECT actual_stock FROM products WHERE name LIKE ? LIMIT 1");
if (!$stmt) {
    die("DB prepare failed: " . $conn->error);
}

foreach ($orderItems as $it) {
    $name = $it['name'];
    $qty = (int)$it['qty'];
    $search = '%' . $name . '%';
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stock = (int)$row['actual_stock'];
        if ($stock >= $qty) {
            echo "<p>✅ <strong>" . htmlspecialchars($name) . "</strong> — Ordered: $qty — In stock: $stock — Left: " . ($stock - $qty) . "</p>";
        } else {
            echo "<p>⚠️ <strong>" . htmlspecialchars($name) . "</strong> — Ordered: $qty — In stock: $stock — Shortage: " . ($qty - $stock) . "</p>";
        }
    } else {
        echo "<p>❌ <strong>" . htmlspecialchars($name) . "</strong> — Not found in database</p>";
    }
}

$stmt->close();
