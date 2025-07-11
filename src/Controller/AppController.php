<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Base para todos os controllers da aplicação.
 */
class AppController extends Controller
{
    /**
     * Inicialização global
     */
    public function initialize(): void
    {
        parent::initialize();

        // Mensagens Flash (sucesso, erro, aviso etc)
        $this->loadComponent('Flash');

        // Você pode habilitar outros componentes se quiser:
        // $this->loadComponent('Security');
        // $this->loadComponent('Paginator');
    }

    /**
     * Autorização padrão
     *
     * Pode ser usado em controllers que exigem permissão por tipo de usuário
     *
     * @param array $user
     * @return bool
     */
    public function isAuthorized($user): bool
    {
        return false; // nega tudo por padrão
    }

    /**
     * Proteção global contra acesso sem login
     *
     * Redireciona se usuário não estiver logado
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $session = $this->request->getSession();
        $user = $session->read('Auth');

        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');

        // Libera ações públicas do UsersController
        $publicAccess = $controller === 'Users' && in_array($action, ['login', 'add', 'logout']);

        // Se não estiver logado e tentar acessar rota protegida
        if (!$user && !$publicAccess) {
            $this->Flash->error('Você precisa estar logado para acessar essa página.');
            // Redireciona manualmente usando response object
            $this->response = $this->redirect([
                'controller' => 'Users',
                'action' => 'login'
            ]);
        }
    }
}
