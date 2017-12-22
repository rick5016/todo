<table style="width:100%;">
    <tr>
        <td colspan="7" align="center">
            <a href="?page=cal&amp;mois={{ num_mois-1 }}&amp;annee={{ num_an }}"><<</a>
            &nbsp;&nbsp;{{ tab_mois[num_mois] }}&nbsp;&nbsp;
            <a href="?page=cal&amp;mois={{ num_mois+1 }}&amp;annee={{ num_an }}">>></a>
        </td>
    </tr>
    <tr>
        <td colspan="7" align="center">
            <a href="calendrier.php?mois=<?php echo $num_mois; ?>&amp;annee=<?php echo $num_an-1; ?>"><<</a>
            &nbsp;&nbsp;{{ num_an }}&nbsp;&nbsp;
            <a href="calendrier.php?mois=<?php echo $num_mois; ?>&amp;annee=<?php echo $num_an+1; ?>">>></a>
        </td>
    </tr>
    <tr>
    {% for i in 1..7 %}
        <td>{{ tab_jours[i] }}</td>
    {% endfor %}
    </tr>

    {% for i in 0..5 %}
        <tr>
        {% for j in 0..6 %}
            <td style="border:solid 1px black;height:200px;" {% if num_mois == "now"|date('m', timezone="Europe/Paris") and num_an == "now"|date('Y', timezone="Europe/Paris") and tab_cal[i][j] == "now"|date('d', timezone="Europe/Paris")%} style="color: #FFFFFF; background-color: #000000;"{% endif %}>
                {% if '*' in tab_cal[i][j][0] %}
                    <font color="#aaaaaa">
                        {{ tab_cal[i][j][0] }}
                        {% for task in tab_cal[i][j] %}
                            {% if loop.index != 1 %}
                                <div {% if task[1] == 1 %}class="alert alert-success" {% elseif task[1] == 2 %}class="alert alert-info" {% else %}class="alert alert-danger" {% endif %}style="padding: 5px; margin: 5px">
                                    {{ task.0.name }}({{ task.0.id }})
                                </div>
                            {% endif %}
                        {% endfor %}
                    </font>
                {% else %}
                    {{ tab_cal[i][j][0] }}
                    {% for task in tab_cal[i][j] %}
                        {% if loop.index != 1 %}
                            <div {% if task[1] == 1 %}class="alert alert-success" {% elseif task[1] == 2 %}class="alert alert-info" {% else %}class="alert alert-danger" {% endif %}style="padding: 5px; margin: 5px">
                                {{ task.0.name }}({{ task.0.id }})
                            </div>
                        {% endif %}
                    {% endfor %}
                {% endif %}
            </td>
        {% endfor %}
        </tr>
    {% endfor %}
</table>