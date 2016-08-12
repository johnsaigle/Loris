<?php

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action == "candidateInfo") {
        editCandInfoFields();
    }
    else if ($action == "probandInfo") {
        editProbandInfoFields();
    }
    else if ($action == "familyInfo") {
        editFamilyInfoFields();
    }
    else if ($action == "participantStatus") {
        editParticipantStatusFields();
    }
    else if ($action == "consentStatus") {
        editConsentStatusFields();
    }
}

function editCandInfoFields() {
    $db   =& Database::singleton();
    $user =& User::singleton();
    if (!$user->hasPermission('candidate_parameter_edit')) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    $candID = $_GET['candID'];

    // Process posted data
    $caveatEmptor   = isset($_POST['flagged_caveatemptor']) ? $_POST['flagged_caveatemptor'] : null;
    $reason    = isset($_POST['flagged_reason']) ? $_POST['flagged_reason'] : null;
    $other    = isset($_POST['flagged_other']) ? $_POST['flagged_other'] : null;

    $updateValues = [
        'flagged_caveatemptor' => $caveatEmptor,
        'flagged_reason'   => $reason,
        'flagged_other'  => $other,
    ];

    $db->update('candidate', $updateValues, ['CandID' => $candID]);
}

function editProbandInfoFields() {
    $db   =& Database::singleton();
    $user =& User::singleton();
    if (!$user->hasPermission('candidate_parameter_edit')) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    $candID = $_GET['candID'];

    // Process posted data
    $gender   = isset($_POST['ProbandGender']) ? $_POST['ProbandGender'] : null;
    $dob    = isset($_POST['ProbandDoB']) ? $_POST['ProbandDoB'] : null;
    $dob2    = isset($_POST['ProbandDoB2']) ? $_POST['ProbandDoB2'] : null;

    if ($dob !== $dob2) {
        // throw error
    }

    $updateValues = [
        'ProbandGender' => $gender,
        'ProbandDoB'   => $dob,
    ];

    $db->update('candidate', $updateValues, ['CandID' => $candID]);
}

function editFamilyInfoFields() {
    $db   =& Database::singleton();
    $user =& User::singleton();
    if (!$user->hasPermission('candidate_parameter_edit')) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    $candID = $_GET['candID'];
}

function editParticipantStatusFields() {
    $db   =& Database::singleton();
    $user =& User::singleton();
    if (!$user->hasPermission('candidate_parameter_edit')) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    $candID = $_GET['candID'];
}

function editConsentStatusFields() {
    $db   =& Database::singleton();
    $user =& User::singleton();
    if (!$user->hasPermission('candidate_parameter_edit')) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    $candID = $_GET['candID'];
}