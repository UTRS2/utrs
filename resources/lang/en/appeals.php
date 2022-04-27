<?php

return [
    'closed-notice' => 'This appeal is closed. No further changes can be made to it.',
    'no-action' => 'You are not permitted to perform any actions on this appeal.',
    'spam'      => 'It has been detected that you or someone else is trying to spam our system with appeals. Please wait until your previous appeal is closed, or if it is already closed, please try again later. If you are applying for an unblock of an IP address, this could mean that an appeal has already been submitted for your IP. In this case, please try again later or contact us to help clarify the issue.',

    'not-found-text'      => 'We were not able to locate your block. Please :link to correct the information in your appeal.',
    'not-found-link-text' => 'click here',
    'not-found-button'    => 'Fix block information',

    'appeal-title' => 'Appeal for ":name"',
    'appeal-number' => 'Appeal number',
    'appeal-for'    => 'Appeal for',
    'appeal-none'   => 'None',

    'comments' => [
        'system'    => 'System',
        'restricted' => 'Access to comment is restricted.',
        'action'    => 'Action',
        'reason'    => 'Reason',
        'leave' => 'Leave a comment',
        'add' => 'Add a comment to this appeal',
    ],

    'appeal-types' => [
        'ip'        => 'IP address',
        'account'   => 'Account',
        'ip-under'  => 'IP address underneath an account',
        'unknown'   => 'Unknown type',
        'assigned-me' => 'Assigned to me',
        'unassigned'=>'All unreserved open appeals',
        'reserved'=>'Open reserved appeals',
        'developer'=>'Developer access appeals',
    ],

    'send-reply-header'  => 'Send a templated reply',
    'send-reply-button'  => 'Send a reply to the user',
    'not-handling-admin' => 'You are not the handling admin.',

    'details-status'         => 'Appeal status',
    'details-block-admin'    => 'Blocking administrator',
    'details-block-reason'   => 'Block reason',
    'details-submitted'      => 'Time submitted',
    'details-handling-admin' => 'Handling administrator',

    'header-previous-appeals' => 'Previous appeals',
    'content-question-why' => 'Why should you be unblocked?',

    'comment-color-text' => 'Lines that are in blue indicate a response to or from the user. Lines in green are private comments from administrators.',
    'comment-input-text' => 'Add a comment to this appeal:',

    'verify' => [
        'verified' => 'This appeal has been verified to the account on the wiki.',
        'not-verified' => 'This appeal has not been or will not be verified to the account on the wiki.',
    ],

    'links' => [
        'user-talk' => 'User talk',
        'contribs'  => 'Contributions',
        'find-block'=> 'Find block',
        'block-log' => 'Block log',
        'ca'        => 'CentralAuth',
        'unblock'   => 'Unblock',
        'reopen'    => 'Re-open',
        'reserve'   => 'Reserve',
        'release'   => 'Release',
        'force'     => 'Force',
        'invalidate'=> 'Oversight',
        'accept'    => 'Accept appeal',
        'decline'   => 'Decline appeal',
        'checkuser' => 'CheckUser',
        'tooladmin' => 'Tool administrator',
        'expire'    => 'Mark appeal as expired',
        'return'    => 'Return to tool users',
        'reverify'  => 'Reverify appeal',
        'advance-search' => 'Advanced search',

    ],

    'cu' => [
        'data-expire'   => 'The CU data for this appeal has expired.',
        'no-request'    => 'You have not submitted a request to view the CheckUser data yet.',
        'reason'        => 'Reason',
        'submit'        => 'Submit',
        'title'         => 'CheckUser data',
        'review-req'    => 'What would you like the checkuser to review in this appeal?',
        'submit-title'  => 'Submit to CheckUser review',
        'user-ip'       => 'User inputted IP',
        'under-ip'      => 'This appeal has an underlying IP that may be blocked. You may need to consult a CheckUser.',
    ],
    
    'nav' => [
        'back-appeal-list' => 'Go back to appeal list',
    ],

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

    'publicheader' => [
        'afterfile' => 'After filing this appeal, you will get an appeal key. You will have to check back here for updates.

                        An administrator will look at your request in due time. Depending on which language and site you are
                        appealing from, appeal times may vary excessively.

                        Please note, any text you input for your appeal you agree to release under a public domain licence so that it can be
                        copied over to Wikipedia if needed. If you do not agree, do not file an appeal.

                        If you have any questions, you can contact us. Please note: We will not expedite, approve, deny, or edit
                        your appeal. It is for information only.',
        'appealkey' => 'On the next page, you will be issued a Appeal Key. Keep it in a safe place. If you forget it, you are able
                        to recover it, but only if your Wikimedia Account has a valid email address. DO NOT SHARE this key with
                        anyone.'
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

        'admin-only-notice' => 'Only administrators will be able to see your appeal.',
        'word-notice'       => 'There is a 4,000 word maximum in this textbox. If you go over it, you will be prevented from filing an appeal.',
        'question-why'      => 'Why should you be unblocked?',

        'verify-secret'      => 'Appeal key',
        'verify-secret-help' => 'You should have received this when you created your appeal.',
    ],

    'key' => [
        'header'              => 'Appeal submitted',
        'do-not-lose'         => 'Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.',
        'your-key-is'         => 'Your Appeal key is:',
        'view-appeal-details' => 'View appeal details',
    ],

    'wrong-key' => [
        'title' => 'Your appeal key appears to be wrong.',
        'text' => 'No appeals could be located using that appeal key. Please check again.',
    ],
];
