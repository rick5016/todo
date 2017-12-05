{% set jourMois = '' %}
{% set today = true %}
<div>
    <a href="/inbox?priority={% if priority|slice(0, 1) == '1' %}{{ '0' ~ priority|slice(1, 4)}}{% else %}{{ '1' ~ priority|slice(1, 4)}}{% endif %}">
        <span{% if priority|slice(0, 1) == '1' %} class="btn btn-primary"{% else %} class="btn btn-default"{% endif %}>Défaut</span>
    </a>
    <a href="/inbox?priority={% if priority|slice(1, 1) == '1' %}{{ priority|slice(0, 1) ~ '0' ~ priority|slice(2, 3)}}{% else %}{{ priority|slice(0, 1) ~ '1' ~ priority|slice(2, 3)}}{% endif %}">
        <span{% if priority|slice(1, 1) == '1' %} class="btn btn-danger"{% else %} class="btn btn-default"{% endif %}>Priorité 1</span>
    </a>
    <a href="/inbox?priority={% if priority|slice(2, 1) == '1' %}{{ priority|slice(0, 2) ~ '0' ~ priority|slice(3, 2)}}{% else %}{{ priority|slice(0, 2) ~ '1' ~ priority|slice(3, 2)}}{% endif %}">
        <span{% if priority|slice(2, 1) == '1' %} class="btn btn-warning"{% else %} class="btn btn-default"{% endif %}>Priorité 2</span>
    </a>
    <a href="/inbox?priority={% if priority|slice(3, 1) == '1' %}{{ priority|slice(0, 3) ~ '0' ~ priority|slice(4, 1)}}{% else %}{{ priority|slice(0, 3) ~ '1' ~ priority|slice(4, 1)}}{% endif %}">
        <span{% if priority|slice(3, 1) == '1' %} class="btn btn-primary" style="background-color:#e8dc00;border-color:#e8dc00"{% else %} class="btn btn-default"{% endif %}>Priorité 3</span>
    </a>
</div>
    <script>
      $(function() {
        $('#past').change(function() {
          $("#filtrer").submit();
        })
        $('#ant').change(function() {
          $("#filtrer").submit();
        })
        $('#details').change(function() {
          $("#filtrer").submit();
        })
      })
    </script>
    <form method="post" id="filtrer">
        {% if filtre_today is not defined %}
            <div>
                <label class="checkbox-inline" value="">
                    {{ form.get('past').get()|raw }} Tâches non accomplies dans le passé
                </label>
            </div>
            <div>
                <label class="checkbox-inline" value="">
                    {{ form.get('ant').get()|raw }} Prochaines Tâches
                </label>
            </div>
        {% endif %}
        <div>
            <label class="checkbox-inline" value="">
                {{ form.get('details').get()|raw }} Afficher les détails
            </label>
        </div>
        <input type="hidden" value="Filtrer" name="filtrer" />
    </form>
