<?php

return [
    'closed-notice' => 'This appeal is closed. No further changes can be made to it.',

    'not-found-text'      => 'We were not able to locate your block. Please :link to correct the information in your appeal.',
    'not-found-link-text' => 'click here',
    'not-found-button'    => 'Fix block information',

    'appeal-title' => 'Appeal for ":name"',

    'details-status'         => 'Appeal status',
    'details-block-admin'    => 'Blocking administrator',
    'details-block-reason'   => 'Block reason',
    'details-submitted'      => 'Time submitted',
    'details-handling-admin' => 'Handling administrator',

    'content-question-why' => 'Why should you be unblocked?',

    'comment-color-text' => 'Lines that are in blue indicate a response to or from the user. Lines in green are private comments from administrators.',
    'comment-input-text' => 'Add a comment to this appeal:',

    'section-headers' => [
        'details'     => 'Appeal details',
        'content'     => 'Appeal content',
        'comments'    => 'Admin comments',
        'add-comment' => 'Add a comment',
    ],

    'status-texts' => [
        'ACCEPT'  => 'This appeal was approved.',
        'EXPIRE'  => 'This appeal expired.',
        'DECLINE' => 'This appeal was denied.',
        'INVALID' => 'This appeal was marked as invalid.',
        'default' => 'This appeal is in progress.',
    ],

    'forms' => [
        'header-account' => 'Appeal a block on an account',
        'header-ip'      => 'Appeal a block on an IP address',
        'header-verify'  => 'Verify account ownership',
        'header-modify'  => 'Modify appeal',

        'about-you'      => 'About you',
        'block-wiki'     => 'What wiki are you blocked on?',
        'block-username' => 'What is your username?',
        'block-ip'       => 'What is the blocked IP address?',

        'direct-question' => 'Is your account directly blocked?',
        'direct-yes'      => 'Yes',
        'direct-no'       => 'No, the underlying IP address is blocked',
        'direct-ip'       => 'No, I do not have an account',

        'edit-notice'       => 'You are now modifying your appeal to be resubmitted. Please ensure the information is correct.',
        'hiddenip-question' => 'If you selected ":option" above, what is the blocked IP?',

        'appeal-info' => 'Block appeal information',

        'admin-only-notice' => 'Only administrator will be able to see your appeal.',
        'word-notice'       => 'There is a 4,000 word maximum in this textbox. If you go over it, you will be prevented from filing an appeal.',
        'question-why'      => 'Why should you be unblocked?',

        'verify-secret'      => 'Appeal key',
        'verify-secret-help' => 'You should have received this when you created your appeal.',
    ],
];
