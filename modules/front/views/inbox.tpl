{% set annee = '' %}
{% set jourMois = '' %}
{% set displayHr = true %}
<div>
    <a href="?page=inbox&priority={% if priority|slice(0, 1) == '1' %}{{ '0' ~ priority|slice(1, 4)}}{% else %}{{ '1' ~ priority|slice(1, 4)}}{% endif %}">
        <span class="btn btn-default"{% if priority|slice(0, 1) == '1' %} style="border:solid 1px black;"{% endif %}>Défaut</span>
    </a>
    <a href="?page=inbox&priority={% if priority|slice(1, 1) == '1' %}{{ priority|slice(0, 1) ~ '0' ~ priority|slice(2, 3)}}{% else %}{{ priority|slice(0, 1) ~ '1' ~ priority|slice(2, 3)}}{% endif %}">
        <span{% if priority|slice(1, 1) == '1' %} class="btn btn-danger"{% else %} class="btn btn-default"{% endif %}>Priorité 1</span>
    </a>
    <a href="?page=inbox&priority={% if priority|slice(2, 1) == '1' %}{{ priority|slice(0, 2) ~ '0' ~ priority|slice(3, 2)}}{% else %}{{ priority|slice(0, 2) ~ '1' ~ priority|slice(3, 2)}}{% endif %}">
        <span{% if priority|slice(2, 1) == '1' %} class="btn btn-warning"{% else %} class="btn btn-default"{% endif %}>Priorité 2</span>
    </a>
    <a href="?page=inbox&priority={% if priority|slice(3, 1) == '1' %}{{ priority|slice(0, 3) ~ '0' ~ priority|slice(4, 1)}}{% else %}{{ priority|slice(0, 3) ~ '1' ~ priority|slice(4, 1)}}{% endif %}">
        <span{% if priority|slice(3, 1) == '1' %} class="btn btn-primary"{% else %} class="btn btn-default"{% endif %}>Priorité 3</span>
    </a>
    <a href="?page=inbox&priority={% if priority|slice(4, 1) == '1' %}{{ priority|slice(0, 4) ~ '0'}}{% else %}{{ priority|slice(0, 4) ~ '1'}}{% endif %}">
        <span{% if priority|slice(4, 1) == '1' %} class="btn btn-info"{% else %} class="btn btn-default"{% endif %}>Priorité 4</span>
    </a>
</div>
{% if today is not defined %}
    <script>
      $(function() {
        $('#past').change(function() {
          $("#filtrer").submit();
        })
        $('#ant').change(function() {
          $("#filtrer").submit();
        })
      })
    </script>
    <form method="post" id="filtrer">
        <div>
            <label class="checkbox-inline" value="">
                <input alt="test" data-toggle="toggle" {% if past is defined %}checked{% endif %} type="checkbox" name="past" id="past" /> Tâches non accomplies dans le passé
            </label>
        </div>
        <div>
        <label class="checkbox-inline">
            <input data-toggle="toggle" {% if end is defined %}checked{% endif %} type="checkbox" name="end" id="end" disabled /> Tâche(s) Finie(s) aujourd'hui
        </label>
        <div>
        </div>
        <label class="checkbox-inline">
            <input data-toggle="toggle" {% if ant is defined %}checked{% endif %} type="checkbox" name="ant" id="ant" /> Prochaines Tâches
        </label>
        </div>
        <input type="hidden" value="Filtrer" name="filtrer" />
    </form>
{% endif %}
<hr />
{% if tasks is empty %}
<ul>
    <li style="list-style-type:none;"><h3>{{ "now"|date('Y', timezone="Europe/Paris") }}</h3></li>
    <ul>
    <li style="list-style-type:none;"><h4>{{ "now"|date('d/m', timezone="Europe/Paris") ~ " - Aujourd'hui" }}</h4></li>
        <ul>
            <li style="list-style-type:none;">Aucun résultat</li>
        </ul>
    </ul>