<hr style="border-bottom: 1px solid #F5F5F5;" />
{% if projects is empty %}
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

    {% set performes = task.getPerformes() %}
    {% set dateStart = task.dateAffichage|date('Y-m-d', timezone="Europe/Paris") %}
    
    <!-- Gere la ligne entre les tâches anterieures à la date du jour et les autres -->
    {% if dateStart > "now"|date('Y-m-d', timezone="Europe/Paris") and today %}
        <!--<hr style="border-bottom: 1px solid #F5F5F5;" />-->
        {% set today = false %}
    {% endif %}

    <!-- Gestion de l'affichage du mois et du jour -->
    {% if jourMois != dateStart|date('d/m', timezone="Europe/Paris") %}
        </ul><ul>
        {% if dateStart|date('d/m/Y', timezone="Europe/Paris") == "now"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Aujourd'hui" %}
        {% elseif dateStart|date('d/m/Y', timezone="Europe/Paris") == "tomorrow"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Demain" %}
        {% elseif dateStart|date('d/m/Y', timezone="Europe/Paris") == "yesterday"|date('d/m/Y', timezone="Europe/Paris") %}
            {% set display = "Hier" %}
        {% else %}
            {% set display = "" %}
        {% endif %}
        {% set jourMois = dateStart|date('d/m', timezone="Europe/Paris") %}
        <li style="list-style-type:none;"><span style="font-size: 25px;font-weight: 500;line-height: 1.1;margin-bottom: 5px;">{{ display }}</span> <span>{{ dateStart|date('d/m/Y', timezone="Europe/Paris") }}</span></li>
        <br />
    {% endif %}

    {% if task.priority == 0 %}
        {% set backgroundColor = "#e7e6ff" %}
        {% set priorityColor = "#c0c0c0" %}
    {% elseif task.priority == 1 %}
        {% set backgroundColor = "#fff0f0" %}
        {% set priorityColor = "#d9534f" %}
    {% elseif task.priority == 2 %}
        {% set backgroundColor = "#fef4e7" %}
        {% set priorityColor = "#f0ad4e" %}
    {% elseif task.priority == 3 %}
        {% set backgroundColor = "#fefde7" %}
        {% set priorityColor = "#e8dc00" %}
    {% endif %}
    
    {% set padding = "5" %}
    {% if today == false and performes|length > 0 %}
        {% set padding = "10" %}
    {% endif %}
    
    <ul>
        <div title="Priorité {{ task.priority }}" style="float:right;background:{{ priorityColor }};margin-right: 13px;margin-top: 13px;border-radius:50%;width:15px;height:15px;"></div>
        <div style="float:right;margin-top: 4px;">{{ task.nbPerforme }} fois</div>
        <li style="list-style-type:none;background-color: {{ backgroundColor }};height: 46px;border-bottom: 1px solid #ddd;padding:{{ padding }}px;">
            
            {% if today and task.dateStart|date('Y-m-d H:i', timezone="Europe/Paris") < "now"|date('Y-m-d H:i', timezone="Europe/Paris") %} <!-- Aujourd'hui : Lien glyphicon valider -->
                <div style="float:left;height:100%;padding:0 7px 0 0;">
                    <a title="valider" href="/done?id={{ task.id }}">
                        <span title="valider" class="glyphicon glyphicon-ok-circle" style="font-size: 35px;vertical-align:-38%;"></span>
                        <span title="valider" >
                            <b>{{ task.name }}</b> 
                        </span>
                    </a>
                </div>
                
            {% else %}
            
                {% if today == false and performes|length > 0 %} <!-- Demain : lien glyphicon annuler -->
                    {% set performe = performes.0 %}
                    <div style="float:left;height:100%;padding:0 7px 0 0;">
                        <a href="/cancel?id={{ task.id }}&idPerforme={{ performe.id }}">
                            <span title="Annuler" class="glyphicon glyphicon-remove-circle" style="font-size: 25px;vertical-align:-22%;"></span>
                        </a>
                        <span style="color:#8f8f8f">
                            <b>{{ task.name }}</b> 
                        </span>
                    </div>
                {% else %} <!-- Aujourd'hui (mais pas encore l'heure) : aucun glyphicon -->
                <div style="float:left;height:100%;width:38px;"></div>
                <div style="float:left;height:100%;padding-top: 6px;padding-left: 0;">
                    <span style="color:#8f8f8f">
                        <b>{{ task.name }}</b> 
                    </span>
                </div>
                {% endif %}
                
            {% endif %}
                
            <div style="float:left;height:100%;padding-top: 6px;padding-left: 0;">
                <a href="/task?id={{ task.id }}"><span title="modifier" class="glyphicon glyphicon-edit"></span></a> <a title="supprimer" href="/delete?id={{ task.id }}"><span class="glyphicon glyphicon-remove"></span></a>
            </div>
                
            {% if details is defined %}
                <div style="float:left;height:100%;padding-top: 5px;padding-left: 0;">
                    {% if task.reiterate == 1 %} - Tous les {{ task.interspace }} jour(s){% elseif task.reiterate == 2 %} - Toutes les {{ task.interspace }} semaine(s){% elseif task.reiterate == 3 %} - Tous les {{ task.interspace }} mois{% elseif task.reiterate == 4 %} - Toutes les {{ task.interspace }} année(s){% endif %}
                </div>
            {% else %}
                {% if task.dateStart|date('d/m/Y', timezone="Europe/Paris") != task.dateEnd|date('d/m/Y', timezone="Europe/Paris") %}
                    <div style="float:left;height:100%;padding-top: 5px;padding-left: 0;">
                        Jusqu'au {{ task.dateEnd|date('d/m/Y', timezone="Europe/Paris") }}
                    </div>
                {% endif %}
            {% endif %}
            
            <div style="float:left;height:100%;padding-top: 5px;padding-left: 0;">
                {% if task.dateStart|date('d/m/Y', timezone="Europe/Paris") != task.dateEnd|date('d/m/Y', timezone="Europe/Paris") %}
                    {% if details is defined %}
                         du {{ task.dateStart|date('d/m/Y', timezone="Europe/Paris") }}
                        {% if task.dateStart|date('H:i', timezone="Europe/Paris") != task.dateStart|date('H:i', timezone="Europe/Paris") %} {{ task.dateEnd|date('H:i', timezone="Europe/Paris") }} {% endif %}
                         au {{ task.dateEnd|date('d/m/Y', timezone="Europe/Paris") }}
                        {% if task.dateEnd|date('H:i', timezone="Europe/Paris") != task.dateEnd|date('H:i', timezone="Europe/Paris") %} {{ task.dateEnd|date('H:i', timezone="Europe/Paris") }} {% endif %}
                    {% endif %}
                {% else %}
                     {% if task.dateStart|date('H:i', timezone="Europe/Paris") != "00:00" or task.dateEnd|date('H:i', timezone="Europe/Paris") != "00:00" %}
                        {% if task.dateStart|date('H:i', timezone="Europe/Paris") == "00:00" and task.dateEnd|date('H:i', timezone="Europe/Paris") == "11:59" %}
                             le matin
                        {% elseif task.dateStart|date('H:i', timezone="Europe/Paris") == "12:00" and (task.dateEnd|date('H:i', timezone="Europe/Paris") == "17:59" or task.dateEnd|date('H:i', timezone="Europe/Paris") == "23:59") %}
                             l'après-midi
                        {% elseif task.dateStart|date('H:i', timezone="Europe/Paris") == "18:00" and task.dateEnd|date('H:i', timezone="Europe/Paris") == "23:59" %}
                             le soir
                        {% else %}
                             De {{ task.dateStart|date('H:i', timezone="Europe/Paris") }} à  {{ task.dateEnd|date('H:i', timezone="Europe/Paris") }}
                        {% endif %}
                     {% endif %}
                {% endif %}
            </div>
        </li>
        <div style="clear:both;padding: 0;"></div>
    </ul>
        
    
{% endfor %}