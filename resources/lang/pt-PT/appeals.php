<?php
return [
    'closed-notice'           => 'Este recurso está encerrado. Nenhuma outra alteração pode ser feita nele.',
    'not-found-text'          => 'Não foi possível localizar seu bloco. Por favor :link para corrigir as informações em sua apelação.',
    'not-found-link-text'     => 'Clique aqui',
    'not-found-button'        => 'Corrigir informações do bloco',
    'appeal-title'            => 'Recurso para ":name"',
    'details-status'          => 'Status da apelação',
    'details-block-admin'     => 'Administrador de bloqueio',
    'details-block-reason'    => 'Motivo do bloqueio',
    'details-submitted'       => 'Hora de envio',
    'details-handling-admin'  => 'Manipulando o administrador',
    'content-question-why'    => 'Por que você deve ser desbloqueado?',
    'comment-color-text'      => 'As linhas em azul indicam uma resposta de ou para o usuário. As linhas em verde são comentários privados dos administradores.',
    'comment-input-text'      => 'Adicione um comentário a esta apelação:',
    'section-headers'         => [
        'details'     => 'Detalhes da apelação',
        'content'     => 'Conteúdo da apelação',
        'comments'    => 'Comentários do administrador',
        'add-comment' => 'Adicione um comentário'
    ],
    'status-texts'            => [
        'ACCEPT'  => 'Este recurso foi aprovado.',
        'EXPIRE'  => 'Este recurso expirou.',
        'DECLINE' => 'Este recurso foi negado.',
        'INVALID' => 'Esta apelação foi marcada como inválida.',
        'default' => 'Este recurso está em andamento.'
    ],
    'forms'                   => [
        'header-account'     => 'Recorrer um bloqueio em uma conta',
        'header-ip'          => 'Contestar um bloqueio em um endereço IP',
        'header-verify'      => 'Verifique a propriedade da conta',
        'header-modify'      => 'Modificar contestação',
        'about-you'          => 'Sobre você',
        'block-wiki'         => 'Em qual wiki você está bloqueado?',
        'block-username'     => 'Qual é o seu nome de usuário?',
        'block-ip'           => 'Qual é o endereço IP bloqueado?',
        'direct-question'    => 'Sua conta está bloqueada diretamente?',
        'direct-yes'         => 'Sim',
        'direct-no'          => 'Não, o endereço IP subjacente está bloqueado',
        'direct-ip'          => 'Não, eu não tenho uma conta',
        'edit-notice'        => 'Agora você está modificando sua apelação para ser reenviada. Por favor, verifique se as informações estão corretas.',
        'hiddenip-question'  => 'Se você selecionou ":option" acima, qual é o IP bloqueado?',
        'appeal-info'        => 'Bloquear informações de apelação',
        'admin-only-notice'  => 'Somente os administradores poderão ver sua contestação.',
        'word-notice'        => 'Há um máximo de 4.000 palavras nesta caixa de texto. Se você passar por cima, você será impedido de apresentar um recurso.',
        'question-why'       => 'Por que você deve ser desbloqueado?',
        'verify-secret'      => 'Chave de apelação',
        'verify-secret-help' => 'Você deveria ter recebido isso quando criou sua apelação.'
    ],
    'key'                     => [
        'header'              => 'Recurso enviado',
        'do-not-lose'         => 'Não perca esta Chave de Apelo. Você só pode recuperá-lo se tiver uma conta com um endereço de e-mail ativado.',
        'your-key-is'         => 'Sua chave de apelação é:',
        'view-appeal-details' => 'Ver detalhes da apelação'
    ],
    'wrong-key'               => [
        'title' => 'Sua chave de recurso parece estar errada.',
        'text'  => 'Nenhuma apelação pôde ser localizada usando essa chave de apelação. Por favor cheque novamente.'
    ],
    'publicheader'            => [
        'afterfile' => 'Depois de apresentar este recurso, você receberá uma chave de recurso. Você terá que voltar aqui para atualizações. Um administrador analisará sua solicitação no devido tempo. Dependendo do idioma e do site de onde você está apelando, os tempos de apelação podem variar excessivamente. Observe que qualquer texto que você inserir para sua apelação você concorda em liberar sob uma licença de domínio público para que possa ser copiado para a Wikipedia, se necessário. Se não concordar, não interponha recurso. Se você tiver alguma dúvida, você pode entrar em contato conosco. Observação: não agilizaremos, aprovaremos, negaremos ou editaremos sua apelação. É apenas para informação.',
        'appealkey' => 'Na próxima página, você receberá uma Chave de Apelação. Mantenha isso em um local seguro. Se você esquecer, poderá recuperá-lo, mas somente se sua conta Wikimedia tiver um endereço de e-mail válido. NÃO COMPARTILHE esta chave com ninguém.'
    ],
    'nav'                     => [
        'back-appeal-list' => 'Voltar para a lista de apelação'
    ],
    'appeal-number'           => 'Número da apelação',
    'verify'                  => [
        'verified'     => 'Este apelo foi verificado para a conta no wiki.',
        'not-verified' => 'Este recurso não foi ou não será verificado para a conta no wiki.'
    ],
    'links'                   => [
        'user-talk'      => 'Conversa do usuário',
        'contribs'       => 'Contribuições',
        'find-block'     => 'Encontrar bloco',
        'block-log'      => 'Bloquear registro',
        'ca'             => 'CentralAuth',
        'unblock'        => 'Desbloquear',
        'reopen'         => 'Reabrir',
        'reserve'        => 'reserva',
        'release'        => 'Liberar',
        'force'          => 'Força',
        'invalidate'     => 'Supervisão',
        'accept'         => 'Aceitar apelação',
        'decline'        => 'Recusar recurso',
        'checkuser'      => 'Verificar usuário',
        'tooladmin'      => 'Administrador de ferramentas',
        'expire'         => 'Marcar contestação como expirada',
        'return'         => 'Retornar aos usuários da ferramenta',
        'reverify'       => 'Reverificar apelação',
        'advance-search' => 'Busca Avançada'
    ],
    'cu'                      => [
        'data-expire'  => 'Os dados de CU para esta apelação expiraram.',
        'no-request'   => 'Você ainda não enviou uma solicitação para visualizar os dados do CheckUser.',
        'reason'       => 'Razão',
        'submit'       => 'Enviar',
        'title'        => 'Verificar dados do usuário',
        'review-req'   => 'O que você gostaria que o usuário de verificação analisasse nesta contestação?',
        'submit-title' => 'Enviar para revisão CheckUser',
        'user-ip'      => 'IP inserido pelo usuário',
        'under-ip'     => 'Esta apelação tem um IP subjacente que pode ser bloqueado. Você pode precisar consultar um CheckUser.'
    ],
    'no-action'               => 'Você não tem permissão para realizar nenhuma ação nesta apelação.',
    'appeal-for'              => 'Apelar para',
    'appeal-none'             => 'Nenhum',
    'header-previous-appeals' => 'Recursos anteriores',
    'comments'                => [
        'system'     => 'Sistema',
        'restricted' => 'O acesso ao comentário é restrito.',
        'action'     => 'Açao',
        'reason'     => 'Razão',
        'leave'      => 'Deixe um comentário',
        'add'        => 'Adicionar um comentário a esta apelação'
    ],
    'send-reply-header'       => 'Envie uma resposta modelo',
    'send-reply-button'       => 'Envie uma resposta ao usuário',
    'not-handling-admin'      => 'Você não é o administrador de manipulação.',
    'spam'                    => 'Foi detectado que você ou outra pessoa está tentando enviar spam ao nosso sistema com recursos. Aguarde até que sua apelação anterior seja encerrada ou, se já estiver encerrada, tente novamente mais tarde. Se você estiver solicitando o desbloqueio de um endereço IP, isso pode significar que uma apelação já foi enviada para o seu IP. Nesse caso, tente novamente mais tarde ou entre em contato conosco para ajudar a esclarecer o problema.',
    'appeal-types'            => [
        'ip'          => 'endereço de IP',
        'account'     => 'Conta',
        'ip-under'    => 'Endereço IP sob uma conta',
        'unknown'     => 'Tipo desconhecido',
        'assigned-me' => 'Atribuído a mim',
        'unassigned'  => 'Todos os recursos abertos não reservados',
        'reserved'    => 'Abrir apelações reservadas',
        'developer'   => 'Apelações de acesso do desenvolvedor',
        'title'       => 'Tipo de Apelação'
    ],
    'status'                  => [
        'OPEN'           => 'Aberto',
        'AWAITING_REPLY' => 'Aguardando resposta',
        'ACCEPT'         => 'Aceitaram',
        'DECLINE'        => 'Recusado',
        'EXPIRE'         => 'Expirado',
        'INVALID'        => 'Supervisionado',
        'NOTFOUND'       => 'Bloco não encontrado',
        'VERIFY'         => 'Precisando de verificação',
        'CHECKUSER'      => 'Precisando de um usuário de verificação',
        'ADMIN'          => 'Precisando de um administrador'
    ]
];