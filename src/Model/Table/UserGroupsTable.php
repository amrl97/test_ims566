<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserGroups Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\UserGroup newEmptyEntity()
 * @method \App\Model\Entity\UserGroup newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserGroup> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserGroup get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserGroup findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserGroup> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserGroup|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserGroup>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserGroup> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserGroup>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserGroup> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserGroupsTable extends Table
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

        $this->setTable('user_groups');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'user_group_id',
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
            ->scalar('name')
            ->maxLength('name', 100)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        return $validator;
    }
}
