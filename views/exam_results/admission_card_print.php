<?php
if (!isset($cards) || !isset($collegeName) || !isset($examTitle) || !isset($periodLabel)) return;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Cards</title>
    <style>
        @page { size: A4; margin: 1.2cm; }
        body { font-family: 'Times New Roman', serif; margin: 0; padding: 15px; font-size: 11pt; }
        .card { page-break-after: always; max-width: 21cm; margin: 0 auto 25px; padding: 24px; border: 1px solid #333; box-sizing: border-box; }
        .card:last-child { page-break-after: auto; }
        .header-wrap { display: table; width: 100%; margin-bottom: 20px; table-layout: fixed; }
        .header-logo-left { display: table-cell; width: 90px; vertical-align: middle; text-align: left; }
        .header-logo-right { display: table-cell; width: 90px; vertical-align: middle; text-align: right; }
        .header-logo-left img, .header-logo-right img { max-height: 52px; max-width: 80px; width: auto; height: auto; object-fit: contain; display: block; }
        .header-logo-right img { margin-left: auto; }
        .header-center { display: table-cell; text-align: center; vertical-align: middle; padding: 0 12px; }
        .header { text-align: center; margin-bottom: 0; }
        .header h1 { font-size: 16pt; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px; }
        .header .college { font-size: 12pt; font-weight: bold; margin-bottom: 2px; }
        .header .exam-title { font-size: 11pt; margin: 2px 0; }
        .student-info { margin: 16px 0; display: table; width: 100%; }
        .student-info .row { display: table-row; }
        .student-info .label { display: table-cell; width: 90px; font-weight: bold; padding: 5px 10px 5px 0; vertical-align: top; }
        .student-info .value { display: table-cell; padding: 5px 0; }
        .instructions { margin: 14px 0; font-size: 10pt; line-height: 1.45; }
        .instructions ul { margin: 6px 0 0 20px; padding: 0; }
        .note { margin: 12px 0; padding: 10px 12px; background: #f8f9fa; font-size: 10pt; border-left: 3px solid #333; line-height: 1.4; }
        table.schedule { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 10pt; }
        table.schedule th, table.schedule td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        table.schedule th { background: #f0f0f0; font-weight: bold; }
        table.schedule .col-date { width: 14%; }
        table.schedule .col-level { width: 12%; }
        table.schedule .col-module { width: 38%; }
        table.schedule .col-sig { width: 18%; }
        .signatures { margin-top: 28px; display: table; width: 100%; font-size: 10pt; table-layout: fixed; }
        .signatures .row { display: table-row; }
        .signatures .cell { display: table-cell; vertical-align: top; width: 50%; padding-right: 24px; }
        .signatures .cell:last-child { padding-right: 0; padding-left: 24px; }
        .signatures .cell strong { display: block; margin-bottom: 4px; font-size: 10pt; }
        .signatures .sig-body { min-height: 44px; margin-bottom: 6px; }
        .signatures .digitalsign { margin-top: 0; margin-bottom: 0; }
        .signatures .digitalsign img { max-height: 40px; max-width: 180px; width: auto; height: auto; object-fit: contain; display: block; }
        .signatures .underline { border-bottom: 1px solid #333; height: 28px; margin-top: 0; width: 100%; max-width: 220px; }
        @media print { .card { page-break-after: always; border: none; } .card:last-child { page-break-after: auto; } body { padding: 0; } }
    </style>
</head>
<body>
<?php foreach ($cards as $card):
    $student = $card['student'];
    $exams = $card['exams'];
    $studentName = htmlspecialchars($student['fullname'] ?? '');
    $regNo = htmlspecialchars($student['reg_no'] ?? '');
?>
    <div class="card">
        <div class="header-wrap">
            <div class="header-logo-left">
                <img src="views/includes/img/ucjlogo.jpg" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="header-center">
                <div class="header">
                    <h1>ADMISSION CARD</h1>
                    <div class="college"><?php echo htmlspecialchars($collegeName); ?></div>
                    <div class="exam-title"><?php echo htmlspecialchars($examTitle); ?></div>
                </div>
            </div>
            <div class="header-logo-right">
                <img src="views/includes/img/univertech logo.jpg" alt="Logo" onerror="this.style.display='none'">
            </div>
        </div>

        <div class="student-info">
            <div class="row">
                <span class="label">Name:</span>
                <span class="value"><?php echo $studentName; ?></span>
            </div>
            <div class="row">
                <span class="label">Reg. No:</span>
                <span class="value"><?php echo $regNo; ?></span>
            </div>
        </div>

        <div class="instructions">
            <strong>Instructions:</strong>
            <ul>
                <li>No candidate will be admitted to the Examination hall without this card.</li>
                <li>Candidates are requested to hand over this card to the Supervisor of their Examination Centre on the day of the last paper.</li>
                <li>Supervisors are kindly requested to initial in the space provided for which the candidate is present.</li>
            </ul>
        </div>
        <div class="note">
            <strong>Important Note:</strong> Candidates will not be allowed to take any Unauthorized Materials, Mobile Phones and Programmable Calculators into the Examination Hall. You are also requested to follow the precaution procedures of the COVID-19 during your Examination.
        </div>

        <table class="schedule">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-level">Level</th>
                    <th class="col-module">Module / Title of paper</th>
                    <th class="col-sig">Candidate's Signature</th>
                    <th class="col-sig">Supervisor's Signature and Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam):
                    $examDate = $exam['exam_date'] ?? '';
                    $dateFormatted = $examDate ? date('d.m.Y', strtotime($examDate)) : '-';
                    $level = htmlspecialchars($exam['semester'] ?? '');
                    $moduleTitle = htmlspecialchars(trim(($exam['module_name'] ?? '') . ' ' . ($exam['module_code'] ?? '')));
                    if (empty(trim($moduleTitle))) $moduleTitle = '-';
                ?>
                    <tr>
                        <td><?php echo $dateFormatted; ?></td>
                        <td><?php echo $level ?: '-'; ?></td>
                        <td><?php echo $moduleTitle; ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="signatures">
            <div class="row">
                <div class="cell">
                    <strong>Assistant Registrar</strong>
                    <div class="sig-body">
                        <div class="digitalsign">
                            <img src="views/includes/img/digitalsign.jpg" alt="Registrar Signature" onerror="this.style.display='none'">
                        </div>
                    </div>
                    <div class="underline"></div>
                </div>
                <div class="cell">
                    <strong>Specimen Signature of the Candidate</strong>
                    <div class="sig-body"></div>
                    <div class="underline"></div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>
