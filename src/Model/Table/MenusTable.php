<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Menus Model
 *
 * @property \App\Model\Table\MenusTable&\Cake\ORM\Association\BelongsTo $ParentMenus
 * @property \App\Model\Table\MenusTable&\Cake\ORM\Association\HasMany $ChildMenus
 *
 * @method \App\Model\Entity\Menu newEmptyEntity()
 * @method \App\Model\Entity\Menu newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Menu> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Menu get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Menu findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Menu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Menu> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Menu|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Menu saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Menu>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Menu> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Menu>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Menu> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class MenusTable extends Table
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

        $this->setTable('menus');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Tree');

        $this->belongsTo('ParentMenus', [
            'className' => 'Menus',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildMenus', [
            'className' => 'Menus',
            'foreignKey' => 'parent_id',
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
            ->integer('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->integer('level')
            ->allowEmptyString('level');

        $validator
            ->scalar('icon')
            ->maxLength('icon', 255)
            ->allowEmptyString('icon');

        $validator
            ->scalar('controller')
            ->maxLength('controller', 255)
            ->allowEmptyString('controller');

        $validator
            ->scalar('action')
            ->maxLength('action', 255)
            ->allowEmptyString('action');

        $validator
            ->scalar('target')
            ->maxLength('target', 255)
            ->allowEmptyString('target');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('url')
            ->maxLength('url', 255)
            ->allowEmptyString('url');

        $validator
            ->scalar('prefix')
            ->maxLength('prefix', 255)
            ->allowEmptyString('prefix');

        $validator
            ->boolean('auth')
            ->allowEmptyString('auth');

        $validator
            ->boolean('admin')
            ->allowEmptyString('admin');

        $validator
            ->boolean('active')
            ->allowEmptyString('active');

        $validator
            ->boolean('disabled')
            ->allowEmptyString('disabled');

        $validator
            ->boolean('divider_before')
            ->allowEmptyString('divider_before');

        $validator
            ->boolean('parent_separator')
            ->allowEmptyString('parent_separator');

        $validator
            ->boolean('division')
            ->allowEmptyString('division');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentMenus'), ['errorField' => 'parent_id']);

        return $rules;
    }
}
