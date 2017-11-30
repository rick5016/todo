{% if valide is defined %}
    <h2 style="color: green;font-weight: bold;">{{ valide }}</h2>
{% endif %}

{% if form.hasErrors() %}
    <ul>
        {% for error in form.getErrors() %}
            <li><span style="color: red;font-weight: bold;">{{ error }}</span></li>
        {% endfor %}
    </ul>
{% endif %}
<h3>Ajouter une tâche</h3>
<form method="post">
    <input name="id" type="hidden" value="{{ task_id }}" />
    {{ form.get('task_name')|raw }}
    {{ form.get('project_id')|raw }}
    {{ form.get('priority')|raw }}
    <div>
        <span title="date" class="glyphicon glyphicon-time" style="margin-right:10px;"></span>
        Toute la journée <input type="checkbox" id="allDay" name="allDay" value="1" /><br />
        <div style="float:left;width:100px;">Début </div>
        
        <div style="float:left;">{{ form.get('dateStart').get()|raw }} </div>
        <div style="float:left;">{{ form.get('timeStart').get()|raw }}</div>
        <div style="clear:both;padding: 0;"></div>
        <div style="float:left;width:100px;">Fin </div>
        <div style="float:left;">{{ form.get('dateEnd').get()|raw }} </div>
        <div style="float:left;">{{ form.get('timeEnd').get()|raw }}</div>
        <div style="clear:both;padding: 0;"></div>
    </div>
    <div>
        {{ form.get('repeat').getHTMLGlyphicon()|raw }}
        {{ form.get('repeat').get()|raw }}
        <div style="clear:both;padding: 0;"></div>
        <div class="custom" style="float:left;display:none;border:solid 1px black;margin-top: 15px;">
            {{ form.get('reiterateEnd').get()|raw }}
            
            <div id="customUntilDate" style="display:none;">
                {{ form.get('untilDate').get()|raw }}<br />
            </div>
            <div id="customUntilNumber" style="display:none;">
                {{ form.get('untilNumber').get()|raw }} fois<br />
            </div>
            
            <div style="clear:both;padding: 0;"></div>
            <div style="float:left;width:100px;">Tous les </div>
            <div style="float:left;">{{ form.get('interspace').get()|raw }} <span id="interspaceText"></span></div>
            <div style="clear:both;padding: 0;"></div>
        </div>
        <div style="clear:both;padding: 0;"></div>
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