</ul>
{% endif %}
{% for task in tasks %}

    {% set calendar = task.calendars.0 %}
    {% set dateStart = calendar.dateAffichage|date('Y-m-d', timezone="Europe/Paris") %}
    
    <!-- Gere la ligne entre les tâches anterieures à la date du jour et les autres -->
    {% if dateStart > "now"|date('Y-m-d', timezone="Europe/Paris") and displayHr %}
        <hr />
        {% set displayHr = false %}
    {% endif %}

    <!-- Gestion de l'affichage du mois et du jour -->
    {% if jourMois != dateStart|date('d/m', timezone="Europe/Paris") %}
        </ul><ul>
        {% if dateStart|date('d/m/Y', timezone="Europe/Paris") == "now"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Aujourd'hui" %}
        {% elseif dateStart|date('d/m/Y', timezone="Europe/Paris") == "tomorrow"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Demain" %}
        {% else %}
            {% set display = "" %}
        {% endif %}
        {% set jourMois = dateStart|date('d/m', timezone="Europe/Paris") %}
        <li style="list-style-type:none;"><span style="font-size: 18px;font-weight: 500;line-height: 1.1;">{{ display }}</span> <span>{{ dateStart|date('d/m/Y', timezone="Europe/Paris") }}</span></li>
    {% endif %}

    <ul>
        Créé le : {{ calendar.created|date('d/m', timezone="Europe/Paris") }} - Du {{ calendar.dateStart|date('d/m/Y', timezone="Europe/Paris") }} au - {{ calendar.dateEnd|date('d/m/Y', timezone="Europe/Paris") }}
        {% if calendar.reiterate == 0 %}Unique{% elseif calendar.reiterate == 1 %}Tous les {{ calendar.interspace }} jour(s){% elseif calendar.reiterate == 2 %}Toutes les {{ calendar.interspace }} semaine(s){% elseif calendar.reiterate == 3 %}Tous les {{ calendar.interspace }} mois{% elseif calendar.reiterate == 4 %}Toutes les {{ calendar.interspace }} année(s){% elseif calendar.reiterate == 5 %}Custom{% endif %}
        <br />
        <li style="list-style-type:none;">
            {% if displayHr %}
            <a href="index.php?page=done&id={{ calendar.id }}">
                {% if task.priority == 0 %}<span title="Aucune Priorité" class="glyphicon glyphicon-ok-circle" style="color:#c0c0c0;font-size: 35;vertical-align:middle;"></span>
                {% elseif task.priority == 1 %}<span title="Priorité 1" class="glyphicon glyphicon-ok-circle" style="color:#d9534f;font-size: 35;vertical-align:middle;"></span>
                {% elseif task.priority == 2 %}<span title="Priorité 2" class="glyphicon glyphicon-ok-circle" style="color:#f0ad4e;font-size: 35;vertical-align:middle;"></span>
                {% elseif task.priority == 3 %}<span title="Priorité 3" class="glyphicon glyphicon-ok-circle" style="color:#337ab7;font-size: 35;vertical-align:middle;"></span>
                {% elseif task.priority == 4 %}<span title="Priorité 4" class="glyphicon glyphicon-ok-circle" style="color:#5bc0de;font-size: 35;vertical-align:middle;"></span>
                {% endif %}
                <b>{{ task.name }}</b> <a href="index.php?page=task&id={{ task.id }}"><span title="modifier" class="glyphicon glyphicon-edit"></span></a> <a title="supprimer" href="index.php?page=del&id={{ task.id }}"><span class="glyphicon glyphicon-remove"></span></a>
            </a> 
            {% else %}
                <b>{{ task.name }}</b> <a href="index.php?page=task&id={{ task.id }}"><span title="modifier" class="glyphicon glyphicon-edit"></span></a> <a title="supprimer" href="index.php?page=del&id={{ task.id }}"><span class="glyphicon glyphicon-remove"></span></a>
            {% endif %}
        </li>
        <br /><br />
    </ul>
        
    
{% endfor %}