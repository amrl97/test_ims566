<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

use AuditStash\Meta\RequestMetadata;
use Cake\Event\EventManager;
use Cake\Routing\Router;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();

		$this->loadComponent('Search.Search', [
			'actions' => ['index'],
		]);
	}
	
	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
	}

	/*public function viewClasses(): array
    {
        return [JsonView::class];
		return [JsonView::class, XmlView::class];
    }*/

    public function logout()
    {
        $this->UserLogs->userLogoutActivity($this->Authentication->getIdentity('id')->getIdentifier('id'));
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

	public function profile($slug = null)
	{
		$this->set('title', 'Account Details');

		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups','Contacts', 'UserLogs'])
			->firstOrFail();
		//$user = $this->Users->get($slug, contain: ['UserGroups', 'Contacts', 'Todos', 'UserLogs']);
		/* $user = $this->Users->get($id, [
            ->contain(['UserGroups'])
        ]); */

		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Edit']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Users']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Users']);
				//$log->setMetaInfo($log->getMetaInfo() + ['slug' => $user]);

			}
		});
		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(('Account details updated'));
				return $this->redirect($this->referer());
			}
			$this->Flash->error(('The user could not be saved. Please, try again.'));
		}
		$userGroups = $this->Users->UserGroups->find('list', ['limit' => 200])->all();
		$this->set(compact('user', 'userGroups'));
	}

	public function update($slug = null, $id = null)
	{
		$this->set('title', 'Update Profile');

		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups', 'AuditLogs'])
			->firstOrFail();
		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(('Account details updated'));
				return $this->redirect($this->referer());
			}
			$this->Flash->error(('The user could not be saved. Please, try again.'));
		}
		$userGroups = $this->Users->UserGroups->find('list', ['limit' => 200])->all();
		$this->set(compact('user', 'userGroups'));
	}

	public function removeAvatar($slug = null)
	{
		$this->set('title', 'Remove Profile Picture');

		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups'])
			->firstOrFail();
		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(('Account details updated'));
				//return $this->redirect($this->referer());
				return $this->redirect(['action' => 'profile', $user->slug]);
			}
			$this->Flash->error(('The user could not be saved. Please, try again.'));
		}
		$userGroups = $this->Users->UserGroups->find('list', ['limit' => 200])->all();
		$this->set(compact('user', 'userGroups', 'auditLogs'));
	}

	public function changePassword($slug = null)
	{
		$this->set('title', 'Change Password');
		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups'])
			->firstOrFail();

		//$userSlug = $this->Auth->user('slug');

		/* if($slug != $userSlug){
				$this->Flash->error(('You are not authorized to view'));
				return $this->redirect(['action' => 'profile', $this->Auth->user('slug')]);
		} */

		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData(), ['validate' => 'password']);

			if ($this->Users->save($user)) {
				$this->Flash->success(('Your password has been updated.'));

				return $this->redirect(['action' => 'profile', $user->slug]);
			}
			$this->Flash->error(('Your password could not be update. Please, try again.'));
		}
		$userGroups = $this->Users->UserGroups->find('list', ['limit' => 200]);
		$this->set(compact('user', 'userGroups'));
	}

	public function activity($slug = null)
	{
		$this->set('title', 'User Activities');

		$user = $this->Users
			->findBySlug($slug)
			->contain([
				'UserGroups',
				'AuditLogs' => function ($q) {
					return $q->order(['AuditLogs.created' => 'DESC'])->limit(5); // Limit to 5 auditlog 
				}
			])
			->limit(5)
			->firstOrFail();

		$userLogs = $this->fetchTable('UserLogs')->find(
			'all',
			limit: 5,
			order: 'UserLogs.created DESC'
		)
			->all();
		/* $this->userLogs = $this->fetchTable('userLogs');
		$userLogs = $this->fetchTable('userLogs');
		$userLogs = $this->userLogs->find('all')
			->where(['user_id' => $user->id])
			->limit(10)
			->orderBy(['created' => 'DESC']); */


		$this->set(compact('user', 'userLogs'));

		//$this->set(compact('user', 'userGroups'));
	}
	
	public function json()
    {
		$this->viewBuilder()->setLayout('json');
        $this->set('users', $this->paginate());
        $this->viewBuilder()->setOption('serialize', 'users');
    }
	
