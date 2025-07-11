<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $action = $this->request->getParam('action');
        $publicActions = ['add', 'login', 'logout'];

        if (in_array($action, $publicActions, true)) {
            return;
        }

        $user = $this->request->getSession()->read('Auth');
        if (empty($user)) {
            $this->Flash->error('Você precisa estar logado para acessar esta página.');
            $this->redirect(['action' => 'login']);
        }
    }

    public function login(): void
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $password = $this->request->getData('password');

            $user = $this->Users->find()
                ->where(['email' => $email])
                ->first();

            if ($user && (new \Authentication\PasswordHasher\DefaultPasswordHasher())->check($password, $user->password)) {
                $this->request->getSession()->write('Auth', $user);
                $this->Flash->success('Login realizado com sucesso.');
                $this->redirect(['controller' => 'Bookmarks', 'action' => 'index']);
                return;
            }

            $this->Flash->error('Email ou senha incorretos.');
        }
    }

    public function logout(): void
    {
        $this->request->getSession()->delete('Auth');
        $this->Flash->success('Você saiu da conta.');
        $this->redirect(['action' => 'login']);
    }

    public function index(): void
    {
        $users = $this->paginate($this->Users->find());
        $this->set(compact('users'));
    }

    public function view(?string $id = null): void
    {
        $user = $this->Users->get($id, ['contain' => ['Bookmarks']]);
        $this->set(compact('user'));
    }

    public function add(): void
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            // Debug para ver por que falha (útil durante desenvolvimento)
            if ($user->getErrors()) {
                debug($user->getErrors());
            }

            if ($this->Users->save($user)) {
                $this->Flash->success('Cadastro realizado com sucesso.');
                $this->redirect(['action' => 'login']);
                return;
            }

            $this->Flash->error('Não foi possível realizar o cadastro.');
        }

        $this->set(compact('user'));
    }

    public function edit(?string $id = null): void
    {
        $user = $this->Users->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            // Se quiser criptografar a senha manualmente, mas já é feito no UsersTable
            // Pode remover se já estiver no beforeSave do modelo
            // if (!empty($user->password)) {
            //     $user->password = (new DefaultPasswordHasher())->hash($user->password);
            // }

            if ($this->Users->save($user)) {
                $this->Flash->success('O usuário foi atualizado.');
                $this->redirect(['action' => 'index']);
                return;
            }

            $this->Flash->error('Não foi possível atualizar o usuário.');
        }

        $this->set(compact('user'));
    }

    public function delete(?string $id = null): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $this->Flash->success('O usuário foi excluído.');
        } else {
            $this->Flash->error('Não foi possível excluir o usuário.');
        }

        $this->redirect(['action' => 'index']);
    }
}
