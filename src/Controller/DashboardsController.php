<?php

declare(strict_types=1);

namespace App\Controller;

use Authentication\IdentityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class DashboardsController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();
	}

	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
		//$this->Authentication->allowUnauthenticated(['view']);
	}
	public function index()
	{
		$this->set('title', 'Dashboard');

		//count approved applications
		$applications = $this->fetchTable('Applications');
		$total_app = $applications->find()->all()->count();
		$approved_app = $applications->find()->where(['approval_status' => 2])->count();
		$approved_app_percent = $approved_app * 100 / $total_app;
		$rejected_app = $applications->find()->where(['approval_status' => 1])->count();
		$rejected_app_percent = $rejected_app * 100 / $total_app;
		$pending_app = $applications->find()->where(['approval_status' => 0])->count();
		$pending_app_percent = $pending_app * 100 / $total_app;

		//count staffs
		$staffs = $this->fetchTable('Staffs');
		$total_staff = $staffs->find()->all()->count();

		//count departments
		$departments = $this->fetchTable('Departments');
		$total_dept = $departments->find()->all()->count();


		//count auditlog
		$auditLogs = $this->fetchTable('AuditLogs');
		$total_auditlog = $auditLogs->find()->all()->count();

		//count to do task
		$todos = $this->fetchTable('Todos');
		$total_todo = $todos->find()->all()->count();
		$pending_todo = $todos->find()->where(['status' => 'Pending'])->count();
		$pending_todo_percent = $pending_todo * 100 / $total_todo;



		//get current authenticate user
		$userdetail = $this->request->getAttribute('identity');
		$userID = $userdetail->id;

		$userLogs = $this->fetchTable('UserLogs');
		//publish activity user (for module)
		$userLogs = $userLogs->find('all')
			->where(['user_id' => $userID])
			->limit(5)
			->orderBy(['created' => 'DESC']);

		//count all user activities and group by date for heatmap
		$userLogsTable = TableRegistry::getTableLocator()->get('UserLogs');
		$query = $userLogsTable->find();
		$query->select([
			'count' => $query->func()->count('*'),
			'date' => $query->func()->date_format(['created' => 'identifier', "%Y-%m-%d"])
		])
			->groupBy(['date']);

		$results = $query->all()->toArray();

		$formattedResults = [];
		foreach ($results as $result) {
			$formattedResults[] = [
				'date' => $result->date,
				'count' => $result->count
			];
		}

		$this->set([
			'results' => $formattedResults,
			'_serialize' => ['results']
		]);

		//count all user activities and group by month for bar chart
		$query = $userLogsTable->find();
		$query->select([
			'count' => $query->func()->count('*'),
			'date' => $query->func()->date_format(['created' => 'identifier', "%b-%Y"])
		])
			->groupBy(['month' => 'MONTH(created)']);

		//$results = $query->all()->toArray();

		$totalActivityByMonth = [];
		foreach ($results as $result) {
			$totalActivityByMonth[] = [
				'month' => $result->date,
				'count' => $result->count
			];
		}

		$this->set([
			'results' => $totalActivityByMonth,
			'_serialize' => ['results']
		]);

		$this->set(compact('total_app', 'total_staff', 'total_auditlog', 'total_todo', 'total_dept', 'rejected_app_percent', 'pending_app_percent', 'approved_app_percent', 'pending_todo_percent', 'userLogs', 'formattedResults', 'totalActivityByMonth'));
	}
}
