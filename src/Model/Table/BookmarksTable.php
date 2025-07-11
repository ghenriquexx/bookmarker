<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;

/**
 * Bookmarks Table
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Bookmark newEmptyEntity()
 * @method \App\Model\Entity\Bookmark newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Bookmark> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Bookmark get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Bookmark findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Bookmark patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Bookmark> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Bookmark|false save(EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Bookmark saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Bookmark>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Bookmark>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Bookmark>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Bookmark> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Bookmark>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Bookmark>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Bookmark>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Bookmark> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BookmarksTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('bookmarks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsToMany('Tags', [
            'foreignKey' => 'bookmark_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'bookmarks_tags',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 50)
            ->allowEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('url')
            ->allowEmptyString('url');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        return $rules;
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options): void

    {
        if ($entity->has('tag_string')) {
            $entity->tags = $this->_buildTags($entity->tag_string);
        }
    }

    protected function _buildTags(string $tagString): array
    {
        $newTags = array_unique(array_map('trim', explode(',', $tagString)));
        $tagEntities = [];

        $existingTagsQuery = $this->Tags->find()
            ->where(['Tags.title IN' => $newTags]);

        $existingTags = $existingTagsQuery->all();

        foreach ($existingTags as $tag) {
            $title = $tag->title;
            $index = array_search($title, $newTags);
            if ($index !== false) {
                unset($newTags[$index]);
            }
            $tagEntities[] = $tag;
        }

        foreach ($newTags as $title) {
            if (!empty($title)) {
                $tagEntities[] = $this->Tags->newEntity(['title' => $title]);
            }
        }

        return $tagEntities;
    }

    public function findTagged(SelectQuery $query, array $options): SelectQuery
    {
        $query = $query->distinct(['Bookmarks.id'])->matching('Tags');

        if (!empty($options['user_id'])) {
            $query->where(['Bookmarks.user_id' => $options['user_id']]);
        }

        if (!empty($options['tags'])) {
            $query->where(['Tags.title IN' => $options['tags']]);
        }

        return $query;
    }
}
