<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\View\JsonView;

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
            'actions' => ['search'],
        ]);
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index']);
    }

    public function viewClasses(): array
    {
        return [JsonView::class];
        //return [JsonView::class, XmlView::class];
    }

    public function index()
    {
        $this->set('title', 'Frequently Asked Questions');
        $this->paginate = [
            'maxLimit' => 10,
        ];
        //Search
        //$faqs = $this->paginate($this->Faqs->find('search', ['search' => $this->request->getQuery()]));

        $this->set('general', $this->paginate($this->Faqs->find('all')->where(['category' => 'General', 'status' => '1'])));
        $this->set('account', $this->paginate($this->Faqs->find('all')->where(['category' => 'Account', 'status' => '1'])));
        $this->set('other', $this->paginate($this->Faqs->find('all')->where(['category' => 'Other', 'status' => '1'])));

        //$this->set(compact('faqs'));
    }
}
