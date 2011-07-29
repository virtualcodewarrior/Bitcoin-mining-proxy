<?php

/*
 * ./htdocs/views/admin/workers.view.php
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

require_once(dirname(__FILE__) . '/../master.view.php');

abstract class WorkersView
    extends MasterView
{
    protected function getMenuId()
    {
        return "workers";
    }
}

class AdminWorkersView
    extends WorkersView
    implements IJsonView
{
    protected function getTitle()
    {
        return "Worker management";
    }

    protected function renderBody()
    {
?>

<div id="workers">

<table class="data centered">
    <tr>
        <th>Name</th>
        <th>Password</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($this->viewdata['workers'] as $row) { ?>
    <tr>
        <td><?php echo_html($row['name'])     ?></td>
        <td><?php echo_html($row['password']) ?></td>
        <td>
            <form action="<?php echo_html(make_url('/admin/worker-pool.php')) ?>">
                <fieldset>
                    <input type="hidden" name="id" value="<?php echo_html($row['id']) ?>" />
                    <?php $this->renderImageButton('index', 'manage-pools', 'Manage pools') ?>
                </fieldset>
            </form>
            <form action="<?php echo_html(make_url('/admin/workers.php')) ?>" method="get">
                <fieldset>
                    <input type="hidden" name="id" value="<?php echo_html($row['id']) ?>" />
                    <?php
                        $this->renderImageButton('edit', 'edit-worker', 'Edit worker');
                        $this->renderImageButton('stats', 'worker-stats', 'Worker stats');
                        if ($row['pools'] == 0) {
                            $this->renderImageButton('delete', 'delete-worker', 'Delete worker');
                        }
                    ?>
                </fieldset>
            </form>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td>
            <form action="<?php echo_html(make_url('/admin/workers.php')) ?>">
                <fieldset>
                    <?php $this->renderImageButton('new', 'new-worker', 'New worker') ?>
                </fieldset>
            </form>
        </td>
    </tr>
</table>

</div>

<?php
    }
}

class AdminWorkerNewEditView
    extends WorkersView
{
    protected function getTitle()
    {
        $worker = $this->viewdata['worker']->name;
        return $this->viewdata['worker']->id ? "Edit worker - $worker" : "New worker";
    }

    protected function getDivId()
    {
        return $this->viewdata['worker']->id ? 'edit-worker' : 'new-worker';
    }

    protected function getAction()
    {
        return $this->viewdata['worker']->id ? 'edit' : 'new';
    }

    protected function getSubmitValue()
    {
        return $this->viewdata['worker']->id ? 'Save changes' : 'Create worker';
    }

    protected function renderBody()
    {
?>

<div id="<?php echo $this->getDivId() ?>">

<form action="<?php echo_html(make_url('/admin/workers.php')) ?>" method="post">

<fieldset>
<input type="hidden" name="action" value="<?php echo $this->getAction() ?>" />
<?php if ($this->viewdata['worker']->id) { ?>
<input type="hidden" name="id" value="<?php echo_html($this->viewdata['worker']->id) ?>" />
<?php } ?>
</fieldset>

<table class="entry centered">
    <tr>
        <th><label for="name">Name:</label></th>
        <td><input name="name" id="name" size="25" value="<?php echo_html($this->viewdata['worker']->name) ?>" /></td>
    </tr>
    <tr>
        <th><label for="password">Password:</label></th>
        <td><input name="password" id="password" size="25" value="<?php echo_html($this->viewdata['worker']->password) ?>" /></td>
    </tr>
    <tr class="submit">
        <td>&nbsp;</td>
        <td><input type="submit" value="<?php echo $this->getSubmitValue() ?>" /></td>
    </tr>
</table>

</form>

</div>

<?php
    }
}

class AdminWorkerStatsView
    extends WorkersView
{
    protected function getTitle()
    {
        $worker = $this->viewdata['worker']['name'];
        return "Worker Stats - $worker";
    }

    protected function renderBody()
    {
        $WorkerStatsByHourCount = count($this->viewdata['WorkerStatsByHour']);
        $WorkerStatsByDateCount = count($this->viewdata['WorkerStatsByDate']);
?>

<div id="worker-stats">

<script type="text/javascript">
    google.setOnLoadCallback(drawChartWorkerMHashByHour);
    function drawChartWorkerMHashByHour() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Hour');
        data.addColumn('number', 'MHash');
        data.addRows(<?php echo $WorkerStatsByHourCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByHour'] as $row) {
            $hour = date('H:i',strtotime(format_date($row['hour'])));
            echo "data.setValue({$idx}, 0, '{$hour}'); ";
            echo "data.setValue({$idx}, 1, {$row['mhash']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workermhashbyhourchart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Orange'],
                          legend: 'none',
                          title: 'MHash Average - Last 24 Hours'});
    }

    google.setOnLoadCallback(drawChartWorkerMHashByDate);
    function drawChartWorkerMHashByDate() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'MHash');
        data.addRows(<?php echo $WorkerStatsByDateCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByDate'] as $row) {
            $date = date('m-d',strtotime(format_date($row['date'])));
            echo "data.setValue({$idx}, 0, '{$date}'); ";
            echo "data.setValue({$idx}, 1, {$row['mhash']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workermhashbydatechart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Orange'],
                          legend: 'none',
                          title: 'MHash Average - Last Month'});
    }

    google.setOnLoadCallback(drawChartWorkerSharesByHour);
    function drawChartWorkerSharesByHour() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Hour');
        data.addColumn('number', 'Valid');
        data.addColumn('number', 'Rejected');
        data.addRows(<?php echo $WorkerStatsByHourCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByHour'] as $row) {
            $hour = date('H:i',strtotime(format_date($row['hour'])));
            echo "data.setValue({$idx}, 0, '{$hour}'); ";
            echo "data.setValue({$idx}, 1, {$row['shares']}); ";
            echo "data.setValue({$idx}, 2, {$row['rejected']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workersharesbyhourchart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Green', 'Red'],
                          legend: 'none',
                          title: 'Shares - Last 24 Hours'});
    }

    google.setOnLoadCallback(drawChartWorkerSharesByDate);
    function drawChartWorkerSharesByDate() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'Valid');
        data.addColumn('number', 'Rejected');
        data.addRows(<?php echo $WorkerStatsByDateCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByDate'] as $row) {
            $date = date('m-d',strtotime(format_date($row['date'])));
            echo "data.setValue({$idx}, 0, '{$date}'); ";
            echo "data.setValue({$idx}, 1, {$row['shares']}); ";
            echo "data.setValue({$idx}, 2, {$row['rejected']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workersharesbydatechart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Green', 'Red'],
                          legend: 'none',
                          title: 'Shares - Last Month'});
    }

    google.setOnLoadCallback(drawChartWorkerGetworksByHour);
    function drawChartWorkerGetworksByHour() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Hour');
        data.addColumn('number', 'Getworks');
        data.addRows(<?php echo $WorkerStatsByHourCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByHour'] as $row) {
            $hour = date('H:i',strtotime(format_date($row['hour'])));
            echo "data.setValue({$idx}, 0, '{$hour}'); ";
            echo "data.setValue({$idx}, 1, {$row['getworks']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workergetworksbyhourchart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Blue'],
                          legend: 'none',
                          title: 'Getworks - Last 24 Hours'});
    }

    google.setOnLoadCallback(drawChartWorkerGetworksByDate);
    function drawChartWorkerGetworksByDate() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'Getworks');
        data.addRows(<?php echo $WorkerStatsByDateCount ?>);
        <?php
        $idx = 0;
        foreach ($this->viewdata['WorkerStatsByDate'] as $row) {
            $date = date('m-d',strtotime(format_date($row['date'])));
            echo "data.setValue({$idx}, 0, '{$date}'); ";
            echo "data.setValue({$idx}, 1, {$row['getworks']}); ";
            echo "\n";
            $idx++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('workergetworksbydatechart_div'));
        chart.draw(data, {width: 500, height: 200,
                          colors: ['Blue'],
                          legend: 'none',
                          title: 'Getworks - Last Month'});
    }
</script>

<table class="centered">
    <tr>
        <td><div id="workermhashbyhourchart_div"></div></td>
        <td><div id="workermhashbydatechart_div"></div></td>
    </tr>
    <tr>
        <td><div id="workersharesbyhourchart_div"></div></td>
        <td><div id="workersharesbydatechart_div"></div></td>
    </tr>
    <tr>
        <td><div id="workergetworksbyhourchart_div"></div></td>
        <td><div id="workergetworksbydatechart_div"></div></td>
    </tr>
</table>

</div>

<?php
    }
}
?>
