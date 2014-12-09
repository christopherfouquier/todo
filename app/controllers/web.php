<?php
require_once __DIR__.'/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use app\model\Tasks as Task;
use app\model\TasksQuery;
use app\Form\Type\TaskType;
use app\model\Users as User;
use app\Form\Type\UserType;
use app\model\Categories as Category;
use app\model\CategoriesQuery;

$app->before(function () use ($app) {
    $token = $app['security']->getToken();
    $currentUser = null;
    if (null !== $token) {
        if ($token->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $currentUser = $token->getUser();
        }
    }
    $app['currentUser'] = $currentUser;
    $app["twig"]->addGlobal("currentUser", $currentUser);
});

$app->get('/', function(Request $request) use ($app) {
    if($app['currentUser'] != null) {
        return $app->redirect($app['url_generator']->generate('manager'));
    }
    $user = new User();
    $form = $app['form.factory']->create(new UserType(), $user);

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $user->setRoles('ROLE_USER');
            $user->save();

            $app['session']->getFlashBag()->add('success', 'Inscription effectuer avec succès, vous pouvez-vous connecter.');

            return $app->redirect($app['url_generator']->generate('homepage'));
        }
    }
    return $app['twig']->render('index.html.twig', array(
        'form'          => $form->createView(),
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->method('get|post')
->bind('homepage');

$app->get('/manager', function(Request $request) use ($app) {
    $page = $request->query->get('p');
    if (!$page) {
        $page = 1;
    }
    $maxPerPage = 20;
    $q = $request->query->get('q');
    $c = $request->query->get('c');
    $o = $request->query->get('order');
    if ($o == null) {
        $o = 'DESC';
    }
    if ($q) {
        $tasks = TasksQuery::create()->joinWithCategories()->filterByUserId($app['currentUser']->getId())->filterByName('%'.$q.'%')->orderByCreated($o)->paginate($page, $maxPerPage);
    }
    elseif ($c) {
        $tasks = TasksQuery::create()->joinWithCategories()->filterByUserId($app['currentUser']->getId())->filterByCategoryId($c)->orderByCreated($o)->paginate($page, $maxPerPage);
    }
    else {
        $tasks = TasksQuery::create()->joinWithCategories()->filterByUserId($app['currentUser']->getId())->orderByCreated($o)->paginate($page, $maxPerPage);
    }
    $categories = CategoriesQuery::create()->orderByName()->findByUserId($app['currentUser']->getId());
    $a_categories = CategoriesQuery::create()->select('name')->orderByName()->filterByUserId($app['currentUser']->getId())->find()->toArray();

    /* A Obtimiser */
    $c_categories = CategoriesQuery::create()->select('id')->filterByUserId($app['currentUser']->getId())->find()->toArray();
    $c_tasks = TasksQuery::create()->select('categoryId')->filterByUserId($app['currentUser']->getId())->find()->toArray();
    foreach ($c_categories as $key => $value) {
        if (!in_array($value, $c_tasks)) {
            $category = CategoriesQuery::create()->findOneById($value)->delete();
        }
    }

    return $app['twig']->render('manager.html.twig', array(
        'tasks' => $tasks,
        'categories' => $categories,
        'list' => $a_categories
    ));
})
->bind('manager');

$app->get('/task/validate', function(Request $request) use ($app) {
    $id = $request->query->get('id');
    if ($id) {
        $task = TasksQuery::create()->findOneById($id);
        if ($task) {
            $task->setStatus(1);
            $task->save();
            return 'Task save';
        }
        else {
            return 'Task not found';
        }
    }
    else {
        return 'No data';
    }
})
->bind('validateTask');

$app->post('/task/add', function(Request $request) use ($app) {
    $data = $request->request->all();
    if ($data) {
        $tabDate = explode('/' , $data['created']);
        if (!array_key_exists(2, $tabDate)) {
            $tabDate[2] = date("Y");
        }
        $date_conv  = $tabDate[2].'-'.$tabDate[1].'-'.$tabDate[0];

        $category = CategoriesQuery::create()->findOneByName($data['category']);
        if (!$category) {
            $category = new Category();
            $category->setName($data['category']);
            $category->setUserId($app['currentUser']->getId());
            $category->save();
        }

        $task = new Task();
        $task->setName($data['name']);
        $task->setCreated($date_conv);
        $task->setCategoryId($category->getId());
        $task->setUserId($app['currentUser']->getId());
        $task->save();
        return 'Task save';
    }
    else {
        return 'No data';
    }
})
->bind('addTask');

$app->post('/task/update', function(Request $request) use ($app) {
    $data = $request->request->all();
    if ($data) {
        $task = TasksQuery::create()->findOneById($data[0]);
        if ($task) {
            //$date = date("Y-m-d", strtotime($data[3]));
            $tabDate = explode('/' , $data[3]);
            if (!array_key_exists(2, $tabDate)) {
                $tabDate[2] = date("Y");
            }
            $date_conv  = $tabDate[2].'-'.$tabDate[1].'-'.$tabDate[0];

            $category = CategoriesQuery::create()->findOneByName($data[2]);
            if (!$category) {
                $category = new Category();
                $category->setName($data[2]);
                $category->setUserId($app['currentUser']->getId());
                $category->save();
            }

            $task->setName($data[1]);
            $task->setCreated($date_conv);
            $task->setCategoryId($category->getId());
            $task->setUserId($app['currentUser']->getId());
            $task->save();
            return 'Task save';
        }
        else {
            return 'Task not found';
        }
    }
    else {
        return 'No data';
    }
})
->bind('updateTask');

$app->get('/task/delete', function(Request $request) use ($app) {
    if ($request->getMethod() == 'POST') {
        $data = $request->request->all();
        if ($data) {
            $tasks = TasksQuery::create()->filterById($data)->find();
            if ($tasks->count() === 0) {
                return 'Task not found';
            }
            $tasks->delete();
            return 'Task save';
        }
        else {
            return 'No data';
        }
    }
    else {
        $id = $request->query->get('id');
        if ($id) {
            $task = TasksQuery::create()->findOneById($id);
            if (!$task) {
                return 'Task not found';
            }
            $task->delete();
            return 'Task save';
        }
        else {
            return 'No data';
        }
    }
})
->method('get|post')
->bind('deleteTask');

return $app;