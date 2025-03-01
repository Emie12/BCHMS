<?php
$servername = "localhost";
$username = "u160512276_admin";
$password = "Syst3m_123";
$dbname = "u160512276_fingerprint";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} else {
    die("Student ID not provided.");
}

// Get student information
$sqlStudent = "SELECT 
    students_tb.id AS student_id,
    students_tb.firstname,
    students_tb.lastname,
    students_tb.middlename,
    students_tb.gender,
    students_tb.birthday,
    students_tb.address,
    students_tb.lrn,
    students_tb.stud_section_code,
    section_tb.section AS section_name,
    students_tb.recipient AS guardian,
    students_tb.recipient_mobile_no AS occupation
FROM 
    students_tb
LEFT JOIN section_tb ON students_tb.stud_section_code = section_tb.section_code
WHERE 
    students_tb.lrn = '$student_id'";
$resultStudent = $conn->query($sqlStudent);
if (!$resultStudent) {
    die("Error in query: " . $conn->error);
}
if ($resultStudent->num_rows > 0) {
    $student = $resultStudent->fetch_assoc();
} else {
    echo "No data found!";
    exit();
}

// Get all subjects (to list all learning areas) grouped by level
$sqlSubjects = "SELECT * FROM subject_tb WHERE has_grade != 'No' ORDER BY level, subject";
$resultSubjects = $conn->query($sqlSubjects);
$subjectsByLevel = [];
if ($resultSubjects && $resultSubjects->num_rows > 0) {
    while ($subject = $resultSubjects->fetch_assoc()) {
        $subjectsByLevel[$subject['level']][] = $subject;
    }
}

// Get the student's grade data (if available) for each subject.
// We use a LEFT JOIN so that if no grade exists, the subject is still listed.
$sqlGrades = "SELECT 
    subject_tb.id as subject_id, 
    student_grade.student_grade_q1,
    student_grade.student_grade_q2,
    student_grade.student_grade_q3,
    student_grade.student_grade_q4,
    student_grade.student_grade_remarks
FROM subject_tb
LEFT JOIN schedules ON subject_tb.id = schedules.subject_id
LEFT JOIN student_grade ON schedules.schedule_id = student_grade.schedule_id 
    AND student_grade.student_id = '{$student['student_id']}'
