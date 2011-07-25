<?php

/*
 * ./htdocs/views/admin/pool-worker.view.php
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

require_once(dirname(__FILE__) . '/pool.view.php');
require_once(dirname(__FILE__) . '/../master.view.php');

class AdminPoolWorkerView
    extends PoolView
    implements IJsonView
{
    protected function getTitle()
    {
        return "Pool worker management - " . $this->viewdata['pool-name'];
    }

    protected function renderBody()
    {
?>

<div id="pool-workers">

<table class="data centered">
    <tr>
        <th>Worker</th>
        <th>Priority</th>
        <th>Enabled</th>
        <th>Pool username</th>
        <th>Pool password</th>
    </tr>
    <?php foreach ($this->viewdata['pool-workers'] as $row) { ?>
    <tr class="<?php if (isset($row['username']) && (!$row['enabled'])) { echo 'disabled'; } ?>">
        <td>
            <form action="<?php echo_html(make_url('/admin/pool-worker.php')) ?>" method="get">
                <fieldset>
                    <input type="hidden" name="worker_id" value="<?php echo_html($row['worker-id']) ?>" />
                    <input type="hidden" name="pool_id" value="<?php echo_html($this->viewdata['pool-id']) ?>" />

                    <?php
                        if (isset($row['username'])) {
                            $this->renderImageButton('edit', 'edit-pool-assignment', 'Edit pool assignment');
                            $this->renderImageButton('delete', 'delete-pool-assignment', 'Delete pool assignment');
                        } else {
                            $this->renderImageButton('edit', 'create-pool-assignment', 'Create pool assignment');
                        }
                    ?>
                </fieldset>
            </form>
            <?php echo_html($row['worker-name']) ?></td>
        <?php if (!isset($row['username'])) { ?>
        <td colspan="5">&nbsp;</td>
        <?php } else { ?>
        <td><?php echo_html($row['priority']) ?></td>
        <td class="enabled-column">
            <?php
                $newstatus = $row['enabled'] ? 0 : 1;
            ?>
            <form action="<?php echo_html(make_url('/admin/pool-worker.php')) ?>" method="post">
                <fieldset>
                    <input type="hidden" name="worker_id" value="<?php echo_html($row['worker-id']) ?>" />
                    <input type="hidden" name="pool_id" value="<?php echo_html($this->viewdata['pool-id']) ?>" />
                    <input type="hidden" name="enabled" value="<?php echo_html($newstatus) ?>" />
                    <?php
                        if ($row['enabled']) {
                            $this->renderImageButton('setEnabled', 'enabled', 'Yes', 'Toggle');
                        } else {
                            $this->renderImageButton('setEnabled', 'disabled', 'No', 'Toggle');
                        }
                    ?>
                </fieldset>
            </form>
        </td>
        <td><?php echo_html($row['username']) ?></td>
        <td><?php echo_html($row['password']) ?></td>
        <?php } ?>
    </tr>
    <?php } ?>
</table>

</div>

<?php
    }
}

class PoolWorkerEditView
    extends PoolView
{
    protected function getTitle()
    {
        $model = $this->viewdata['pool-worker'];

        return "Worker pool management - {$model->worker_name} on {$model->pool_name}";
    }

    protected function renderBody()
    {
        $model = $this->viewdata['pool-worker'];

?>

<div id="edit-pool-worker">

<form action="<?php echo_html(make_url('/admin/pool-worker.php')) ?>" method="post">
<fieldset>
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="worker_id" value="<?php echo_html($model->worker_id) ?>" />
<input type="hidden" name="pool_id" value="<?php echo_html($model->pool_id) ?>" />
</fieldset>

<table class="entry centered">
    <tr>
        <th>Worker:</th>
        <td><?php echo_html($model->worker_name) ?></td>
    </tr>
    <tr>
        <th>Pool:</th>
        <td><?php echo_html($model->pool_name) ?></td>
    </tr>
    <tr>
        <th>Priority:</th>
        <td><input type="text" maxlength="3" size="3" name="priority"
            value="<?php echo_html($model->priority) ?>" /></td>
    </tr>
    <tr>
        <th>Enabled:</th>
        <td><input type="checkbox" name="enabled" value="1" <?php
            if ($model->enabled) { ?>checked="checked" <?php } ?> /></td>
    </tr>
    <tr>
        <th>Pool username:</th>
        <td><input type="text" name="pool_username" size="50" value="<?php echo_html($model->pool_username) ?>" /></td>
    </tr>
    <tr>
        <th>Pool password:</th>
        <td><input type="text" name="pool_password" size="50" value="<?php echo_html($model->pool_password) ?>" /></td>
    </tr>
    <tr class="submit">
        <td>&nbsp;</td>
        <td><input type="submit" value="Save" /></td>
    </tr>
</table>

</form>

</div>

<?php
    }
}

?>
