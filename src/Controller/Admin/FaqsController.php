<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\JsonView;
use Cake\View\XmlView;

use AuditStash\Meta\ApplicationMetadata;
use Cake\Event\EventManager;


/**
 * Faqs Controller
 *
 * @property \App\Model\Table\FaqsTable $Faqs
 * @method \App\Model\Entity\Faq[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FaqsController extends AppController
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

	public function viewClasses(): array
	{
		return [JsonView::class];
		//return [JsonView::class, XmlView::class];
	}

	public function json()
	{
		$this->viewBuilder()->setLayout('json');
		$this->set('faqs', $this->paginate());
		$this->viewBuilder()->setOption('serialize', 'faqs');
	}

	public function csv()
	{
		$this->response = $this->response->withDownload('faqs.csv');
		$faqs = $this->Faqs->find();
		$_serialize = 'faqs';

		$this->viewBuilder()->setClassName('CsvView.Csv');
		$this->set(compact('faqs', '_serialize'));
	}

	public function pdfList()
	{
		$this->viewBuilder()->enableAutoLayout(false);
		$faqs = $this->paginate($this->Faqs);
		$this->viewBuilder()->setClassName('CakePdf.Pdf');
		$this->viewBuilder()->setOption(
			'pdfConfig',
			[
				'orientation' => 'portrait',
				'download' => true,
				'filename' => 'faqs_List.pdf'
			]
		);
		$this->set(compact('faqs'));
	}
	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void Renders view
	 */
	public function index()
	{
		$this->set('title', 'Frequently Asked Questions');
		$this->paginate = [
			'maxLimit' => 10,
		];
		//Search
		//$faqs = $this->paginate($this->Faqs->find('search', ['search' => $this->request->getQuery()]));
		$query = $this->Faqs->find('search', search: $this->request->getQueryParams());
		//->contain(['UserGroups'])
		//->where(['title IS NOT' => null])
		//;
		$faqs = $this->paginate($query);
		//count
		$this->set('total_faqs', $this->Faqs->find()->count());
		$this->set('total_faqs_archived', $this->Faqs->find()->where(['status' => 2])->count());
		$this->set('total_faqs_active', $this->Faqs->find()->where(['status' => 1])->count());
		$this->set('total_faqs_disabled', $this->Faqs->find()->where(['status' => 0])->count());

		$query = $this->Faqs->find();

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

		$totalFaqByMonth = [];
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

			$totalFaqByMonth[] = [
				'month' => $expectedMonth,
				'count' => $count
			];
		}

		$this->set([
			'results' => $totalFaqByMonth,
			'_serialize' => ['results']
		]);

		// JSON arrays
		$totalFaqByMonth = json_encode($totalFaqByMonth);
		$dataArray = json_decode($totalFaqByMonth, true);
		$monthArray = [];
		$countArray = [];
		foreach ($dataArray as $data) {
			$monthArray[] = $data['month'];
			$countArray[] = $data['count'];
		}

		//$this->set(compact('faqs'));
		//$this->set('faqs', $this->paginate($query));
		$this->set(compact('faqs', 'monthArray', 'countArray'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Faq id.
	 * @return \Cake\Http\Response|null|void Renders view
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null)
	{
		$this->set('title', 'Faqs Details');

		$auditLogs = $this->fetchTable('AuditLogs');
		$auditLogs->find()
			->where([
				'primary_key' => $id,
			])
			->orderBy(['created' => 'ASC'])
			->limit(10);


		$faq = $this->Faqs->get($id, [
			'contain' => [],
		]);

		$this->set(compact('faq', 'auditLogs'));
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add()
	{
		$this->set('title', 'New Faqs');

		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Add']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Faqs']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});

		$faq = $this->Faqs->newEmptyEntity();
		if ($this->request->is('post')) {
			$faq = $this->Faqs->patchEntity($faq, $this->request->getData());
			$faq->status = 1;
			if ($this->Faqs->save($faq)) {
				$this->Flash->success(__('The faq has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The faq could not be saved. Please, try again.'));
		}
		$this->set(compact('faq'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Faq id.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function edit($id = null)
	{
		$this->set('title', 'Faqs Edit');
		$faq = $this->Faqs->get($id, [
			'contain' => [],
		]);
		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Edit']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Faqs']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});
		if ($this->request->is(['patch', 'post', 'put'])) {
			$faq = $this->Faqs->patchEntity($faq, $this->request->getData());
			if ($this->Faqs->save($faq)) {
				$this->Flash->success(__('The faq has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The faq could not be saved. Please, try again.'));
		}
		$this->set(compact('faq'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Faq id.
	 * @return \Cake\Http\Response|null|void Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null)
	{
		EventManager::instance()->on('AuditStash.beforeLog', function ($event, array $logs) {
			foreach ($logs as $log) {
				$log->setMetaInfo($log->getMetaInfo() + ['a_name' => 'Delete']);
				$log->setMetaInfo($log->getMetaInfo() + ['c_name' => 'Faqs']);
				$log->setMetaInfo($log->getMetaInfo() + ['ip' => $this->request->clientIp()]);
				$log->setMetaInfo($log->getMetaInfo() + ['url' => Router::url(null, true)]);
				$log->setMetaInfo($log->getMetaInfo() + ['slug' => $this->Authentication->getIdentity('slug')->getIdentifier('slug')]);
			}
		});
		$this->request->allowMethod(['post', 'delete']);
		$faq = $this->Faqs->get($id);
		if ($this->Faqs->delete($faq)) {
			$this->Flash->success(__('The faq has been deleted.'));
		} else {
			$this->Flash->error(__('The faq could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}
