<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Cake Software Foundation
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Error Handling Controller
 *
 * Controller usado pelo ExceptionRenderer para exibir páginas de erro.
 */
class ErrorController extends AppController
{
    /**
     * Inicialização do controller
     */
    public function initialize(): void
    {
        // ⚠️ Só use parent::initialize() se o AppController for seguro para erros
        // parent::initialize();
    }

    /**
     * Callback executada antes de qualquer ação
     */
    public function beforeFilter(EventInterface $event): void
    {
        // Intencionalmente vazio — não precisa configurar filtros aqui
    }

    /**
     * Callback executada antes de renderizar o template
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTemplatePath('Error');
    }

    /**
     * Callback executada após a resposta
     */
    public function afterFilter(EventInterface $event): void
    {
        // Nada por aqui — mas você pode logar ou limpar sessão se quiser
    }
}
