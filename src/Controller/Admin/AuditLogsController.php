<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\JsonView;
use Cake\View\XmlView;

/**
 * AuditLogs Controller
 *
 * @property \App\Model\Table\AuditLogsTable $AuditLogs
 * @method \App\Model\Entity\AuditLog[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AuditLogsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['search'],
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
        $this->set('auditLogs', $this->paginate());
        $this->viewBuilder()->setOption('serialize', 'auditLogs');
    }

    public function csv()
    {
        $this->response = $this->response->withDownload('auditLogs.csv');
        $auditLogs = $this->AuditLogs->find();
        $_serialize = 'auditLogs';

        $this->viewBuilder()->setClassName('CsvView.Csv');
        $this->set(compact('auditLogs', '_serialize'));
    }

    public function pdfList()
    {
        $this->viewBuilder()->enableAutoLayout(false);
        $auditLogs = $this->paginate($this->AuditLogs);
        $this->viewBuilder()->setClassName('CakePdf.Pdf');
        $this->viewBuilder()->setOption(
            'pdfConfig',
            [
                'orientation' => 'portrait',
                'download' => true,
                'filename' => 'auditLogs_List.pdf'
            ]
        );
        $this->set(compact('auditLogs'));
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->set('title', 'AuditLogs List');
        $this->paginate = [
            //'maxLimit' => 10,
        ];
        //Search
        // $auditLogs = $this->paginate($this->AuditLogs->find('search', ['search' => $this->request->getQuery()]));
        $query = $this->AuditLogs->find('search', search: $this->request->getQueryParams());
        $auditLogs = $this->paginate($query);

        //count
        $this->set('total_auditLogs', $this->AuditLogs->find()->count());
        $this->set('total_auditLogs_archived', $this->AuditLogs->find()->where(['status' => 2])->count());
        $this->set('total_auditLogs_active', $this->AuditLogs->find()->where(['status' => 1])->count());
        $this->set('total_auditLogs_disabled', $this->AuditLogs->find()->where(['status' => 0])->count());

        $query = $this->AuditLogs->find();

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

        $totalBookByMonth = [];
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

            $totalBookByMonth[] = [
                'month' => $expectedMonth,
                'count' => $count
            ];
        }

        $this->set([
            'results' => $totalBookByMonth,
            '_serialize' => ['results']
        ]);

        // JSON arrays
        $totalBookByMonth = json_encode($totalBookByMonth);
        $dataArray = json_decode($totalBookByMonth, true);
        $monthArray = [];
        $countArray = [];
        foreach ($dataArray as $data) {
            $monthArray[] = $data['month'];
            $countArray[] = $data['count'];
        }


        $this->set(compact('auditLogs', 'monthArray', 'countArray'));
        //$this->set('auditLogs', $this->paginate($query));
    }

    /**
     * View method
     *
     * @param string|null $id Audit Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->set('title', 'AuditLogs Details');
        $auditLog = $this->AuditLogs->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('auditLog'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->set('title', 'New AuditLogs');
        $auditLog = $this->AuditLogs->newEmptyEntity();
        if ($this->request->is('post')) {
            $auditLog = $this->AuditLogs->patchEntity($auditLog, $this->request->getData());
            if ($this->AuditLogs->save($auditLog)) {
                $this->Flash->success(__('The audit log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The audit log could not be saved. Please, try again.'));
        }
        $this->set(compact('auditLog'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Audit Log id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->set('title', 'AuditLogs Edit');
        $auditLog = $this->AuditLogs->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $auditLog = $this->AuditLogs->patchEntity($auditLog, $this->request->getData());
            if ($this->AuditLogs->save($auditLog)) {
                $this->Flash->success(__('The audit log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The audit log could not be saved. Please, try again.'));
        }
        $this->set(compact('auditLog'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Audit Log id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $auditLog = $this->AuditLogs->get($id);
        if ($this->AuditLogs->delete($auditLog)) {
            $this->Flash->success(__('The audit log has been deleted.'));
        } else {
            $this->Flash->error(__('The audit log could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function change()
    {
        if ($this->getRequest()->is('post')) {
            $check = $this->request->getData('check');
            $status = $this->request->getData('status');

            $this->AuditLogs->updateAll(
                ['status ' => $status],
                ['id IN' => $check]
            );
            $this->Flash->success(__('The selected action has been succesfully executed'));
            return $this->redirect($this->referer());
        }
    }
}
