<?php

declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'user_group_id' => 1,
                'fullname' => 'Administrator',
                'password' => '$2y$10$OrUKHzNQUic6dFqSuG9QBeDzMbbwPz1BQXKgBKPcQyMTNdv3Z22xe',
                'email' => 'admin@localhost.com',
                'avatar' => '',
                'avatar_dir' => '',
                'token' => NULL,
                'token_created_at' => '2024-07-10 20:30:04',
                'status' => '1',
                'is_email_verified' => 1,
                'last_login' => '2024-07-10 20:30:04',
                'ip_address' => '::1',
                'slug' => 'Administrator',
                'created' => '2022-10-26 02:54:19',
                'modified' => '2024-07-08 21:08:15',
                'created_by' => NULL,
                'modified_by' => NULL,
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
