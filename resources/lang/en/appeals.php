<?php

return [
    'closed-notice' => 'This appeal is closed. No further changes can be made to it.',
    'no-action' => 'You are not permitted to perform any actions on this appeal.',
    'spam'      => 'It has been detected that you or someone else may be trying to spam our system with appeals. Please wait until your previous appeal is closed, or if it is already closed, please try again about 2 days later. If you are applying for an unblock of an IP address, this could mean that an appeal has already been submitted for your IP. In this case, please try again later or contact us to help clarify the issue. Email:',
    'comment-spam' => 'You have reached the maximum number of comments you can make in a 24 hour period or on this appeal. Please try again later or contact us to help clarify the issue. Email:',

    'not-found-text'      => 'We were not able to locate your block. Please click the button below to correct the information in your appeal.',
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

    'status' => [
        'OPEN'              => 'Open',
        'AWAITING_REPLY'    => 'Awaiting reply',
        'ACCEPT'            => 'Accepted',
        'DECLINE'           => 'Declined',
        'EXPIRE'            => 'Expired',
        'INVALID'           => 'Oversighted',
        'NOTFOUND'          => 'Block not found',
        'VERIFY'            => 'Needing verification',
        'CHECKUSER'         => 'Needing a checkuser',
        'ADMIN'             => 'Needing an administrator',
    ],

    'appeal-types' => [
        'title'     => 'Appeal Type',
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

    'details-status'         => 'Appeal number & status',
    'details-block-admin'    => 'Blocking administrator',
    'details-block-reason'   => 'Block reason',
    'details-submitted'      => 'Time submitted',
    'details-handling-admin' => 'Handling administrator',

    'header-previous-appeals' => 'Previous appeals',
    'content-question-why' => 'Why should you be unblocked?',

    'comment-color-text' => 'Lines that are in blue indicate a response to or from the user. Lines in green are private comments from administrators.',
    'comment-input-text' => 'Add a comment to this appeal:',

    'verify' => [
        'negativeaction' => 'This appeal has not been verified to match the user on wiki. Do not take any negative action towards the user based on this appeal without having a CheckUser review.',
        'ip-emailverified' => 'The appeal has an email address which has been verified.',
        'notableverified' => 'This appeal will not be able to be verified.',
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
        'transfer'  => 'Transfer to another wiki',
        'transfer-to' => 'Transfer this to',
        'cancel'    => 'Cancel',

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
        'ip-address'    => 'IP address: :ip',
        'user-agent'    => 'User agent: :ua',
        'browser-lang'  => 'Browser language: :lang',
    ],
    
    'nav' => [
        'back-appeal-list' => 'Go back to appeal list',
    ],

    'section-headers' => [
        'details'     => 'Appeal details',
        'content'     => 'Appeal content',
        'comments'    => 'Admin comments',
        'add-comment' => 'Add a comment',
        'actions'     => 'Actions',
        'status'      => 'Appeal status',
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
        'word-notice'       => 'There is a 4,000 character maximum in this textbox. If you go over it, you will be prevented from filing an appeal.',
        'question-why'      => 'Why should you be unblocked?',

        'verify-secret'      => 'Appeal key',
        'verify-secret-help' => 'You should have received this when you created your appeal.',
    ],

    'key' => [
        'header'              => 'Appeal submitted',
        'do-not-lose'         => 'Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.',
        'your-key-is'         => 'Your Appeal key is:',
        'view-appeal-details' => 'View appeal details',
        'proxyask'            => 'If you are using a proxy, please specify below why you need to use one. Please include specific reasons as to why, as a generic reason of privacy is not acceptable per the policy at the following link:',
    ],

    'wrong-key' => [
        'title' => 'Your appeal key appears to be wrong.',
        'text' => 'No appeals could be located using that appeal key. Please check again.',
    ],

    'proxy' => [
        'unlikelyproxy' => 'This appeal is unlikely to have come from a proxy or VPN.',
        'likelyproxy'   => 'This appeal is likely to have come from a proxy or VPN.',
    ],

    'map' => [
        'reviewappeal' => 'Review this appeal',
        'submitted' => 'Appeal submitted #:id',
        'assigned' => 'Appeal assigned to an administrator',
        'verified' => 'Appeal verified to an email',
        'respond' => 'The administrator responded with:',
        'released' => 'The appeal has been returned to the queue for a new administrator to review',
        'reopen' => 'The appeal has been reopened or returned for administrator to review',
        'transfer' => 'The appeal has been transferred to another wiki for review',
        'checkuser' => 'The appeal has been sent to a checkuser for review',
        'admin' => 'The appeal has been sent to a tool administrator for review',
        'verifiedaccount' => 'The appeal has been verified to an account',
        'awaitreply' => 'The administrator requested a reply from you',
        'declined' => 'The administrator declined your appeal',
        'expired' => 'Your appeal has been closed due to inactivity',
        'accepted' => 'Your appeal has been granted',
        'invalid' => 'Your appeal has been closed without review',
        'unhandled' => 'Unhandled status: :status',
        'switch-appeal-map' => 'Switch to appeal map',
    ],

    'template' => [
        'alert' => 'On this screen, you will see a list of templates to choose from in responding to a user. To use a template,
        click it\'s name.',
        'return-appeal' => 'Return to appeal',
        'reply-custom' => 'Reply with a custom message',
        'greeting' => 'Hello :name,',
    ]
];
