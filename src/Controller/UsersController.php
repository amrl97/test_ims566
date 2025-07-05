<?php

declare(strict_types=1);

namespace App\Controller;


use Cake\Mailer\Mailer;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
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
        $this->loadComponent('UserLogs');
        /* $this->loadComponent('Search.Search', [
            'actions' => ['index'],
        ]); */
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'registration', 'forgotPassword', 'forgotUsername', 'resetPassword', 'verify']);
    }

    public function login()
    {
        $this->set('title', 'Sign-in');
        $this->set('metaTitle', 'Re-CRUD Login');
        $this->set('metaKeywords', 'recrud, re-crud, login, auth');
        $this->set('metaSubject', 'Learning Coding');
        $this->set('metaCopyright', 'Re-CRUD');
        $this->set('metaDescription', 'This is a login page only');

        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            //$target = $this->Authentication->getLoginRedirect() ?? '/dashboard';
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Dashboards',
                'action' => 'index',
            ]);
            $this->UserLogs->userLoginActivity($this->Authentication->getIdentity('id')->getIdentifier('id'));
            $this->updateLoginFields(); //capture ip and login time
            $session = $this->request->getSession();
            return $this->redirect($redirect);
        }
        if ($this->request->is('post')) {
            $this->Flash->error('Invalid username or password');
        }
    }

    protected function updateLoginFields()
    {
        //$userTable = TableRegistry::get('Users');
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $user = $this->Authentication->getIdentity();
        $this->request->getSession()->write('User.last_login', date("Y-m-d H:i:s"));
        $this->request->getSession()->write('User.ip_address', $this->request->clientIp());
        $updateData = [
            'last_login' => date("Y-m-d H:i:s"),
            'ip_address' => $this->request->clientIp(),
        ];
        $this->Users->updateQuery()->set($updateData)->where(['id' => $user['id']])->execute();
    }

    public function logout()
    {
        $this->UserLogs->userLogoutActivity($this->Authentication->getIdentity('id')->getIdentifier('id'));
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function forgotUsername()
    {
        $this->set('title', 'Forgot Username');
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            //$userTable = TableRegistry::get('Users');
            //$userTable = TableRegistry::getTableLocator()->get('Users');
            //$userTable = TableRegistry::get('Users');
            $userTable = TableRegistry::getTableLocator()->get('Users');

            if ($email == NULL) {
                $this->Flash->error(('Please insert your email address'));
            }
            if ($user = $userTable->find('all')->where(['email' => $email])->first()) {
                $fullname = $user->fullname;
                if ($userTable->save($user)) {
                    $mailer = new Mailer('default');
                    $mailer->setTransport('default');
                    $mailer->setFrom(['noreply@codethepixel.com' => 'ReCRUD'])
                        ->setTo($email)
                        ->setEmailFormat('html')
                        ->setSubject('Forgot Username Request')
                        ->deliver('Hi<br/><br/>Your username is ' . $fullname . '</a>');
                }
                $this->Flash->success('Your username is sent to ' . $email . ', please check your email');
            }
            if ($total = $userTable->find('all')->where(['email' => $email])->count() == 0) {
                $this->Flash->error(('Email is not registered in system'));
            }
        }
    }

    public function forgotPassword()
    {
        $this->set('title', 'Forgot Password');
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            //$token = Security::hash(Security::randomBytes(25));
            $token = Security::hash(Security::randomBytes(32), 'sha256', true);
            //$userTable = TableRegistry::get('Users');
            $userTable = TableRegistry::getTableLocator()->get('Users');
            if ($email == NULL) {
                $this->Flash->error(('Please insert your email address'));
            }
            if ($user = $userTable->find('all')->where(['email' => $email])->first()) {
                $user->token = $token;
                $user->token_created_at = date('Y-m-d H:i:s');
                if ($userTable->save($user)) {
                    $mailer = new Mailer('default');
                    $mailer->setTransport('default');
                    $mailer->setFrom(['noreply@codethepixel.com' => 'ReCRUD'])
                        ->setTo($email)
                        ->setEmailFormat('html')
                        ->setSubject('Forgot Password Request')
                        ->deliver('Hello<br/>Please click link below to reset your password<br/><br/><a href="http://localhost/recrud/users/reset_password/' . $token . '">Reset Password</a>');
                }
                $this->Flash->success('Reset password link has been sent to your email (' . $email . '), please check your email');
            }
            if ($total = $userTable->find('all')->where(['email' => $email])->count() == 0) {
                $this->Flash->error(('Email is not registered in system'));
            }
        }
    }

    public function resetPassword($token = null)
    {
        $this->set('title', 'Reset Password');
        if (!$token) {
            $this->Flash->error('Invalid token.');
            return $this->redirect(['action' => 'forgotPassword']);
        }

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->find('all', [
            'conditions' => ['token' => $token, 'token_created_at >' => date('Y-m-d H:i:s', strtotime('-1 hour'))]
        ])->first();

        if (!$user) {
            $this->Flash->error('Invalid or expired token.');
            return $this->redirect(['action' => 'forgotPassword']);
        }

        $user = $this->Users->findByToken($token)->first();
        $password = $this->request->getData('password');

        if ($this->request->is(['post'])) {
            $user->password = $password;
            $user->token = null;
            $user->token_created_at = null;
            if ($this->Users->save($user)) {
                $this->Flash->success(('Your password has been successfully updated.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(('Your password could not be saved. Please, try again.'));
        }
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

    public function profile($slug = null)
    {
        $this->set('title', 'Account Details');

        $user = $this->Users
            ->findBySlug($slug)
            ->contain(['UserGroups', 'Applications', 'Contacts', 'UserLogs'])
            ->firstOrFail();

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
            ->contain(['UserGroups'])
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
        $this->set(compact('user', 'userGroups'));
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

        /*  $auditLogs = $this->AuditLogs->find('all')
            ->contain([
                'Comments' => function ($q) {
                    return $q->limit(5); // Limit to 5 comments per article
                }
            ])
            ->toList(); */

        /* $this->userLogs = $this->fetchTable('userLogs');
        $userLogs = $this->userLogs->find('all')
            ->where(['user_id' => $user->id])
            ->limit(10)
            ->orderBy(['created' => 'DESC']); */


        $this->set(compact('user', 'userLogs'));

        //$this->set(compact('user', 'userGroups'));
    }

    public function pdfProfile($slug = null)
    {
        $this->viewBuilder()->enableAutoLayout(false);
        //$user = $this->Users->get($slug);
        $user = $this->Users
            ->findBySlug($slug)
            ->contain(['UserGroups'])
            ->firstOrFail();
        //debug($user);
        //exit;
        $this->viewBuilder()->setClassName('CakePdf.Pdf');
        $this->viewBuilder()->setOption(
            'pdfConfig',
            [
                'orientation' => 'portrait',
                'download' => true, // This can be omitted if "filename" is specified.
                'filename' => 'User_' . $slug . '.pdf' //// This can be omitted if you want file name based on URL.
            ]
        );
        $this->set('user', $user);
    }
}