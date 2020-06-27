<?php

return [
    'closed-notice' => 'Shown as an alert to users when the appeal they are viewing is closed and can\'t be modified.',

    'not-found-text' => 'Shown as an alert to users when their block was not found. ":link" is will be replaced by a link to the page where they correct the information with "appeals.not-found-link-text" as the link text.',
    'not-found-link-text' => 'Link text for the "appeals.not-found-text" message.',
    'not-found-button'    => 'Button text to correct block information.',

    'appeal-title' => 'Header for block data. ":name" will be replaced by the user\'s name.',

    'details-status'         => 'Header in the block detail view.',
    'details-block-admin'    => 'Header in the block detail view.',
    'details-block-reason'   => 'Header in the block detail view.',
    'details-submitted'      => 'Header in the block detail view.',
    'details-handling-admin' => 'Header in the block detail view.',

    'content-question-why' => 'Question shown to users appealing their blocks.',

    'comment-color-text' => 'Text indicating colors in the block comments table.',
    'comment-input-text' => 'Label for the comment field.',

    'section-headers' => [
        'details'     => 'Shown as a section header on when viewing individual appeals.',
        'content'     => 'Shown as a section header on when viewing individual appeals.',
        'comments'    => 'Shown as a section header on when viewing individual appeals.',
        'add-comment' => 'Shown as a section header on when viewing individual appeals.',
    ],

    'status-texts' => [
        'ACCEPT'  => 'Shown as a status message in the interface.',
        'EXPIRE'  => 'Shown as a status message in the interface.',
        'DECLINE' => 'Shown as a status message in the interface.',
        'INVALID' => 'Shown as a status message in the interface.',
        'default' => 'Shown as a status message in the interface.',
    ],

    'forms' => [
        'header-account' => 'Shown as a header on when creating an appeal.',
        'header-ip'      => 'Shown as a header on when creating an appeal.',
        'header-verify'  => 'Shown as a header on when verifying account ownership.',
        'header-modify'  => 'Shown as a header on when modifying appeal details.',

        'about-you'      => 'Section header in appeal creation form.',
        'block-wiki'     => 'Field label in appeal creation form.',
        'block-username' => 'Field label in appeal creation form.',
        'block-ip'       => 'Field label in appeal creation form.',

        'direct-question' => 'Field label in appeal creation form.',
        'direct-yes'      => 'Select option in appeal creation form.',
        'direct-no'       => 'Select option in appeal creation form.',
        'direct-ip'       => 'Select option in appeal creation form.',

        'edit-notice'       => 'Information text shown when modifying appeal details.',
        'hiddenip-question' => 'Field label in appeal creation form. ":option" will be replaced by the message "appeals.form.direct-no".',

        'appeal-info' => 'Section header in appeal creation form.',

        'admin-only-notice' => 'Information text shown when creating an appeal.',
        'word-notice'       => 'Information text shown when creating an appeal.',
        'question-why'      => 'Field label in appeal creation form.',

        'verify-secret'      => 'Field label in appeal verification form.',
        'verify-secret-help' => 'Field information text in appeal verification form.',
    ],

    'key' => [
        'header'              => 'Header used in the screen giving user their appeal key',
        'do-not-lose'         => 'Information text shown along the appeal key.',
        'your-key-is'         => 'Information text shown along the appeal key.',
        'view-appeal-details' => 'Button text shown along the appeal key.',
    ],
];
