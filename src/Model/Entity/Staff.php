<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Staff Entity
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $staff_no
 * @property string $profile
 * @property string $profile_dir
 * @property int $department_id
 * @property int $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Application[] $applications
 */
class Staff extends Entity
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
        'name' => true,
        'email' => true,
        'staff_no' => true,
        'profile' => true,
        'profile_dir' => true,
        'department_id' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'department' => true,
        'applications' => true,
    ];
}
