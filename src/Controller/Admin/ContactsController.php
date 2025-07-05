<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\I18n\FrozenTime;

class ContactsController extends AppController
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
        /* if(isset($this->request->query['search'])){
		  $this->request->query['search'] = explode(" ", $this->request->query['search']);
		} */
    }

    public function index()
    {
        $this->set('title', 'Contacts Management');
        $this->paginate = [
            'maxLimit' => 10,
        ];
        //$query = $this->Contacts->find('search', search: $this->request->getQueryParams());
        //$contacts = $this->paginate($query);
        //$contacts = $this->paginate($this->Contacts);
        //$contacts = $this->paginate($this->Contacts->find('search', ['search' => $this->request->getQuery()]));
        $query = $this->Contacts->find('search', search: $this->request->getQueryParams());
        $contacts = $this->paginate($query);
        //count
        $this->set('total_contacts', $this->Contacts->find()->count());
        $this->set('total_contacts_pending', $this->Contacts->find()->where(['status' => 0])->count());
        $this->set('total_contacts_responded', $this->Contacts->find()->where(['status' => 1])->count());
        $this->set('total_contacts_ignored', $this->Contacts->find()->where(['status' => 2])->count());


        $query = $this->Contacts->find();

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

        $totalContactByMonth = [];
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

            $totalContactByMonth[] = [
                'month' => $expectedMonth,
                'count' => $count
            ];
        }

        $this->set([
            'results' => $totalContactByMonth,
            '_serialize' => ['results']
        ]);

        // JSON arrays
        $totalContactByMonth = json_encode($totalContactByMonth);
        $dataArray = json_decode($totalContactByMonth, true);
        $monthArray = [];
        $countArray = [];
        foreach ($dataArray as $data) {
            $monthArray[] = $data['month'];
            $countArray[] = $data['count'];
        }

        $this->set(compact('contacts', 'monthArray', 'countArray'));
    }

    public function view($id = null)
    {
        $this->set('title', 'Contacts Details');
        $contact = $this->Contacts->get($id, [
            //'contain' => ['Users'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->getData());
            $contact->respond_date_time = FrozenTime::now();
            $contact->status = 1;
            $contact->is_replied = true;
            $contact->user_id = $this->Authentication->getIdentity('id')->getIdentifier('id');
            if ($this->Contacts->save($contact)) {
                $this->Flash->success(__('The contact has been replied.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The contact could not be saved. Please, try again.'));
        }
        $users = $this->Contacts->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('contact', 'users'));
    }

    public function edit($id = null)
    {
        $this->set('title', 'Contact Ticket Details');
        $contact = $this->Contacts->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->getData());
            //$contact->user_id = $this->Authentication->getIdentity('id')->getIdentifier('id'); //capture auth id
            $contact->respond_date_time = FrozenTime::now();
            $contact->status = 1;
            if ($this->Contacts->save($contact)) {
                $this->Flash->success(__('The contact has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The contact could not be saved. Please, try again.'));
        }
        $users = $this->Contacts->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('contact', 'users'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $contact = $this->Contacts->get($id);
        if ($this->Contacts->delete($contact)) {
            $this->Flash->success(__('The contact has been deleted.'));
        } else {
            $this->Flash->error(__('The contact could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
