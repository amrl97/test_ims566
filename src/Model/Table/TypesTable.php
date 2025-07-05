<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Types Model
 *
 * @property \App\Model\Table\ApplicationsTable&\Cake\ORM\Association\HasMany $Applications
 *
 * @method \App\Model\Entity\Type newEmptyEntity()
 * @method \App\Model\Entity\Type newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Type> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Type get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Type findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Type patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Type> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Type|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Type saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Type>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Type>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Type>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Type> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Type>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Type>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Type>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Type> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TypesTable extends Table
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

        $this->setTable('types');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Applications', [
            'foreignKey' => 'type_id',
        ]);
		$this->addBehavior('AuditStash.AuditLog');
		$this->addBehavior('Search.Search');
		$this->searchManager()
			->value('id')
				->add('search', 'Search.Like', [
					//'before' => true,
					//'after' => true,
					'fieldMode' => 'OR',
					'multiValue' => true,
					'multiValueSeparator' => '|',
					'comparison' => 'LIKE',
					'wildcardAny' => '*',
					'wildcardOne' => '?',
					'fields' => ['id'],
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
            ->maxLength('name', 20)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->integer('max_days')
            ->requirePresence('max_days', 'create')
            ->notEmptyString('max_days');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        return $validator;
    }
}
