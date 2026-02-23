<?php
require_once __DIR__ . '/../models/Module.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Version.php';

class ModuleController {
    private $module;
    private $course;
    private $version;

    public function __construct() {
        $this->module = new Module();
        $this->course = new Course();
        $this->version = new Version();
    }

    public function requireAdminOrTeacher() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
            $_SESSION['error'] = "Access denied. Admin or Teacher privileges required.";
            header("Location: index.php?action=dashboard");
            exit();
        }
    }

    public function index() {
        $this->requireAdminOrTeacher();
        
        // Get filter parameters
        $filters = [
            'course_id' => $_GET['filter_course'] ?? '',
            'version_id' => $_GET['filter_version'] ?? '',
            'semester' => $_GET['filter_semester'] ?? '',
            'search' => $_GET['filter_search'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });
        
        $modules = $this->module->getAllModules($filters);
        $courses = $this->course->getAllCourses();
        
        // Get versions for selected course if filter is set
        $versions = [];
        if (!empty($filters['course_id'])) {
            $allVersions = $this->version->getAllVersions(['course_id' => $filters['course_id']]);
            foreach ($allVersions as $v) {
                $versions[] = $v;
            }
        } else {
            $allVersions = $this->version->getAllVersions();
            foreach ($allVersions as $v) {
                $versions[] = $v;
            }
        }
        
        require_once __DIR__ . '/../views/modules/index.php';
    }

    public function create() {
        $this->requireAdminOrTeacher();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->module->mcode = trim($_POST['mcode'] ?? '');
            $this->module->mname = trim($_POST['mname'] ?? '');
            $this->module->cid = $_POST['cid'] ?? 0;
            $this->module->version_id = $_POST['version_id'] ?? 0;
            $this->module->semester = !empty($_POST['semester']) ? trim($_POST['semester']) : null;
            $this->module->credit = !empty($_POST['credit']) ? (float)$_POST['credit'] : 0;

            // Validation
            if (empty($this->module->mcode) || empty($this->module->mname)) {
                $_SESSION['error'] = "Module code and name are required.";
                header("Location: index.php?action=modules&sub=create");
                exit();
            }

            if (empty($this->module->cid) || $this->module->cid == 0) {
                $_SESSION['error'] = "Course selection is required.";
                header("Location: index.php?action=modules&sub=create");
                exit();
            }

            if (empty($this->module->version_id) || $this->module->version_id == 0) {
                $_SESSION['error'] = "Version selection is required.";
                header("Location: index.php?action=modules&sub=create");
                exit();
            }

            if ($this->module->mcodeExists($this->module->mcode, $this->module->version_id)) {
                $_SESSION['error'] = "Module code already exists for this version.";
                header("Location: index.php?action=modules&sub=create");
                exit();
            }

            if ($this->module->create()) {
                $_SESSION['success'] = "Module created successfully.";
                header("Location: index.php?action=modules");
                exit();
            } else {
                $_SESSION['error'] = "Failed to create module.";
                header("Location: index.php?action=modules&sub=create");
                exit();
            }
        } else {
            $courses = $this->course->getAllCourses();
            $versions = $this->version->getAllVersions();
            require_once __DIR__ . '/../views/modules/create.php';
        }
    }

    public function edit() {
        $this->requireAdminOrTeacher();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->module->id = $id;
            $this->module->mcode = trim($_POST['mcode'] ?? '');
            $this->module->mname = trim($_POST['mname'] ?? '');
            $this->module->cid = $_POST['cid'] ?? 0;
            $this->module->version_id = $_POST['version_id'] ?? 0;
            $this->module->semester = !empty($_POST['semester']) ? trim($_POST['semester']) : null;
            $this->module->credit = !empty($_POST['credit']) ? (float)$_POST['credit'] : 0;

            // Validation
            if (empty($this->module->mcode) || empty($this->module->mname)) {
                $_SESSION['error'] = "Module code and name are required.";
                header("Location: index.php?action=modules&sub=edit&id=" . $id);
                exit();
            }

            if (empty($this->module->cid) || $this->module->cid == 0) {
                $_SESSION['error'] = "Course selection is required.";
                header("Location: index.php?action=modules&sub=edit&id=" . $id);
                exit();
            }

            if (empty($this->module->version_id) || $this->module->version_id == 0) {
                $_SESSION['error'] = "Version selection is required.";
                header("Location: index.php?action=modules&sub=edit&id=" . $id);
                exit();
            }

            if ($this->module->mcodeExists($this->module->mcode, $this->module->version_id, $id)) {
                $_SESSION['error'] = "Module code already exists for this version.";
                header("Location: index.php?action=modules&sub=edit&id=" . $id);
                exit();
            }

            if ($this->module->update()) {
                $_SESSION['success'] = "Module updated successfully.";
                header("Location: index.php?action=modules");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update module.";
                header("Location: index.php?action=modules&sub=edit&id=" . $id);
                exit();
            }
        } else {
            if ($this->module->getModuleById($id)) {
                $module = $this->module;
                $courses = $this->course->getAllCourses();
                $versions = $this->version->getAllVersions();
                require_once __DIR__ . '/../views/modules/edit.php';
            } else {
                $_SESSION['error'] = "Module not found.";
                header("Location: index.php?action=modules");
                exit();
            }
        }
    }

    public function delete() {
        // Only admin can delete
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            $_SESSION['error'] = "Access denied. Admin privileges required to delete.";
            header("Location: index.php?action=modules");
            exit();
        }

        $id = $_GET['id'] ?? 0;

        if ($this->module->delete($id)) {
            $_SESSION['success'] = "Module deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete module.";
        }

        header("Location: index.php?action=modules");
        exit();
    }

    public function import() {
        $this->requireAdminOrTeacher();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cid = $_POST['course_id'] ?? 0;
            $version_id = $_POST['version_id'] ?? 0;

            if (empty($cid) || $cid == 0) {
                $_SESSION['error'] = "Course selection is required.";
                header("Location: index.php?action=modules&sub=import");
                exit();
            }

            if (empty($version_id) || $version_id == 0) {
                $_SESSION['error'] = "Version selection is required.";
                header("Location: index.php?action=modules&sub=import");
                exit();
            }

            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Please select an Excel file to upload.";
                header("Location: index.php?action=modules&sub=import");
                exit();
            }

            $file = $_FILES['excel_file']['tmp_name'];
            
            // Check for upload errors
            if ($_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
                ];
                $_SESSION['error'] = "Upload error: " . ($errorMessages[$_FILES['excel_file']['error']] ?? 'Unknown error');
                header("Location: index.php?action=modules&sub=import");
                exit();
            }
            
            $modules = $this->parseExcelFile($file);

            // Error message is already set in parseExcelFile if empty
            if (empty($modules)) {
                header("Location: index.php?action=modules&sub=import");
                exit();
            }

            $result = $this->module->importFromArray($modules, $cid, $version_id);

            if ($result['success'] > 0) {
                $_SESSION['success'] = "Successfully imported {$result['success']} module(s).";
                if (!empty($result['errors'])) {
                    $_SESSION['import_errors'] = $result['errors'];
                }
            } else {
                $_SESSION['error'] = "Failed to import modules. " . implode(', ', $result['errors']);
            }

            header("Location: index.php?action=modules");
            exit();
        } else {
            $courses = $this->course->getAllCourses();
            $versions = $this->version->getAllVersions();
            require_once __DIR__ . '/../views/modules/import.php';
        }
    }

    private function parseExcelFile($file) {
        $modules = [];
        $errors = [];
        
        // Check if file exists
        if (!file_exists($file)) {
            $_SESSION['error'] = "File not found. Please try uploading again.";
            return [];
        }
        
        // Check if file is readable
        if (!is_readable($file)) {
            $_SESSION['error'] = "File is not readable. Please check file permissions.";
            return [];
        }
        
        // Check file size
        if (filesize($file) == 0) {
            $_SESSION['error'] = "File is empty. Please upload a file with data.";
            return [];
        }
        
        // Check if file is CSV or Excel
        $fileExtension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        
        if ($fileExtension == 'csv' || $fileExtension == 'txt') {
            // Try different encodings and delimiters
            $handle = @fopen($file, 'r');
            
            if ($handle === false) {
                $_SESSION['error'] = "Could not open file. Please ensure the file is a valid CSV file.";
                return [];
            }
            
            // Read first line to detect delimiter and BOM
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Check for BOM and remove it
            $hasBOM = false;
            if (substr($firstLine, 0, 3) == "\xEF\xBB\xBF") {
                $hasBOM = true;
                $firstLine = substr($firstLine, 3);
            }
            
            // Detect delimiter by counting occurrences
            $commaCount = substr_count($firstLine, ',');
            $semicolonCount = substr_count($firstLine, ';');
            $tabCount = substr_count($firstLine, "\t");
            
            $delimiter = ',';
            if ($semicolonCount > $commaCount && $semicolonCount > 0) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > 0) {
                $delimiter = "\t";
            }
            
            // Try multiple methods to read header
            $header = false;
            $attempts = [
                ['delimiter' => $delimiter, 'enclosure' => '"', 'escape' => '\\'],
                ['delimiter' => $delimiter, 'enclosure' => '', 'escape' => ''],
                ['delimiter' => ',', 'enclosure' => '"', 'escape' => '\\'],
                ['delimiter' => ';', 'enclosure' => '"', 'escape' => '\\'],
            ];
            
            foreach ($attempts as $attempt) {
                rewind($handle);
                if ($hasBOM) {
                    fread($handle, 3); // Skip BOM
                }
                
                // Skip empty lines at the beginning
                $lineNumber = 0;
                while ($lineNumber < 5) { // Check first 5 lines max
                    $lineNumber++;
                    $testHeader = fgetcsv($handle, 0, $attempt['delimiter'], $attempt['enclosure']);
                    
                    // Skip empty or whitespace-only rows
                    if (empty($testHeader) || !is_array($testHeader)) {
                        continue;
                    }
                    
                    // Check if row has at least 2 non-empty columns
                    $nonEmptyCount = 0;
                    foreach ($testHeader as $col) {
                        if (!empty(trim($col))) {
                            $nonEmptyCount++;
                        }
                    }
                    
                    if ($nonEmptyCount >= 2) {
                        $header = $testHeader;
                        $delimiter = $attempt['delimiter'];
                        break 2; // Break out of both loops
                    }
                }
            }
            
            // If still empty, try manual parsing
            if (empty($header) || !is_array($header) || count($header) < 2) {
                rewind($handle);
                if ($hasBOM) {
                    fread($handle, 3);
                }
                $rawLine = fgets($handle);
                
                // Manual CSV parsing as fallback
                if (!empty($rawLine)) {
                    $header = str_getcsv(trim($rawLine), $delimiter, '"');
                }
            }
            
            if (empty($header) || !is_array($header) || count($header) < 2) {
                rewind($handle);
                $rawLine = fgets($handle);
                $preview = substr($rawLine, 0, 200);
                $hexPreview = bin2hex(substr($rawLine, 0, 50));
                
                $_SESSION['error'] = "Could not read header row from CSV file.<br><br>" .
                                    "<strong>File preview (first 200 chars):</strong> " . htmlspecialchars($preview) . "<br>" .
                                    "<strong>Hex dump (first 50 bytes):</strong> " . $hexPreview . "<br>" .
                                    "<strong>File size:</strong> " . filesize($file) . " bytes<br>" .
                                    "<strong>Expected format:</strong> First line should be: <code>mcode,mname,semester,credit</code><br><br>" .
                                    "Please ensure your CSV file:<br>" .
                                    "1. Has a header row as the first line<br>" .
                                    "2. Uses comma (,) as delimiter<br>" .
                                    "3. Is saved as UTF-8 encoding<br>" .
                                    "4. Download the sample file to see the correct format";
                fclose($handle);
                return [];
            }
            
            // Remove BOM from first column if present
            if (!empty($header[0])) {
                // Remove UTF-8 BOM
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                // Remove visible BOM characters
                $header[0] = preg_replace('/^ï»¿/', '', $header[0]);
                // Remove any other invisible characters
                $header[0] = preg_replace('/^[\x00-\x1F\x80-\xFF]+/', '', $header[0]);
                $header[0] = trim($header[0]);
            }
            
            // Clean all header columns
            $header = array_map(function($col) {
                $col = preg_replace('/^\xEF\xBB\xBF/', '', $col); // Remove BOM
                $col = preg_replace('/^ï»¿/', '', $col); // Remove visible BOM
                $col = trim($col);
                return $col;
            }, $header);
            
            // Store original headers for error messages
            $originalHeaders = $header;
            
            // Normalize header - convert to lowercase and trim
            $normalizedHeader = array_map(function($col) {
                return strtolower(trim($col));
            }, $header);
            
            // Find column indices (case-insensitive with flexible matching)
            $mcodeIndex = false;
            $mnameIndex = false;
            $semesterIndex = false;
            $creditIndex = false;
            
            // Possible variations for mcode
            $mcodeVariations = ['mcode', 'module code', 'code', 'module_code', 'modulecode', 'modcode'];
            // Possible variations for mname
            $mnameVariations = ['mname', 'module name', 'name', 'module_name', 'modulename', 'modname', 'module title', 'title'];
            
            foreach ($normalizedHeader as $index => $col) {
                $col = strtolower(trim($col));
                
                // Check mcode variations
                if (in_array($col, $mcodeVariations) || preg_match('/^m[\s_]?code$/i', $col)) {
                    $mcodeIndex = $index;
                }
                // Check mname variations
                elseif (in_array($col, $mnameVariations) || preg_match('/^m[\s_]?name$/i', $col)) {
                    $mnameIndex = $index;
                }
                // Check semester
                elseif ($col == 'semester' || $col == 'sem') {
                    $semesterIndex = $index;
                }
                // Check credit
                elseif ($col == 'credit' || $col == 'credits' || $col == 'credit hours') {
                    $creditIndex = $index;
                }
            }
            
            // Validate required columns exist
            if ($mcodeIndex === false || $mnameIndex === false) {
                // Clean headers for display - show both original and normalized
                $displayHeaders = [];
                $normalizedDisplay = [];
                
                if (!empty($originalHeaders) && is_array($originalHeaders)) {
                    foreach ($originalHeaders as $h) {
                        $h = trim($h);
                        if (!empty($h)) {
                            // Show hex representation if contains special chars
                            if (preg_match('/[^\x20-\x7E]/', $h)) {
                                $hex = bin2hex($h);
                                $displayHeaders[] = htmlspecialchars($h) . ' [hex: ' . substr($hex, 0, 20) . '...]';
                            } else {
                                $displayHeaders[] = htmlspecialchars($h);
                            }
                        }
                    }
                }
                
                if (!empty($normalizedHeader) && is_array($normalizedHeader)) {
                    foreach ($normalizedHeader as $h) {
                        if (!empty(trim($h))) {
                            $normalizedDisplay[] = htmlspecialchars($h);
                        }
                    }
                }
                
                $foundHeaders = !empty($displayHeaders) ? implode(', ', $displayHeaders) : '(empty or could not read)';
                $normalizedHeaders = !empty($normalizedDisplay) ? implode(', ', $normalizedDisplay) : '(empty)';
                
                $missingColumns = [];
                if ($mcodeIndex === false) $missingColumns[] = 'mcode';
                if ($mnameIndex === false) $missingColumns[] = 'mname';
                
                $columnCount = !empty($originalHeaders) ? count($originalHeaders) : 0;
                
                $_SESSION['error'] = "CSV file must contain 'mcode' and 'mname' columns in the header row.<br><br>" .
                                    "<strong>Missing columns:</strong> " . implode(', ', $missingColumns) . "<br><br>" .
                                    "<strong>Found headers (original):</strong> " . $foundHeaders . "<br>" .
                                    "<strong>Found headers (normalized):</strong> " . $normalizedHeaders . "<br>" .
                                    "<strong>Number of columns detected:</strong> " . $columnCount . "<br><br>" .
                                    "<strong>Expected format:</strong> <code>mcode,mname,semester,credit</code><br><br>" .
                                    "<strong>Tip:</strong> Please download the sample CSV file to see the exact format required.";
                fclose($handle);
                return [];
            }
            
            // Read data rows (skip header row)
            $rowNumber = 1;
            $dataRowsFound = 0;
            $emptyRowsSkipped = 0;
            
            while (($row = fgetcsv($handle, 0, $delimiter, '"')) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty($row) || !is_array($row)) {
                    $emptyRowsSkipped++;
                    continue;
                }
                
                // Check if row has only empty values
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty(trim($cell))) {
                        $hasData = true;
                        break;
                    }
                }
                
                if (!$hasData) {
                    $emptyRowsSkipped++;
                    continue;
                }
                
                // Ensure row has enough columns
                $maxIndex = max($mcodeIndex, $mnameIndex, $semesterIndex !== false ? $semesterIndex : -1, $creditIndex !== false ? $creditIndex : -1);
                if (count($row) <= $maxIndex) {
                    continue;
                }
                
                // Extract data based on column indices
                $mcode = isset($row[$mcodeIndex]) ? trim($row[$mcodeIndex]) : '';
                $mname = isset($row[$mnameIndex]) ? trim($row[$mnameIndex]) : '';
                $semester = ($semesterIndex !== false && isset($row[$semesterIndex])) ? trim($row[$semesterIndex]) : '';
                $credit = ($creditIndex !== false && isset($row[$creditIndex])) ? trim($row[$creditIndex]) : '';
                
                // Only add if mcode and mname are not empty
                if (!empty($mcode) && !empty($mname)) {
                    $modules[] = [
                        'mcode' => $mcode,
                        'mname' => $mname,
                        'semester' => $semester,
                        'credit' => $credit
                    ];
                    $dataRowsFound++;
                }
            }
            
            fclose($handle);
            
            if ($dataRowsFound == 0) {
                $_SESSION['error'] = "No valid data rows found in the CSV file.<br><br>" .
                                    "<strong>Rows processed:</strong> " . ($rowNumber - 1) . "<br>" .
                                    "<strong>Empty rows skipped:</strong> " . $emptyRowsSkipped . "<br>" .
                                    "<strong>Valid rows found:</strong> 0<br><br>" .
                                    "Please ensure your CSV file contains data rows with mcode and mname values after the header row.";
                return [];
            }
            
        } else {
            $_SESSION['error'] = "Please upload a CSV file (.csv extension). Excel files (.xlsx, .xls) are not supported. Please save your Excel file as CSV format.";
            return [];
        }
        
        return $modules;
    }

    public function getVersionsByCourse() {
        $this->requireAdminOrTeacher();
        
        $course_id = $_GET['course_id'] ?? 0;
        
        if ($course_id > 0) {
            $versions = $this->version->getAllVersions(['course_id' => $course_id]);
            header('Content-Type: application/json');
            echo json_encode($versions);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit();
    }

    public function downloadSample() {
        $this->requireAdminOrTeacher();
        
        // Sample data with proper spelling and formatting - NO SPACES, NO BLANK LINES
        $sampleData = [
            ['mcode', 'mname', 'semester', 'credit'],
            ['CS101', 'Introduction to Programming', '1', '3.0'],
            ['CS102', 'Data Structures and Algorithms', '2', '3.5'],
            ['MA201', 'Linear Algebra', '3', '3.0'],
            ['PH105', 'Physics Fundamentals', '1', '2.5'],
            ['EN101', 'English Communication', '1', '2.0'],
            ['CS201', 'Database Systems', '3', '3.0'],
            ['CS202', 'Web Development', '4', '3.5'],
            ['MA301', 'Calculus', '2', '3.0']
        ];
        
        // Clean any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for CSV download - MUST be first output
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="module_import_sample.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output CSV - use php://output for direct download
        $output = fopen('php://output', 'w');
        
        // DO NOT add BOM - it causes encoding issues
        // Write header row first (this is critical - must be exact: mcode,mname,semester,credit)
        // NO SPACES before or after
        fputcsv($output, $sampleData[0], ',', '"');
        
        // Write data rows
        for ($i = 1; $i < count($sampleData); $i++) {
            fputcsv($output, $sampleData[$i], ',', '"');
        }
        
        fclose($output);
        exit();
    }
}
?>
