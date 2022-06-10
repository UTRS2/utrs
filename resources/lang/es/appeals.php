<?php
return [
    'closed-notice'           => 'Esta apelación está cerrada. No se pueden realizar más cambios.',
    'no-action'               => 'No tienes permitido realizar ninguna acción en esta apelación.',
    'spam'                    => 'Se ha detectado que tú u otra persona está intentando ahogar nuestro sistema con apelaciones. Espera hasta que se cierre tu apelación anterior o, si ya está cerrada, inténtalo de nuevo más tarde. Si estás solicitando el desbloqueo de una dirección IP, esto podría significar que ya se ha presentado una apelación para tu IP. En este caso, vuelve a intentarlo más tarde o comunícate con nosotros para ayudar a aclarar el problema.',
    'not-found-text'          => 'No pudimos localizar tu bloqueo. Por favor :link para corregir la información en tu apelación.',
    'not-found-link-text'     => 'haz clic aquí',
    'not-found-button'        => 'Corregir la información del bloqueo',
    'appeal-title'            => 'Apelación de «:name»',
    'appeal-number'           => 'Número de apelación',
    'appeal-for'              => 'Apelación de',
    'appeal-none'             => 'Ninguna',
    'comments'                => [
        'system'     => 'Sistema',
        'restricted' => 'El acceso a los comentarios está restringido.',
        'action'     => 'Acción',
        'reason'     => 'Motivo',
        'leave'      => 'Añadir comentario',
        'add'        => 'Añadir comentario a esta apelación:'
    ],
    'status'                  => [
        'OPEN'           => 'Abierto',
        'AWAITING_REPLY' => 'Esperando respuesta',
        'ACCEPT'         => 'Aceptado',
        'DECLINE'        => 'Rechazado',
        'EXPIRE'         => 'Expirado',
        'INVALID'        => 'Suprimido',
        'NOTFOUND'       => 'Bloqueo no encontrado',
        'VERIFY'         => 'Necesita verificación',
        'CHECKUSER'      => 'Necesita un CheckUser',
        'ADMIN'          => 'Necesita un administrador'
    ],
    'appeal-types'            => [
        'title'       => 'Tipo de apelación',
        'ip'          => 'Dirección IP',
        'account'     => 'Cuenta',
        'ip-under'    => 'Dirección IP detrás de una cuenta',
        'unknown'     => 'Tipo desconocido',
        'assigned-me' => 'Apelaciones asignadas',
        'unassigned'  => 'Todas las apelaciones no reservadas abiertas ',
        'reserved'    => 'Apelaciones reservadas abiertas',
        'developer'   => 'Apelaciones de acceso de desarrolladores'
    ],
    'send-reply-header'       => 'Enviar una respuesta con plantilla',
    'send-reply-button'       => 'Enviar una respuesta al usuario',
    'not-handling-admin'      => 'No eres el administrador tramitador.',
    'details-status'          => 'Estado de la apelación',
    'details-block-admin'     => 'Administrador que aplicó el bloqueo',
    'details-block-reason'    => 'Motivo del bloqueo',
    'details-submitted'       => 'Hora de envío',
    'details-handling-admin'  => 'Administrador tramitador',
    'header-previous-appeals' => 'Apelaciones anteriores',
    'content-question-why'    => '¿Por qué deberías ser desbloqueado?',
    'comment-color-text'      => 'Las líneas que están en color azul indican una respuesta para o del usuario. Las líneas que están en color verde son comentarios privados de los administradores.',
    'comment-input-text'      => 'Añadir comentario a esta apelación:',
    'verify'                  => [
        'verified'     => 'Esta apelación ha sido verificada a la cuenta en la wiki.',
        'not-verified' => 'Esta apelación no ha sido o no será verificada a la cuenta en la wiki.'
    ],
    'links'                   => [
        'user-talk'      => 'Discusión del usuario',
        'contribs'       => 'Contribuciones',
        'find-block'     => 'Buscar bloqueo',
        'block-log'      => 'Registro de bloqueos',
        'ca'             => 'CentralAuth',
        'unblock'        => 'Desbloquear',
        'reopen'         => 'Reabrir',
        'reserve'        => 'Reservar',
        'release'        => 'Liberar',
        'force'          => 'Forzar',
        'invalidate'     => 'Oversight
',
        'accept'         => 'Aceptar apelación',
        'decline'        => 'Rechazar apelación',
        'checkuser'      => 'CheckUser',
        'tooladmin'      => 'Administrador de herramientas',
        'expire'         => 'Marcar apelación como caducada',
        'return'         => 'Volver a herramientas de usuario',
        'reverify'       => 'Reverificar apelación',
        'advance-search' => 'Búsqueda avanzada'
    ],
    'cu'                      => [
        'data-expire'  => 'Los datos CheckUser para esta apelación han expirado.',
        'no-request'   => 'Aún no has enviado una solicitud para ver los datos de CheckUser.',
        'reason'       => 'Motivo',
        'submit'       => 'Enviar',
        'title'        => 'Data CheckUser',
        'review-req'   => '¿Qué te gustaría que el CheckUser revisara en esta apelación?',
        'submit-title' => 'Enviar a revisión de CheckUser',
        'user-ip'      => 'IP ingresada por el usuario',
        'under-ip'     => 'Esta apelación tiene una IP asociada que puede estar bloqueada. Es posible que deba consultar a un CheckUser.'
    ],
    'nav'                     => [
        'back-appeal-list' => 'Volver a la lista de apelaciones'
    ],
    'section-headers'         => [
        'details'     => 'Detalles de la apelación',
        'content'     => 'Contenido de la apelación',
        'comments'    => 'Comentarios del administrador',
        'add-comment' => 'Añadir comentario'
    ],
    'status-texts'            => [
        'ACCEPT'  => 'Esta apelación fue aprobada.',
        'EXPIRE'  => 'Esta apelación expiró.',
        'DECLINE' => 'Esta apelación fue rechazada.',
        'INVALID' => 'Esta apelación se marcó como inválida.',
        'default' => 'Esta apelación está en curso.'
    ],
    'publicheader'            => [
        'afterfile' => 'Después de presentar esta apelación, recibirás una clave de apelación. Tendrás que volver a consultar aquí para obtener actualizaciones. Un administrador revisará tu solicitud a su debido tiempo. Según el idioma y el sitio desde el que apeles, los tiempos de apelación pueden variar excesivamente. Ten en cuenta que aceptas publicar cualquier texto que ingrese para tu apelación bajo una licencia de dominio público para que pueda copiarse en Wikipedia si es necesario. Si no estás de acuerdo, no presentes una apelación. Si tienes alguna pregunta, puedes contactarnos. Ten en cuenta: No aceleraremos, aprobaremos, denegaremos ni editaremos tu apelación. Es sólo para información.',
        'appealkey' => 'En la página siguiente, se te dará una clave de apelación. Guárdala en un lugar seguro. Si la olvidas, puedes recuperarla, pero solo si tu cuenta de Wikimedia tiene una dirección de correo electrónico válida. NO COMPARTAS esta clave con nadie.'
    ],
    'forms'                   => [
        'header-account'     => 'Apelar un bloqueo en una cuenta',
        'header-ip'          => 'Apelar un bloqueo a una dirección IP',
        'header-verify'      => 'Verificar la propiedad de la cuenta',
        'header-modify'      => 'Modificar apelación',
        'about-you'          => 'Acerca de ti',
        'block-wiki'         => '¿En cuál wiki estás bloqueado?',
        'block-username'     => '¿Cual es tu nombre de usuario?',
        'block-ip'           => '¿Cuál es la dirección IP bloqueada?',
        'direct-question'    => '¿Tu cuenta está directamente bloqueada?',
        'direct-yes'         => 'Sí',
        'direct-no'          => 'No, la dirección IP asociada está bloqueada',
        'direct-ip'          => 'No, no tengo una cuenta.',
        'edit-notice'        => 'Ahora estás modificando tu apelación para volver a enviarla. Por favor, asegúrate de que la información sea correcta.',
        'hiddenip-question'  => 'Si seleccionaste ":option" arriba, ¿cuál es la IP bloqueada?',
        'appeal-info'        => 'Información de la apelación de bloqueo',
        'admin-only-notice'  => 'Solo los administradores podrán ver tu apelación.',
        'word-notice'        => 'Hay un máximo de 4000 palabras en este cuadro de texto. Si lo superas, no podrás presentar una apelación.',
        'question-why'       => '¿Por qué deberías ser desbloqueado?',
        'verify-secret'      => 'Clave de apelación',
        'verify-secret-help' => 'Deberías haberla recibido cuando creaste tu apelación.'
    ],
    'key'                     => [
        'header'              => 'Apelación presentada',
        'do-not-lose'         => 'No pierdas esta clave de apelación. Solo puedes recuperarla si tienes una cuenta con una dirección de correo electrónico habilitada.',
        'your-key-is'         => 'Tu clave de apelación es:',
        'view-appeal-details' => 'Ver detalles de la apelación'
    ],
    'wrong-key'               => [
        'title' => 'Tu clave de apelación parece ser incorrecta.',
        'text'  => 'No se pudieron ubicar apelaciones usando esa clave de apelación. Por favor revisa de nuevo.'
    ]
];