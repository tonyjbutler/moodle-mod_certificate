<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

// Date formatting - can be customized if necessary
$certificatedate = '';
if ($certrecord->certdate > 0) {
    $certdate = $certrecord->certdate;
} else {
    $certdate = certificate_generate_date($certificate, $course);
}
if ($certificate->printdate > 0) {
    if ($certificate->datefmt == 1) {
        $certificatedate = str_replace(' 0', ' ', strftime('%B %d, %Y', $certdate));
    } else if ($certificate->datefmt == 2) {
        $certificatedate = date('F jS, Y', $certdate);
    } else if ($certificate->datefmt == 3) {
        $certificatedate = str_replace(' 0', '', strftime('%d %B %Y', $certdate));
    } else if ($certificate->datefmt == 4) {
        $certificatedate = strftime('%B %Y', $certdate);
    } else if ($certificate->datefmt == 5) {
        $certificatedate = userdate($certdate, get_string('strftimedate', 'langconfig'));
    }
}

// Grade formatting
$grade = '';
// Print the course grade
$coursegrade = certificate_print_course_grade($course);
if ($certificate->printgrade == 1 && $certrecord->reportgrade) {
    $reportgrade = $certrecord->reportgrade;
    $grade = $strcoursegrade . ':  ' . $reportgrade;
} else if ($certificate->printgrade > 0) {
    if ($certificate->printgrade == 1) {
        if ($certificate->gradefmt == 1) {
            $grade = $strcoursegrade . ':  ' . $coursegrade->percentage;
        } else if ($certificate->gradefmt == 2) {
            $grade = $strcoursegrade . ':  ' . $coursegrade->points;
        } else if ($certificate->gradefmt == 3) {
            $grade = $strcoursegrade . ':  ' . $coursegrade->letter;
        }
    } else { // Print the mod grade
        $modinfo = certificate_print_mod_grade($course, $certificate->printgrade);
        if ($certrecord->reportgrade) {
            $modgrade = $certrecord->reportgrade;
            $grade = $modinfo->name . ' ' . $strgrade . ': ' . $modgrade;
        } else if ($certificate->printgrade > 1) {
            if ($certificate->gradefmt == 1) {
                $grade = $modinfo->name . ' ' . $strgrade . ': ' . $modinfo->percentage;
            } else if ($certificate->gradefmt == 2) {
                $grade = $modinfo->name . ' ' . $strgrade . ': ' . $modinfo->points;
            } else if ($certificate->gradefmt == 3) {
                $grade = $modinfo->name . ' ' . $strgrade . ': ' . $modinfo->letter;
            }
        }
    }
}
// Print the outcome
$outcome = '';
$outcomeinfo = certificate_print_outcome($course, $certificate->printoutcome);
if ($certificate->printoutcome > 0) {
    $outcome = $outcomeinfo->name . ': ' . $outcomeinfo->grade;
}

// Print the code number
$code = '';
if ($certificate->printnumber) {
    $code = $certrecord->code;
}

// Print the student name
$studentname = '';
$studentname = $certrecord->studentname;
$classname = '';
$classname = $certrecord->classname;
// Print the credit hours
if ($certificate->printhours) {
    $credithours = $strcredithours . ': ' . $certificate->printhours;
} else {
    $credithours = '';
}

$pdf = new TCPDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

// $pdf->SetProtection(array('print'));
$pdf->SetTitle($certificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 30;
    if ($certificate->printseal == 'College logo.png') {
        $sealx = 185;
        $sealy = 160;
        $sealh = 12;
    } else if ($certificate->printseal == 'CLE logo.png') {
        $sealx = 210;
        $sealy = 155;
        $sealh = 20;
    } else if ($certificate->printseal == 'CLE + SARAD logos.png') {
        $sealx = 195;
        $sealy = 155;
        $sealh = 20;
    } else {
        $sealx = 230;
        $sealy = 150;
        $sealh = '';
    }
    $sigx = 47;
    $sigy = 160;
    $custx = 47;
    $custy = 160;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
} else { //Portrait
    $x = 10;
    $y = 40;
    if ($certificate->printseal == 'College logo.png') {
        $sealx = 120;
        $sealy = 230;
        $sealh = 10;
    } else if ($certificate->printseal == 'CLE logo.png') {
        $sealx = 130;
        $sealy = 225;
        $sealh = 18;
    } else if ($certificate->printseal == 'CLE + SARAD logos.png') {
        $sealx = 115;
        $sealy = 225;
        $sealh = 18;
    } else {
        $sealx = 150;
        $sealy = 220;
        $sealh = '';
    }
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}

// Add images and lines
print_border($pdf, $certificate, $brdrx, $brdry, $brdrw, $brdrh);
draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
print_watermark($pdf, $certificate, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
print_seal($pdf, $certificate, $sealx, $sealy, '', $sealh);
print_signature($pdf, $certificate, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 120);
cert_printtext($pdf, $x, $y, 'C', 'freesans', '', 30, get_string('title', 'certificate'));
$pdf->SetTextColor(0, 0, 0);
cert_printtext($pdf, $x, $y + 20, 'C', 'freeserif', '', 20, get_string('certify', 'certificate'));
cert_printtext($pdf, $x, $y + 36, 'C', 'freesans', '', 30, $studentname);
cert_printtext($pdf, $x, $y + 55, 'C', 'freesans', '', 20, get_string('statement', 'certificate'));
cert_printtext($pdf, $x, $y + 72, 'C', 'freesans', '', 20, $classname);
cert_printtext($pdf, $x, $y + 92, 'C', 'freesans', '', 14, $certificatedate);
cert_printtext($pdf, $x, $y + 102, 'C', 'freeserif', '', 10, $grade);
cert_printtext($pdf, $x, $y + 112, 'C', 'freeserif', '', 10, $outcome);
cert_printtext($pdf, $x, $y + 122, 'C', 'freeserif', '', 10, $credithours);
cert_printtext($pdf, $x, $codey, 'C', 'freeserif', '', 10, $code);
$i = 0;
if ($certificate->printteacher) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            cert_printtext($pdf, $sigx, $sigy + ($i * 4), 'L', 'freeserif', '', 12, fullname($teacher));
        }
    }
}

cert_printtext($pdf, $custx, $custy, 'L', '', '', '', $certificate->customtext);
?>