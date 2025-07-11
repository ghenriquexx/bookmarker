<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Bookmarks Controller
 *
 * @property \App\Model\Table\BookmarksTable $Bookmarks
 */
class BookmarksController extends AppController
{
    /**
     * Autorização customizada
     */
    public function isAuthorized($user): bool
    {
        $action = $this->request->getParam('action');

        if (in_array($action, ['index', 'add', 'tags'])) {
            return true;
        }

        $id = $this->request->getParam('pass.0');
        if (!$id) {
            return false;
        }

        $bookmark = $this->Bookmarks->get($id);
        return $bookmark->user_id === $user['id'];
    }

    /**
     * Listar bookmarks do usuário logado
     */
    public function index(): void
    {
        $user = $this->request->getSession()->read('Auth');
        if (empty($user['id'])) {
            $this->Flash->error('Você precisa estar logado para acessar seus bookmarks.');
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
            return;
        }

        $query = $this->Bookmarks
            ->find()
            ->where(['Bookmarks.user_id' => $user['id']]);

        $bookmarks = $this->paginate($query);
        $this->set(compact('bookmarks'));
    }

    /**
     * Ver bookmark
     */
    public function view(?string $id = null): void
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Users', 'Tags']
        ]);
        $this->set(compact('bookmark'));
    }

    /**
     * Adicionar bookmark
     */
    public function add(): void
    {
        $user = $this->request->getSession()->read('Auth');
        if (empty($user['id'])) {
            $this->Flash->error('Você precisa estar logado para adicionar bookmarks.');
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
            return;
        }

        $bookmark = $this->Bookmarks->newEmptyEntity();
        if ($this->request->is('post')) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->getData());
            $bookmark->user_id = $user['id'];
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success('O bookmark foi salvo com sucesso.');
                $this->redirect(['action' => 'index']);
                return;
            }
            $this->Flash->error('O bookmark não pôde ser salvo. Tente novamente.');
        }

        $tags = $this->Bookmarks->Tags->find('list')->all();
        $this->set(compact('bookmark', 'tags'));
    }

    /**
     * Editar bookmark
     */
    public function edit(?string $id = null): void
    {
        $user = $this->request->getSession()->read('Auth');
        if (empty($user['id'])) {
            $this->Flash->error('Você precisa estar logado para editar bookmarks.');
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
            return;
        }

        $bookmark = $this->Bookmarks->get($id, ['contain' => ['Tags']]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->getData());
            $bookmark->user_id = $user['id'];
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success('O bookmark foi atualizado.');
                $this->redirect(['action' => 'index']);
                return;
            }
            $this->Flash->error('Não foi possível atualizar o bookmark.');
        }

        $tags = $this->Bookmarks->Tags->find('list')->all();
        $this->set(compact('bookmark', 'tags'));
    }

    /**
     * Excluir bookmark
     */
    public function delete(?string $id = null): void
    {
        $user = $this->request->getSession()->read('Auth');
        if (empty($user['id'])) {
            $this->Flash->error('Você precisa estar logado para excluir bookmarks.');
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
            return;
        }

        $this->request->allowMethod(['post', 'delete']);
        $bookmark = $this->Bookmarks->get($id);
        if ($this->Bookmarks->delete($bookmark)) {
            $this->Flash->success('O bookmark foi excluído.');
        } else {
            $this->Flash->error('Não foi possível excluir o bookmark.');
        }

        $this->redirect(['action' => 'index']);
    }

    /**
     * Filtrar bookmarks por tags
     */
    public function tags(): void
    {
        $user = $this->request->getSession()->read('Auth');
        if (empty($user['id'])) {
            $this->Flash->error('Você precisa estar logado para filtrar por tags.');
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
            return;
        }

        $tags = $this->request->getParam('pass');
        $bookmarks = $this->Bookmarks->find('tagged', [
            'tags' => $tags,
            'user_id' => $user['id']
        ]);
        $this->set(compact('bookmarks', 'tags'));
    }
}
