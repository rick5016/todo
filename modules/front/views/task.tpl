<h3>Ajouter une tâche</h3>
<form method="post">
    <div>
        <span title="Titre" class="glyphicon glyphicon-tasks" style="margin-right:10px;"></span>
        <input type="text" name="name" value="{{ name }}"/>
    </div>
    <div>
        <span title="projet" class="glyphicon glyphicon-calendar" style="margin-right:10px;"></span>
        Choix du projet<br />
    </div>
    <div>
        <span title="priorité" class="glyphicon glyphicon-chevron-up" style="margin-right:10px;"></span>
        <input type="radio" {% if priority == 1 %}checked="checked" {% endif %}name="priority" value="1" /><span style="color:red;font-weight:bold">1</span>
        <input type="radio" {% if priority == 2 %}checked="checked" {% endif %}name="priority" value="2" /><span style="color:orange;font-weight:bold">2</span>
        <input type="radio" {% if priority == 3 %}checked="checked" {% endif %}name="priority" value="3" /><span style="color:#E8D630;font-weight:bold">3</span>
        <input type="radio" {% if priority == 4 %}checked="checked" {% endif %}name="priority" value="4" /><span style="color:wheat;font-weight:bold">4</span>
        <input type="radio" {% if priority == 0 %}checked="checked" {% endif %}name="priority" value="0" /><span style="color:97CEFA;font-weight:bold">0</span>
    </div>
    <div>
        <span title="date" class="glyphicon glyphicon-time" style="margin-right:10px;"></span>
        Toute la journée <input type="checkbox" id="allDay" name="allDay" value="1" /><br />
        <div style="float:left;width:100px;">Début </div>
        <div style="float:left;"><input type="text" name="dateStart" id="dateStart" value="{% if dateStart is not empty %}{{ dateStart }}{% else %}{{ "now"|date('d/m/Y', timezone="Europe/Paris") }}{% endif %}" /> </div>
        <div style="float:left;"><input type="text" size="2" name="timeStart" id="timeStart" value="{% if timeStart is not empty %}{{ timeStart|date('H:i', timezone="Europe/Paris") }}{% endif %}"/></div>
        <div style="clear:both;padding: 0;"></div>
        <div style="float:left;width:100px;">Fin </div>
        <div style="float:left;"><input type="text" name="dateEnd" id="dateEnd" value="{% if dateEnd is not empty %}{{ dateEnd }}{% else %}{{ "now"|date('d/m/Y', timezone="Europe/Paris") }}{% endif %}" /> </div>
        <div style="float:left;"><input type="text" size="2" name="timeEnd" id="timeEnd" value="{% if timeEnd is not empty %}{{ timeEnd|date('H:i', timezone="Europe/Paris") }}{% endif %}" /></div>
        <div style="clear:both;padding: 0;"></div>
    </div>
    <div>
        <span title="boucle" class="glyphicon glyphicon-repeat" style="margin-right:10px;"></span>
        <div style="clear:both;padding: 0;"></div>
        <div style="float:left;">
            <select id="repeat" name="repeat">
                <option value="0"{% if calendar is null or repeat == 0 %} selected="selected"{% endif %}>Once</option>
                <option value="1"{% if repeat == 1 %} selected="selected"{% endif %}>Days</option>
                <option value="2"{% if repeat == 2 %} selected="selected"{% endif %}>Weeks</option>
                <option value="3"{% if repeat == 3 %} selected="selected"{% endif %}>Month</option>
                <option value="4"{% if repeat == 4 %} selected="selected"{% endif %}>Years</option>
            </select>
        </div>
        <div class="custom" style="float:left;display:none;border:solid 1px black;">
            <select id="reiterateEnd" name="reiterateEnd">
                <option value="0"{% if calendar is null or reiterateEnd == 0 %} selected="selected"{% endif %}>Toujours</option>
                <option value="1"{% if reiterateEnd == 1 %} selected="selected"{% endif %}>Jusqu'à une certains date</option>
                <option value="2"{% if reiterateEnd == 2 %} selected="selected"{% endif %}>Jusqu'à un nombre de fois</option>
            </select>
            
            <div id="customUntilDate" style="display:none;">
                <input type="text" id="untilDate" name="untilDate" value="{% if untilDate is not empty %}{{ untilDate|date('d/m/Y', timezone="Europe/Paris") }}{% endif %}" /><br />
            </div>
            <div id="customUntilNumber" style="display:none;">
                <input size="2" type="text" name="untilNumber" value="{{ untilNumber }}" size="2" /> fois<br /><br />
            </div>
            
            <div style="clear:both;padding: 0;"></div>
            <div style="float:left;width:100px;">Tous les </div>
            <div style="float:left;"><input size="1" type="text" name="interspace" value="{% if interspace == '' or interspace == '0' %}1{% else %}{{ interspace }}{% endif %}" /> <span id="interspaceText"></span></div>
            <div style="clear:both;padding: 0;"></div>
        </div>
        <div style="clear:both;padding: 0;"></div>

        
    <!--
    <div id="customWeeks" style="display:none;">
        Jours (select multiple) <select name="repeatCustomWeeks">
            <option value="0">Lundi</option>
            <option value="1">Mardi</option>
            <option value="2">Mercredi</option>
            <option value="3">Jeudi</option>
            <option value="4">Vendredi</option>
            <option value="5">Samedi</option>
            <option value="6">Dimanche</option>
        </select><br /><br />
    </div>
    <div id="customMonths" style="display:none;">
        <input type="radio" name="repeatCustomMonths" value="0" /> le même jour chaque mois<br />
        <input type="radio" name="repeatCustomMonths" value="1" /> tout les deuxièmes vendredi (j'ai cliqué sur le second vendredi du mois)<br /><br />
    </div>
    -->
        
        
        
    </div>
    <div>
        <span title="lieu" class="glyphicon glyphicon-map-marker" style="margin-right:10px;"></span>
        Lieu
    </div>
    <div>
        <span title="rappel" class="glyphicon glyphicon-bell" style="margin-right:10px;"></span>
        Rappel
    </div>
    <div>
        <span title="guest" class="glyphicon glyphicon-user" style="margin-right:10px;"></span>
        Invités
    </div>
    <div>
        <span title="color" class="glyphicon glyphicon-text-background" style="margin-right:10px;"></span>
        Couleur
    </div>
    <div>
        <span title="note" class="glyphicon glyphicon-pencil" style="margin-right:10px;"></span>
        Note
    </div>
    <div>
        <span title="file" class="glyphicon glyphicon-file" style="margin-right:10px;"></span>
        Pièce jointe
    </div>
    <div>
        <input type="submit" name="submit" value="Submit" />
    </div>
</form>
<script type="text/javascript">
    $(function () {
        $('#timeStart, #timeEnd').timepicker({'timeFormat': 'H:i'});
        $('#dateStart, #dateEnd, #untilDate').datepicker({'dateFormat': 'dd/mm/yy'});

        function repeat(repeat)
        {
            if (repeat !== '0') { // custom
                $(".custom").show();
                $("#interspaceText").html("jours");
                repeatCustom(repeat);
            } else {
                $(".custom").hide();
            }
        }
        function repeatCustom(repeat)
        {
            if (repeat === '1') {
                $("#interspaceText").html("jour(s)");
            } else if (repeat === '2') {
                $("#customWeeks").show();
                $("#interspaceText").html("semaine(s)");
            } else if (repeat === '3') {
                $("#customMonths").show();
                $("#interspaceText").html("mois");
            } else if (repeat === '4') {
                $("#interspaceText").html("année(s)");
            }
        }
        function until(val, repeat)
        {
            if (repeat !== '0')
            {
                $("#customUntilDate").hide();
                $("#customUntilNumber").hide();
                if (val === '1') {
                    $("#customUntilDate").show();
                } else if (val === '2') {
                    $("#customUntilNumber").show();
                }
            }
        }
        function allDay(checked)
        {
            if (checked) {
                $('#timeStart, #timeEnd').hide();
            } else {
                $('#timeStart, #timeEnd').show();
            }
        }
        //--------------------------------------------------------------
        repeat($("#repeat").val());
        $("#repeat").change(function () {
            repeat($(this).val());
        });
        //--------------------------------------------------------------
        until($("#reiterateEnd").val(), $("#repeat").val());
        $("#reiterateEnd").change(function () {
            until($(this).val(), $("#repeat").val());
        });
        //--------------------------------------------------------------
        allDay($("#allDay").is(':checked'));
        $("#allDay").click(function () {
            allDay($(this).is(':checked'));
        });
        //-------------------------------------------------------------
    });
</script>