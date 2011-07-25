<?php

/*
 * ./htdocs/admin/pool-worker.php
 *
 * Copyright (C) 2011  Chris Howie <me@chrishowie.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../admin/controller.inc.php');
require_once(dirname(__FILE__) . '/../models/pool-worker.inc.php');
require_once(dirname(__FILE__) . '/../views/admin/pool-worker.view.php');

class AdminPoolWorkerController extends AdminController
{
    public function indexGetView($request)
    {
        $id = (int)$request['id'];

        if ($id == 0) {
            return new RedirectView('/admin/pool.php');
        }

        $pdo = db_connect();

        $name = db_query($pdo, '
            SELECT name

            FROM pool

            WHERE id = :pool_id
        ', array(':pool_id' => $id));

        if (count($name) == 0) {
            return new RedirectView('/admin/pool.php');
        }

        $name = $name[0]['name'];

        $viewdata = array(
            'pool-id'     => $id,
            'pool-name'   => $name
        );

        $viewdata['pool-workers'] = db_query($pdo, '
            SELECT
                wp.pool_username AS username,
                wp.pool_password AS password,
                wp.priority AS priority,
                wp.enabled AS enabled,

                w.name AS `worker-name`,
                w.id AS `worker-id`,

                p.id AS `pool-id`

            FROM worker w

            INNER JOIN pool p
                ON p.id = :pool_id

            LEFT OUTER JOIN worker_pool wp
                ON p.id = wp.pool_id
               AND wp.worker_id = w.id

            ORDER BY w.name
        ', array(':pool_id' => $id));

        return new AdminPoolWorkerView($viewdata);
    }

    public function setEnabledPostView($request)
    {
        $pool_id = (int)$request['pool_id'];
        $worker_id = (int)$request['worker_id'];
        $enabled = (int)$request['enabled'];

        if ($pool_id == 0 || $worker_id == 0) {
            return new RedirectView('/admin/pool.php');
        }

        $pdo = db_connect();

        $q = $pdo->prepare('
            UPDATE worker_pool

            SET enabled = :enabled

            WHERE worker_id = :worker_id
              AND pool_id = :pool_id
        ');

        $q->execute(array(
            ':enabled'      => $enabled,
            ':pool_id'      => $pool_id,
            ':worker_id'    => $worker_id
        ));

        if (!$q->rowCount()) {
            $_SESSION['tempdata']['errors'][] =
                sprintf('Worker not found or not affected.');
        }

        return new RedirectView("/admin/pool-worker.php?id=$pool_id");
    }

    public function setGlobalValuesPostView($request)
    {
        $pool_id = (int)$request['pool_id'];
        $priority = ($request['priority']) ? $request['priority'] : NULL;
        $username = ($request['username']) ? $request['username'] : NULL;
        $password = ($request['password']) ? $request['password'] : NULL;

        if ($pool_id == 0) {
            return new RedirectView('/admin/pool.php');
        }

        $pdo = db_connect();

        $q = $pdo->prepare('
            UPDATE worker_pool

               SET priority = COALESCE(:priority,priority),
                   pool_username = COALESCE(:username,pool_username),
                   pool_password = COALESCE(:password,pool_password)

             WHERE pool_id = :pool_id
        ');
        $q->execute(array(
            ':priority'     => $priority,
            ':username'     => $username,
            ':password'     => $password,
            ':pool_id'      => $pool_id
        ));
        if (!$q->rowCount()) {
            $_SESSION['tempdata']['errors'][] =
                sprintf('Worker not found or not affected.');
        }

        return new RedirectView("/admin/pool-worker.php?id=$pool_id");
    }

    public function editGetView(PoolWorkerModel $model)
    {
        if ($model->pool_id == 0) {
            return new RedirectView('/admin/pool.php');
        }

        if ($model->worker_id == 0) {
            return new RedirectView("/admin/pool-worker.php?id={$model->pool_id}");
        }

        $model->refresh();

        return new PoolWorkerEditView(array('pool-worker' => $model));
    }

    public function editPostView(PoolWorkerModel $model)
    {
        if ($model->pool_id == 0) {
            return new RedirectView('/admin/pool.php');
        }

        if ($model->worker_id == 0) {
            return new RedirectView("/admin/pool-worker.php?id={$model->pool_id}");
        }

        $errors = $model->validate();
        if ($errors !== TRUE) {
            $_SESSION['tempdata']['errors'] = array_merge(
                (array)$_SESSION['tempdata']['errors'], $errors);

            return new PoolWorkerEditView(array('pool-worker' => $model));
        }

        if (!$model->save()) {
            $_SESSION['tempdata']['errors'][] = 'Unable to save worker pool data.';

            return new PoolWorkerEditView(array('pool-worker' => $model));
        }

        return new RedirectView("/admin/pool-worker.php?id={$model->pool_id}");
    }

    public function deleteDefaultView(PoolWorkerModel $model)
    {
        if ($model->pool_id != 0 && $model->worker_id != 0) {
            $pdo = db_connect();

            $q = $pdo->prepare('
                DELETE FROM worker_pool

                WHERE worker_id = :worker_id
                  AND pool_id = :pool_id
            ');

            $q->execute(array(
                ':worker_id'    => $model->worker_id,
                ':pool_id'      => $model->pool_id
            ));

            if (!$q->rowCount()) {
                $_SESSION['tempdata']['errors'][] = 'Unable to delete worker pool assignment.';
            }
        }

        if ($model->pool_id != 0) {
            return new RedirectView("/admin/pool-worker.php?id={$model->pool_id}");
        }

        return new RedirectView('/admin/pool.php');
    }
}

MvcEngine::run(new AdminPoolWorkerController());

?>