ORDER BY subject_tb.level, subject_tb.subject";
$resultGrades = $conn->query($sqlGrades);
$grades = [];
if ($resultGrades && $resultGrades->num_rows > 0) {
    while($rowGrade = $resultGrades->fetch_assoc()){
         $grades[$rowGrade['subject_id']] = $rowGrade;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>FORM 137-E (Elementary School Permanent Record)</title>
  <style>
    /* Set page size for printing */
    @page {
      size: 8in 13in;
      margin: 10mm;
    }
    /* Ensure table lines are printed */
    @media print {
      body {
        margin: 0;
        zoom: 1;
      }
      table {
        border-collapse: collapse;
      }
      /* Prevent elements from breaking across pages */
      tr, td, th {
        page-break-inside: avoid;
      }
    }
    /* General styling */
    table, th, td {
      border: 1px solid #000;
    }
    th, td {
      padding: 5px;
    }
  </style>
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
  <!-- Header with logos -->
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <img src="https://logodix.com/logo/1355314.png" alt="Left Logo" style="height: 80px;">
    <div style="text-align: center;">
      <h3>FORM 137-E</h3>
      <p style="margin: 0; padding: 0;">Republika ng Pilipinas (Republic of the Philippines)</p>
      <p style="margin: 0; padding: 0;">Kagawaran ng Edukasyon (Department of Education)</p>
      <p style="margin: 0; padding: 0;">Kawanihan ng Edukasyong Elementarya (Bureau of Elementary Education)</p>
      <p style="margin: 0; padding: 0;">Rehiyon X</p>
    </div>
    <img src="https://red-mantis-106658.hostingersite.com/assets/admin/img/logo.jpg" alt="Right Logo" style="height: 80px;">
  </div>
  <hr style="margin: 20px 0;">
  <div style="text-align: center;">
    <h2 style="margin-top: 10px;">
      Holy Child Montessori<br>
      <small>(Elementary School Permanent Record)</small>
    </h2>
    <p style="margin: 0; padding: 0;">LRN: <?php echo htmlspecialchars($student['lrn']); ?></p>
  </div>
  <br>
  <div style="margin-top: 20px;">
    <table border="0" style="width: 100%; border-collapse: collapse; margin-bottom: 1em;">
      <tr>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Apelyido (Surname):</label><br>
          <span><?php echo htmlspecialchars($student['lastname']); ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Unang Pangalan (First Name):</label><br>
          <span><?php echo htmlspecialchars($student['firstname']); ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Gitnang Pangalan (Middle Name):</label><br>
          <span><?php echo htmlspecialchars($student['middlename']); ?></span>
        </td>
      </tr>
      <tr>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Kasarian (Sex):</label><br>
          <span><?php echo htmlspecialchars($student['gender']); ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Petsa ng Kapanganakan (Date of Birth):</label><br>
          <span><?php echo htmlspecialchars($student['birthday']); ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Pook (Place of Birth):</label><br>
          <span>[Not Provided]</span>
        </td>
      </tr>
      <tr>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Magulang / Tagapag-alaga (Parent/Guardian):</label><br>
          <span><?php echo htmlspecialchars($student['guardian']); ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px;">
          <label style="font-weight: bold;">Hanapbuhay (Occupation):</label><br>
          <span><?php echo htmlspecialchars($student['occupation']); ?></span>
        </td>
      </tr>
    </table>
  </div>
  <div style="font-weight: bold; text-align: center; margin-top: 30px; margin-bottom: 10px;">
    PAG-UNLAD SA MABABANG PAARALAN<br>
    <small>(Elementary School Progress)</small>
  </div>
  <!-- Display all grades by level -->
  <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <!-- Grade I -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade I</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px; width: 30%;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px; width: 15%;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px; width: 5%;">1</th>
            <th style="padding: 5px; width: 5%;">2</th>
            <th style="padding: 5px; width: 5%;">3</th>
            <th style="padding: 5px; width: 5%;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
if (isset($subjectsByLevel['1'])):
    foreach ($subjectsByLevel['1'] as $subject):
        $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
?>
<tr>
    <td><?php echo htmlspecialchars($subject['subject']); ?></td>
    <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
    <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
    <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
    <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
    <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
</tr>
<?php 
    endforeach;
else:
    echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade I.</td></tr>";
endif;
?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
    <!-- Grade II -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade II</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px; width: 30%;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px; width: 15%;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px;">1</th>
            <th style="padding: 5px;">2</th>
            <th style="padding: 5px;">3</th>
            <th style="padding: 5px;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (isset($subjectsByLevel['2'])):
              foreach ($subjectsByLevel['2'] as $subject):
                  $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($subject['subject']); ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
          </tr>
          <?php 
              endforeach;
          else:
              echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade II.</td></tr>";
          endif;
          ?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
  </div>
  <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <!-- Grade III -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade III</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px;">1</th>
            <th style="padding: 5px;">2</th>
            <th style="padding: 5px;">3</th>
            <th style="padding: 5px;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (isset($subjectsByLevel['3'])):
              foreach ($subjectsByLevel['3'] as $subject):
                  $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($subject['subject']); ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
          </tr>
          <?php 
              endforeach;
          else:
              echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade III.</td></tr>";
          endif;
          ?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
    <!-- Grade IV -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade IV</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px;">1</th>
            <th style="padding: 5px;">2</th>
            <th style="padding: 5px;">3</th>
            <th style="padding: 5px;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (isset($subjectsByLevel['4'])):
              foreach ($subjectsByLevel['4'] as $subject):
                  $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($subject['subject']); ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
          </tr>
          <?php 
              endforeach;
          else:
              echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade IV.</td></tr>";
          endif;
          ?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
  </div>
  <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <!-- Grade V -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade V</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px;">1</th>
            <th style="padding: 5px;">2</th>
            <th style="padding: 5px;">3</th>
            <th style="padding: 5px;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (isset($subjectsByLevel['5'])):
              foreach ($subjectsByLevel['5'] as $subject):
                  $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($subject['subject']); ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
          </tr>
          <?php 
              endforeach;
          else:
              echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade V.</td></tr>";
          endif;
          ?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
    <!-- Grade VI -->
    <div style="width: 48%;">
      <p style="margin: 0; font-weight: bold;">Grade VI</p>
      <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <thead>
          <tr>
            <th rowspan="2" style="padding: 5px;">LEARNING AREAS</th>
            <th colspan="4" style="padding: 5px; text-align: center;">Periodic Rating</th>
            <th rowspan="2" style="padding: 5px;">Remarks</th>
          </tr>
          <tr>
            <th style="padding: 5px;">1</th>
            <th style="padding: 5px;">2</th>
            <th style="padding: 5px;">3</th>
            <th style="padding: 5px;">4</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (isset($subjectsByLevel['6'])):
              foreach ($subjectsByLevel['6'] as $subject):
                  $grade = isset($grades[$subject['id']]) ? $grades[$subject['id']] : null;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($subject['subject']); ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q1']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q2']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q3']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_q4']) : ''; ?></td>
            <td><?php echo $grade ? htmlspecialchars($grade['student_grade_remarks']) : ''; ?></td>
          </tr>
          <?php 
              endforeach;
          else:
              echo "<tr><td colspan='6' style='text-align:center;'>No subjects found for Grade VI.</td></tr>";
          endif;
          ?>
          <tr>
            <td style="font-weight: bold;">General Average</td>
            <td colspan="4"></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0;">Eligible for admission to Grade: <span style="border-bottom: 1px solid #000; padding: 0 10px;">__________</span></p>
    </div>
  </div>
</body>
</html>
