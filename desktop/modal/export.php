<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
?>



<div id='div_exportAlert' style="display: none;"></div>

<div id="present" class="panel panel-primary">
	<div class="panel-heading">
        <h3 class="panel-title">Présent</h3>
	</div>
	<div class="panel-body">
		<div class="well1 well well-sm"></div>
		<div class="well2 well well-sm"></div>
		<div class="well3 well well-sm"></div>
	</div>
</div>

<div id="absent" class="panel panel-primary">
	<div class="panel-heading">
        <h3 class="panel-title">Absent</h3>
	</div>
	<div class="panel-body">
		<div class="well1 well well-sm"></div>
		<div class="well2 well well-sm"></div>
		<div class="well3 well well-sm"></div>
	</div>
</div>

<div id="nuit" class="panel panel-primary">
	<div class="panel-heading">
        <h3 class="panel-title">Nuit</h3>
	</div>
	<div class="panel-body">
		<div class="well1 well well-sm"></div>
		<div class="well2 well well-sm"></div>
		<div class="well3 well well-sm"></div>
	</div>
</div>
<div id="travail" class="panel panel-primary">
	<div class="panel-heading">
        <h3 class="panel-title">Travail</h3>
	</div>
	<div class="panel-body">
		<div class="well1 well well-sm"></div>
		<div class="well2 well well-sm"></div>
		<div class="well3 well well-sm"></div>
	</div>
</div>


<div>
<!--<a class="btn btn-success disabled"><i class='fa fa-floppy-o'></i> {{Enregistrer}}</a>-->
</div>

 
</div>

<?php include_file('desktop', 'export', 'js', 'presence');?>