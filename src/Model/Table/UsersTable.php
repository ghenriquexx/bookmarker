<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\Entity;
use Cake\Event\EventInterface;
use ArrayObject;
use Authentication\PasswordHasher\DefaultPasswordHasher;

/**
 * Users Model
 *
 * @property \App\Model\Table\BookmarksTable&\Cake\ORM\Association\HasMany $Bookmarks
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Bookmarks', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email', 'O campo de e-mail é obrigatório.');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Informe uma senha.');

        return $validator;
    }

    /**
     * Regras de integridade (ex: email único)
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }

    /**
     * Criptografa a senha automaticamente antes de salvar
     *
     * @param \Cake\Event\EventInterface $event
     * @param \Cake\ORM\Entity $entity
     * @param \ArrayObject $options
     * @return void
     */
    public function beforeSave(EventInterface $event, Entity $entity, ArrayObject $options): void
    {
        if (!empty($entity->password)) {
            $entity->password = (new DefaultPasswordHasher())->hash($entity->password);
        }
    }
}
