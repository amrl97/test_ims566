<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserLog Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $action
 * @property string|null $useragent
 * @property string|null $os
 * @property string|null $ip
 * @property string|null $host
 * @property string|null $referrer
 * @property int|null $status
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class UserLog extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'action' => true,
        'useragent' => true,
        'os' => true,
        'ip' => true,
        'host' => true,
        'referrer' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
