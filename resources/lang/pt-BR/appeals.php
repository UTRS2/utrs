<?php
return [
    'closed-notice'           => 'Esta apelação está encerrada. Nenhuma outra alteração pode ser feita nela.',
    'not-found-text'          => 'Não foi possível localizar seu bloqueio. Por favor :link para corrigir as informações em sua apelação.',
    'not-found-link-text'     => 'clique aqui',
    'not-found-button'        => 'Corrigir informações do bloqueio',
    'appeal-title'            => 'Apelação de ":name"',
    'details-status'          => 'Status da apelação',
    'details-block-admin'     => 'Bloqueio efetuado por',
    'details-block-reason'    => 'Motivo do bloqueio',
    'details-submitted'       => 'Hora de envio',
    'details-handling-admin'  => 'Administrador responsável',
    'content-question-why'    => 'Por que você deve ser desbloqueado?',
    'comment-color-text'      => 'As linhas em azul indicam uma resposta de ou para o usuário. As linhas em verde são comentários privados dos administradores.',
    'comment-input-text'      => 'Adicionar um comentário a esta apelação:',
    'section-headers'         => [
        'details'     => 'Detalhes da apelação',
        'content'     => 'Conteúdo da apelação',
        'comments'    => 'Comentários dos administradores',
        'add-comment' => 'Adicionar um comentário'
    ],
    'status-texts'            => [
        'ACCEPT'  => 'Esta apelação foi aprovada.',
        'EXPIRE'  => 'Esta apelação expirou.',
        'DECLINE' => 'Esta apelação foi negada.',
        'INVALID' => 'Esta apelação foi marcada como inválida.',
        'default' => 'Esta apelação está em andamento.'
    ],
    'forms'                   => [
        'header-account'     => 'Apelar um bloqueio em uma conta',
        'header-ip'          => 'Apelar um bloqueio em um endereço IP',
        'header-verify'      => 'Verificar propriedade da conta',
        'header-modify'      => 'Modificar apelação',
        'about-you'          => 'Sobre você',
        'block-wiki'         => 'Em qual wiki você está bloqueado?',
        'block-username'     => 'Qual é o seu nome de usuário?',
        'block-ip'           => 'Qual é o endereço IP bloqueado?',
        'direct-question'    => 'Sua conta está bloqueada diretamente?',
        'direct-yes'         => 'Sim',
        'direct-no'          => 'Não, o meu endereço IP está bloqueado',
        'direct-ip'          => 'Não, eu não tenho uma conta',
        'edit-notice'        => 'Agora você está modificando sua apelação para ser reenviada. Por favor, verifique se as informações estão corretas.',
        'hiddenip-question'  => 'Se você selecionou ":option" acima, qual é o IP bloqueado?',
        'appeal-info'        => 'Informações da apelação do bloqueio',
        'admin-only-notice'  => 'Somente os administradores poderão ver sua apelação.',
        'word-notice'        => 'Há um máximo de 4.000 palavras nesta caixa de texto. Se você passar disso, será impedido de criar uma apelação.',
        'question-why'       => 'Por que você deve ser desbloqueado?',
        'verify-secret'      => 'Chave de apelação',
        'verify-secret-help' => 'Você deve ter recebido isso quando criou sua apelação.'
    ],
    'key'                     => [
        'header'              => 'Apelação enviada',
        'do-not-lose'         => 'Não perca esta Chave de Apelação. Você só pode recuperá-la se possuir uma conta com um endereço de e-mail ativado.',
        'your-key-is'         => 'Sua chave de apelação é:',
        'view-appeal-details' => 'Ver detalhes da apelação'
    ],
    'wrong-key'               => [
        'title' => 'Sua chave de apelação parece estar errada.',
        'text'  => 'Nenhuma apelação pôde ser localizada usando essa chave de apelação. Por favor verifique novamente.'
    ],
    'publicheader'            => [
        'afterfile' => 'Depois de apresentar esta apelação, você receberá uma chave de apelação. Você terá que voltar aqui para atualizações. Um administrador analisará sua solicitação no devido tempo. Dependendo do idioma e do site de onde você está apelando, os tempos de apelação podem variar excessivamente. Observe que, para qualquer texto que você inserir para sua apelação, você concorda em liberar sob uma licença de domínio público para que possa ser copiado para a Wikipédia, se necessário. Se não concordar, não crie a apelação. Se você tiver alguma dúvida, você pode entrar em contato conosco. Observação: não agilizaremos, aprovaremos, negaremos ou editaremos sua apelação. É apenas para informações.',
        'appealkey' => 'Na próxima página, você receberá uma Chave de Apelação. Mantenha isso em um local seguro. Se você a esquecer, poderá recuperá-la, mas somente se sua conta Wikimedia possuir um endereço de e-mail válido. NÃO COMPARTILHE esta chave com ninguém.'
    ],
    'nav'                     => [
        'back-appeal-list' => 'Voltar para a lista de apelações'
    ],
    'appeal-number'           => 'Número da apelação',
    'verify'                  => [
        'verified'     => 'Esta apelação foi verificada com a conta na wiki.',
        'not-verified' => 'Esta apelação não foi ou não será verificada com a conta na wiki.'
    ],
    'links'                   => [
        'user-talk'      => 'Discussão do usuário',
        'contribs'       => 'Contribuições',
        'find-block'     => 'Encontrar bloqueio',
        'block-log'      => 'Registro de bloqueios',
        'ca'             => 'CentralAuth',
        'unblock'        => 'Desbloquear',
        'reopen'         => 'Reabrir',
        'reserve'        => 'Reservar',
        'release'        => 'Liberar',
        'force'          => 'Forçar',
        'invalidate'     => 'Supressão',
        'accept'         => 'Aceitar apelação',
        'decline'        => 'Recusar apelação',
        'checkuser'      => 'Verificadores',
        'tooladmin'      => 'Administradores da ferramenta',
        'expire'         => 'Marcar apelação como expirada',
        'return'         => 'Devolver aos usuários da ferramenta',
        'reverify'       => 'Reverificar apelação',
        'advance-search' => 'Busca avançada'
    ],
    'cu'                      => [
        'data-expire'  => 'Os dados de verificação para esta apelação expiraram.',
        'no-request'   => 'Você ainda não enviou uma solicitação para visualizar os dados de verificação.',
        'reason'       => 'Razão',
        'submit'       => 'Enviar',
        'title'        => 'Dados de verificação',
        'review-req'   => 'O que você gostaria que o verificador analisasse nesta apelação?',
        'submit-title' => 'Enviar para revisão dos verificadores',
        'user-ip'      => 'IP inserido pelo usuário',
        'under-ip'     => 'Esta apelação tem um IP subjacente que pode ser bloqueado. Você pode precisar consultar um CheckUser.'
    ],
    'no-action'               => 'Você não tem permissão para realizar nenhuma ação nesta apelação.',
    'appeal-for'              => 'Apelação de',
    'appeal-none'             => 'Nenhum',
    'header-previous-appeals' => 'Apelações anteriores',
    'comments'                => [
        'system'     => 'Sistema',
        'restricted' => 'O acesso a este comentário é restrito.',
        'action'     => 'Ação',
        'reason'     => 'Razão',
        'leave'      => 'Deixar um comentário',
        'add'        => 'Adicionar um comentário a esta apelação'
    ],
    'send-reply-header'       => 'Enviar uma resposta predefinida',
    'send-reply-button'       => 'Enviar uma resposta ao usuário',
    'not-handling-admin'      => 'Você não é o administrador responsável.',
    'spam'                    => 'Foi detectado que você ou outra pessoa está tentando sobrecarregar nosso sistema com apelações. Aguarde até que sua apelação anterior seja encerrada ou, se já estiver encerrada, tente novamente mais tarde. Se você estiver solicitando o desbloqueio de um endereço IP, isso pode significar que uma apelação já foi enviada para o seu IP. Nesse caso, tente novamente mais tarde ou entre em contato conosco para ajudar a esclarecer o problema.',
    'appeal-types'            => [
        'ip'          => 'endereço IP',
        'account'     => 'Conta',
        'ip-under'    => 'Endereço IP sob uma conta',
        'unknown'     => 'Tipo desconhecido',
        'assigned-me' => 'Designado a mim',
        'unassigned'  => 'Todas as apelações abertas não reservadas',
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