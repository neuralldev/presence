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


<div>
<h1 lang="fr"><span dir="auto">Le Plugin Présence</span></h1>
<p>Cette aide est en construction.</p>
<p>Le plugin de gestion de la présence vous permet de gérer plusieurs modes (Présent / Absent / Nuit / Travail / Vacances) sur lesquels vous allez pouvoir définir des déclencheurs et actions</p>
<p>Une fois le plugin activé rendez-vous dans Plugins / Organisation / Présence pour ajouter un nouveau module.</p>

<p>L'indicateur "Jour de retour" permet au mode vacance de connaitre le temps sur lequel se baser pour effectuer les actions avant le retour.</p>
<p>Pour passer en mode vacances et sélectionner la date de retour il suffit de cliquer sur la date sur le widget d'accueil.</p>

<p>5 onglets permettent de gérer les différents modes.</p>
<img src="plugins/presence/desktop/modal/modes.png" width="500px" heigth="150px" />
<p>Les modes automatiques sont les suivants :</p>
<ul>
	<li>Présent</li>
	<li>Absent</li>
	<li>Nuit</li>
	<li>Travail</li>
</ul>

<h1 lang="fr"><span dir="auto">Les déclencheurs</span>&nbsp<img src="plugins/presence/desktop/modal/declencheur.png" width="120px" heigth="30px" /></h1>
<p>Les déclencheurs permettent d'activer un mode. Le mode sera validé seulement si le déclencheur est dans l'état programmé depuis le temps choisis.</p>
<img src="plugins/presence/desktop/modal/declencheur2.png" width="1000px" heigth="300px" />
<p>Il est possible de combiner des déclencheurs en utilisant la fonction "Et". A ce moment il faudra que les conditions des déclencheurs "Et" soient toutes validées pour que le mode soit activé.</p>

<h1 lang="fr"><span dir="auto">Les actions</span>&nbsp<img src="plugins/presence/desktop/modal/action.png" width="120px" heigth="30px" /></h1>
<p>Il est possible d'effectuer une action lors d'un passage dans un mode.</p>
<p>Pour en ajouter une il suffit de cliquer sur "Ajouter action" puis de sélectionner l'action souhaité.</p>
<img src="plugins/presence/desktop/modal/action2.png" width='400px" heigth="200px" />

<h1 lang="fr"><span dir="auto">Les scénarios</span></h1>
<p>Pour utiliser le module sur des conditions (If) dans les scénarios les valeurs suivantes doivent être utilisées :</p>
<ul>
	<li>Présent : 1</li>
	<li>Absent : 2</li>
	<li>Nuit : 3</li>
	<li>Travail : 4</li>
	<li>Travail : 5</li>
</ul>


<!--<img src="plugins/teleinfo/desktop/modal/configuration.png" width="800px" heigth="500px" />-->

</div>