public function registration()
	{
		$this->set('title', 'User Registration');
		$user = $this->Users->newEmptyEntity();
		if ($this->request->is('post')) {
			$user = $this->Users->patchEntity($user, $this->request->getData(), ['validate' => 'register']);
			if ($this->Users->save($user)) {
				$this->Flash->success(('The user has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(('The user could not be saved. Please, try again.'));
		}
		$userGroups = $this->Users->UserGroups->find('list', ['limit' => 200])->all();
		$this->set(compact('user', 'userGroups'));
	}

public function adminVerify($slug = null)
	{
		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups'])
			->firstOrFail();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			$user->is_email_verified = 1;
			if ($this->Users->save($user)) {
				$this->Flash->success(('Account has been verified'));
				return $this->redirect($this->referer());
			}
			$this->Flash->error(('Cannot verified. Please, try again.'));
		}
	}

	public function archived($slug = null)
	{
		$user = $this->Users
			->findBySlug($slug)
			->contain(['UserGroups'])
			->firstOrFail();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			$user->status = 2;
			if ($this->Users->save($user)) {
				$this->Flash->success(('Account has been verified'));
				return $this->redirect($this->referer());
			}
			$this->Flash->error(('Cannot verified. Please, try again.'));
		}
	}

	public function csv()
	{
		$this->response = $this->response->withDownload('users.csv');
		$users = $this->Users->find();
		$_serialize = 'users';

		$this->viewBuilder()->setClassName('CsvView.Csv');
		$this->set(compact('users', '_serialize'));
	}
	
	public function pdfList()
	{
		$this->viewBuilder()->enableAutoLayout(false); 
        $this->paginate = [
            'contain' => ['UserGroups'],
			'maxLimit' => 10,
        ];
		$users = $this->paginate($this->Users);
		$this->viewBuilder()->setClassName('CakePdf.Pdf');
		$this->viewBuilder()->setOption(
			'pdfConfig',
			[
				'orientation' => 'portrait',
				'download' => true, 
				'filename' => 'users_List.pdf' 
			]
		);
		$this->set(compact('users'));
	}
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
		$this->set('title', 'Users List');
		$this->paginate = [
			'maxLimit' => 10,
        ];
        $query = $this->Users->find('search', search: $this->request->getQueryParams())
            ->contain(['UserGroups']);
			//->where(['title IS NOT' => null])
        $users = $this->paginate($query);
		
		//count
		$this->set('total_users', $this->Users->find()->count());
		$this->set('total_users_archived', $this->Users->find()->where(['status' => 2])->count());
		$this->set('total_users_active', $this->Users->find()->where(['status' => 1])->count());
		$this->set('total_users_disabled', $this->Users->find()->where(['status' => 0])->count());
		
		//Count By Month
		$this->set('january', $this->Users->find()->where(['MONTH(created)' => date('1'), 'YEAR(created)' => date('Y')])->count());
		$this->set('february', $this->Users->find()->where(['MONTH(created)' => date('2'), 'YEAR(created)' => date('Y')])->count());
		$this->set('march', $this->Users->find()->where(['MONTH(created)' => date('3'), 'YEAR(created)' => date('Y')])->count());
		$this->set('april', $this->Users->find()->where(['MONTH(created)' => date('4'), 'YEAR(created)' => date('Y')])->count());
		$this->set('may', $this->Users->find()->where(['MONTH(created)' => date('5'), 'YEAR(created)' => date('Y')])->count());
		$this->set('jun', $this->Users->find()->where(['MONTH(created)' => date('6'), 'YEAR(created)' => date('Y')])->count());
		$this->set('july', $this->Users->find()->where(['MONTH(created)' => date('7'), 'YEAR(created)' => date('Y')])->count());
		$this->set('august', $this->Users->find()->where(['MONTH(created)' => date('8'), 'YEAR(created)' => date('Y')])->count());
		$this->set('september', $this->Users->find()->where(['MONTH(created)' => date('9'), 'YEAR(created)' => date('Y')])->count());
		$this->set('october', $this->Users->find()->where(['MONTH(created)' => date('10'), 'YEAR(created)' => date('Y')])->count());
		$this->set('november', $this->Users->find()->where(['MONTH(created)' => date('11'), 'YEAR(created)' => date('Y')])->count());
		$this->set('december', $this->Users->find()->where(['MONTH(created)' => date('12'), 'YEAR(created)' => date('Y')])->count());

		$query = $this->Users->find();

        $expectedMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $expectedMonths[] = date('M-Y', strtotime("-$i months"));
        }

        $query->select([
            'count' => $query->func()->count('*'),
            'date' => $query->func()->date_format(['created' => 'identifier', "%b-%Y"]),
            'month' => 'MONTH(created)',
            'year' => 'YEAR(created)'
        ])
            ->where([
                'created >=' => date('Y-m-01', strtotime('-11 months')),
                'created <=' => date('Y-m-t')
            ])
            ->groupBy(['year', 'month'])
            ->orderBy(['year' => 'ASC', 'month' => 'ASC']);

        $results = $query->all()->toArray();

        $totalByMonth = [];
        foreach ($expectedMonths as $expectedMonth) {
            $found = false;
            $count = 0;

            foreach ($results as $result) {
                if ($expectedMonth === $result->date) {
                    $found = true;
                    $count = $result->count;
                    break;
                }
            }

            $totalByMonth[] = [
                'month' => $expectedMonth,
                'count' => $count
            ];
        }

        $this->set([
            'results' => $totalByMonth,
            '_serialize' => ['results']
        ]);

        //data as JSON arrays for report chart
        $totalByMonth = json_encode($totalByMonth);
        $dataArray = json_decode($totalByMonth, true);
        $monthArray = [];
        $countArray = [];
        foreach ($dataArray as $data) {
            $monthArray[] = $data['month'];
            $countArray[] = $data['count'];
        }

        $this->set(compact('users', 'monthArray', 'countArray'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
		$this->set('title', 'Users Details');
        $user = $this->Users->get($id, contain: ['UserGroups', 'Contacts', 'Todos', 'UserLogs']);
        $this->set(compact('user'));

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$this->set('title', 'New Users');
		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Add']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Users']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $userGroups = $this->Users->UserGroups->find('list', ['limit' => 200])->all();
        $this->set(compact('user', 'userGroups'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->set('title', 'Users Edit');
		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Edit']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Users']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
		$userGroups = $this->Users->UserGroups->find('list', limit: 200)->all();
        $this->set(compact('user', 'userGroups'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Delete']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Users']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	


    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful login, renders view otherwise.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Flash->success(__('Login successful'));
            $redirect = $this->Authentication->getLoginRedirect();
            if ($redirect) {
                return $this->redirect($redirect);
            }
        }

        // Display error if user submitted and authentication failed
        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }
}
