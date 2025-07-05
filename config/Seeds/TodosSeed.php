<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Todos seed.
 */
class TodosSeed extends AbstractSeed
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
                'id' => 'a02daac9-27e3-49ea-8090-927e60f9e255',
                'user_id' => '1',
                'urgency' => 'High',
                'task' => 'task 4 desc',
                'description' => '<p>task 4 desc</p>',
                'note' => '<p>task 4 desc</p>',
                'status' => 'In Progress',
                'created' => '2024-05-31 13:48:26',
                'modified' => '2024-05-31 13:48:31',
            ],
            [
                'id' => 'a8618f9e-a3f7-4e7a-8a3f-05a5323cd5af',
                'user_id' => '1',
                'urgency' => 'High',
                'task' => 'task 1',
                'description' => '<p>task 1 desc</p>',
                'note' => '<p>task 1 desc</p>',
                'status' => 'Completed',
                'created' => '2024-05-31 13:45:22',
                'modified' => '2024-05-31 13:45:40',
            ],
            [
                'id' => 'c892f026-c6f8-4353-82c2-75f49c0f5d6b',
                'user_id' => '1',
                'urgency' => 'Medium',
                'task' => 'Task 3 - Develop the To Do Task and render in Dashboard',
                'description' => '<p>task 3 desc</p>',
                'note' => '<p>task 3 desc</p>',
                'status' => 'Pending',
                'created' => '2024-05-31 13:48:06',
                'modified' => '2024-05-31 13:48:06',
            ],
            [
                'id' => 'eda46e51-555a-4309-a28b-d0835bf4f4b2',
                'user_id' => '1',
                'urgency' => 'Low',
                'task' => 'task 2',
                'description' => '<p>task 2 desc</p>',
                'note' => '<p>task 2 desc</p>',
                'status' => 'Canceled',
                'created' => '2024-05-31 13:46:12',
                'modified' => '2024-05-31 13:46:24',
            ],
        ];

        $table = $this->table('todos');
        $table->insert($data)->save();
    }
}
