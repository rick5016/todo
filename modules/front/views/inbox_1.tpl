{% set showDone = true %}
{% set annee = '' %}
{% set jourMois = '' %}
Priority : <a href="todo.php?page=inbox"><span style="color:#97CEFA;;font-weight:bold">None</span></a>, 
<a href="todo.php?page=inbox&priority=1"><span style="color:red;;font-weight:bold">1</span></a>, 
<a href="todo.php?page=inbox&priority=2"><span style="color:orange;;font-weight:bold">2</span></a>, 
<a href="todo.php?page=inbox&priority=3"><span style="color:#E8D630;;font-weight:bold">3</span></a>, 
<a href="todo.php?page=inbox&priority=4"><span style="color:wheat;;font-weight:bold">4</span></a><br />
{% for task in tasks %}
    {% set done = false %}
    {% set calendar = task.calendars.0 %}
    {% set performes = calendar.getPerformes() %}
    {% set dateStart = calendar.dateStart|date('Y-m-d', timezone="Europe/Paris") %}
    {% set dateStartReel = calendar.dateCreateCalendar|date('d/m', timezone="Europe/Paris") %}

    {% if calendar.reiterate == 1 %}
        {% set dateStart = "now"|date('Y-m-d', timezone="Europe/Paris") %}
        {% if performes is not null %}
            {% set performe_count = performes|length %}
            {% set performe = performes|last %}
            {% set performe_date = performe.dateUpdate|date('Y-m-d', timezone="Europe/Paris") %}
            {% if performe_date >= "now"|date('Y-m-d', timezone="Europe/Paris") %}
                {% set dateStart = app.setDateNext(dateStart, 'day') %}
            {% endif %}
        {% endif %}
    {% endif %}
    
    {% if calendar.reiterate == 2 %}
        {% set dateStart = app.setDatePrev(dateStart, 'week') %}
        {% if performes is not null %}
            {% set performe_count = performes|length %}
            {% set performe = performes|last %}
            {% set performe_date = performe.dateUpdate|date('Y-m-d', timezone="Europe/Paris") %}
            {% if performe_date >= "now"|date('Y-m-d', timezone="Europe/Paris") %}
                {% set done = true %}
            {% endif %}
        {% endif %}
    {% endif %}
    
    {% if calendar.reiterate == 3 %}
        {% set dateStart = app.setDatePrev(dateStart, 'month') %}
        {% if performes is not null %}
            {% set performe_count = performes|length %}
            {% set performe = performes|last %}
            {% set performe_date = performe.dateUpdate|date('Y-m-d', timezone="Europe/Paris") %}
            {% if performe_date >= "now"|date('Y-m-d', timezone="Europe/Paris") %}
                {% set done = true %}
            {% endif %}
        {% endif %}
    {% endif %}
    
    {% if done or showDone %}
    
        {% if annee != dateStart|date('Y', timezone="Europe/Paris") %}
            {% if annee is not empty %}
                <ul>
            {% endif %}
            <ul>
            {% set annee = dateStart|date('Y', timezone="Europe/Paris") %}
            <li style="list-style-type:none;"><h3>{{ annee }}</h3></li>
        {% endif %}
            
        {% if jourMois != dateStart|date('d/m', timezone="Europe/Paris") %}
            </ul><ul>
            {% set jourMois = dateStart|date('d/m', timezone="Europe/Paris") %}
            <li style="list-style-type:none;"><h4>{{ jourMois }}</h4></li>
        {% endif %}
            
        <ul>
            {% if task.priority == 0 %}<span style="color:#97CEFA;font-weight:bold">Aucune Priorité</span>
            {% elseif task.priority == 1 %}<span style="color:red;font-weight:bold">Priorité 1</span>
            {% elseif task.priority == 2 %}<span style="color:orange;font-weight:bold">Priorité 2</span>
            {% elseif task.priority == 3 %}<span style="color:#E8D630;font-weight:bold">Priorité 3</span>
            {% elseif task.priority == 4 %}<span style="color:wheat;font-weight:bold">Priorité 4</span>
            {% endif %}
            Créé le : {{ dateStartReel }} - 
            {% if calendar.reiterate == 0 %}Unique{% elseif calendar.reiterate == 1 %}Chaque jour{% elseif calendar.reiterate == 2 %}Chaque semaine{% elseif calendar.reiterate == 3 %}Chaque mois{% elseif calendar.reiterate == 4 %}Chaque année{% elseif calendar.reiterate == 5 %}Custom{% endif %}
            <br />
            <li style="list-style-type:none;">
                {% if done == false %}
                    <a href="index.php?page=done&id={{ calendar.id }}"><span title="à faire" class="glyphicon glyphicon-unchecked" style="color:red"></span></a> 
                {% else %}
                    <span title="fait" class="glyphicon glyphicon-check" style="color:green"></span> 
                {% endif %}
                <b>{{ task.name }}</b> <a href="index.php?page=task&id={{ task.id }}"><span title="modifier" class="glyphicon glyphicon-edit"></span></a> <a title="supprimer" href="index.php?page=del&id={{ task.id }}"><span class="glyphicon glyphicon-remove"></span></a>
            </li>
            <br /><br />
        </ul>
        
    {% endif %}
    
{% endfor %}