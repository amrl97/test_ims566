<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property int|null $user_group_id
 * @property string $fullname
 * @property string $password
 * @property string $email
 * @property string|null $avatar
 * @property string|null $avatar_dir
 * @property string|null $token
 * @property \Cake\I18n\DateTime $token_created_at
 * @property string $status
 * @property int $is_email_verified
 * @property \Cake\I18n\DateTime|null $last_login
 * @property string|null $ip_address
 * @property string $slug
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property int|null $created_by
 * @property int|null $modified_by
 *
 * @property \App\Model\Entity\UserGroup $user_group
 * @property \App\Model\Entity\Contact[] $contacts
 * @property \App\Model\Entity\Todo[] $todos
 * @property \App\Model\Entity\UserLog[] $user_logs
 */
class User extends Entity
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
        'user_group_id' => true,
        'fullname' => true,
        'password' => true,
        'email' => true,
        'avatar' => true,
        'avatar_dir' => true,
        'token' => true,
        'token_created_at' => true,
        'status' => true,
        'is_email_verified' => true,
        'last_login' => true,
        'ip_address' => true,
        'slug' => true,
        'created' => true,
        'modified' => true,
        'created_by' => true,
        'modified_by' => true,
        'user_group' => true,
        'contacts' => true,
        'todos' => true,
        'user_logs' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
        'token',
    ];
}
