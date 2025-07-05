<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Application Entity
 *
 * @property int $id
 * @property int $staff_id
 * @property int $department_id
 * @property int $type_id
 * @property \Cake\I18n\Date $start_date
 * @property \Cake\I18n\Date $end_date
 * @property string $reason
 * @property string $image
 * @property string $image_dir
 * @property string $approval_status
 * @property int $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Staff $staff
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Type $type
 */
class Application extends Entity
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
        'staff_id' => true,
        'department_id' => true,
        'type_id' => true,
        'start_date' => true,
        'end_date' => true,
        'reason' => true,
        'image' => true,
        'image_dir' => true,
        'approval_status' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'staff' => true,
        'department' => true,
        'type' => true,
    ];
}
