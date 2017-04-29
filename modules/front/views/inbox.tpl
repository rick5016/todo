{% set annee = '' %}
{% set jourMois = '' %}
{% set displayHr = true %}
Priority : <a href="todo.php?page=inbox"><span style="color:#97CEFA;font-weight:bold">None</span></a>, 
<a href="todo.php?page=inbox&priority=1"><span style="color:red;font-weight:bold">1</span></a>, 
<a href="todo.php?page=inbox&priority=2"><span style="color:orange;font-weight:bold">2</span></a>, 
<a href="todo.php?page=inbox&priority=3"><span style="color:#E8D630;font-weight:bold">3</span></a>, 
<a href="todo.php?page=inbox&priority=4"><span style="color:wheat;font-weight:bold">4</span></a><br />

<form method="post">
    <label class="checkbox-inline">
      <input {% if past is defined %}checked{% endif %} type="checkbox" name="past" /> Tâche(s) passsée(s) *1
    </label>
    <label class="checkbox-inline">
      <input {% if end is defined %}checked{% endif %} type="checkbox" name="end" /> Tâche(s) Finie(s) *2
    </label>
    <label class="checkbox-inline">
      <input {% if ant is defined %}checked{% endif %} type="checkbox" name="ant" /> Tâche(s) future(s) *1
    </label>
    <input type="submit" value="Filtrer" />
</form>
  *1 Non finie(s)<br />
  *2 Aujourd'hui<br />
{% for task in tasks %}

    {% set calendar = task.calendars.0 %}
    {% set dateStart = calendar.dateStart|date('Y-m-d', timezone="Europe/Paris") %}

    <!-- Gere la ligne entre les tâches anterieures à la date du jour et les autres -->
    {% if dateStart|date('d/m/Y', timezone="Europe/Paris") > "now"|date('d/m/Y', timezone="Europe/Paris") and displayHr %}
        <hr />
        {% set displayHr = false %}
    {% endif %}
        
    <!-- Gestion de l'affichage de l'année : TODO à tester -->
    {% if annee != dateStart|date('Y', timezone="Europe/Paris") %}
        {% if annee is not empty %}
            <ul>
        {% endif %}
        <ul>
        {% set annee = dateStart|date('Y', timezone="Europe/Paris") %}
        <li style="list-style-type:none;"><h3>{{ annee }}</h3></li>
    {% endif %}

    <!-- Gestion de l'affichage du mois et du jour : TODO proposer différent mode d'affichage -->
    {% if jourMois != dateStart|date('d/m', timezone="Europe/Paris") %}
        </ul><ul>
        {% if dateStart|date('d/m/Y', timezone="Europe/Paris") == "now"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Aujourd'hui" %}
        {% else %}
            {% set display = dateStart|date('d/m', timezone="Europe/Paris") %}
        {% endif %}
        {% set jourMois = dateStart|date('d/m', timezone="Europe/Paris") %}
        <li style="list-style-type:none;"><h4>{{ display }}</h4></li>
    {% endif %}

    <ul>
        {% if task.priority == 0 %}<span style="color:#97CEFA;font-weight:bold">Aucune Priorité</span>
        {% elseif task.priority == 1 %}<span style="color:red;font-weight:bold">Priorité 1</span>
        {% elseif task.priority == 2 %}<span style="color:orange;font-weight:bold">Priorité 2</span>
        {% elseif task.priority == 3 %}<span style="color:#E8D630;font-weight:bold">Priorité 3</span>
        {% elseif task.priority == 4 %}<span style="color:wheat;font-weight:bold">Priorité 4</span>
        {% endif %}
        Créé le : {{ calendar.dateCreateCalendar|date('d/m', timezone="Europe/Paris") }} - 
        {% if calendar.reiterate == 0 %}Unique{% elseif calendar.reiterate == 1 %}Chaque jour{% elseif calendar.reiterate == 2 %}Chaque semaine{% elseif calendar.reiterate == 3 %}Chaque mois{% elseif calendar.reiterate == 4 %}Chaque année{% elseif calendar.reiterate == 5 %}Custom{% endif %}
        <br />
        <li style="list-style-type:none;">
            {% if calendar.done == false %}
                <a href="index.php?page=done&id={{ calendar.id }}"><span title="à faire" class="glyphicon glyphicon-unchecked" style="color:red"></span></a> 
            {% else %}
                <span title="fait" class="glyphicon glyphicon-check" style="color:green"></span> 
            {% endif %}
            <b>{{ task.name }}</b> <a href="index.php?page=task&id={{ task.id }}"><span title="modifier" class="glyphicon glyphicon-edit"></span></a> <a title="supprimer" href="index.php?page=del&id={{ task.id }}"><span class="glyphicon glyphicon-remove"></span></a>
        </li>
        <br /><br />
    </ul>
    
{% endfor %}