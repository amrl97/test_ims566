<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserLogs Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserLog newEmptyEntity()
 * @method \App\Model\Entity\UserLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserLog> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserLogsTable extends Table
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

        $this->setTable('user_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
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
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('action')
            ->maxLength('action', 255)
            ->allowEmptyString('action');

        $validator
            ->scalar('useragent')
            ->maxLength('useragent', 256)
            ->allowEmptyString('useragent');

        $validator
            ->scalar('os')
            ->maxLength('os', 255)
            ->allowEmptyString('os');

        $validator
            ->scalar('ip')
            ->maxLength('ip', 50)
            ->allowEmptyString('ip');

        $validator
            ->scalar('host')
            ->maxLength('host', 255)
            ->allowEmptyString('host');

        $validator
            ->scalar('referrer')
            ->maxLength('referrer', 255)
            ->allowEmptyString('referrer');

        $validator
            ->integer('status')
            ->allowEmptyString('status');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
