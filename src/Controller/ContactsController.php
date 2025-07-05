<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Http\ServerRequest;

/**
 * Contacts Controller
 *
 * @property \App\Model\Table\ContactsTable $Contacts
 */
class ContactsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['search', 'check', 'index'],
        ]);
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['add', 'check']);
        /* if(isset($this->request->query['search'])){
		  $this->request->query['search'] = explode(" ", $this->request->query['search']);
		} */
    }


    public function add()
    {
        $this->set('title', 'Contact');
        $contact = $this->Contacts->newEmptyEntity();
        if ($this->request->is('post')) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->getData());
            $contact->ip = $this->request->clientIp();
            $contact->status =  1;
            if ($this->Contacts->save($contact)) {
                $this->Flash->success(__('The contact has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The contact could not be saved. Please, try again.'));
        }
        $users = $this->Contacts->Users->find('list', limit: 200)->all();
        $this->set(compact('contact', 'users'));
    }

    public function check()
    {
        $this->set('title', 'Contact Ticket Response Check');
        //$this->paginate['maxLimit'] = 999;
        //$this->paginate = [
        //    'contain' => '',
        //];
        //$contacts = $this->paginate($this->Contacts->find('search', ['search' => $this->request->getQuery()]));
        $query = $this->Contacts->find('search', search: $this->request->getQueryParams());
        $contacts = $this->paginate($query);

        $ticket = $this->request->getQuery('ticket');
        if ($ticket != NULL) {
            $this->set('count_ticket', $this->Contacts->find()->where(['ticket' => $ticket])->count());
        } elseif ($ticket == null) {
            $this->set('count_ticket', '0');
        } else
            $this->set('count_ticket', '2');

        $this->set(compact('contacts'));
        $this->set('_serialize', ['contacts']);
    }
}
