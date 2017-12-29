<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="library/jquery-ui.css">
        <link rel="stylesheet" href="library/bootstrap/dist/css/bootstrap.css">
        <link rel="stylesheet" href="library/jquery-timepicker/jquery.timepicker.css">
        <link rel="stylesheet" href="library/bootstrap-toggle.min.css">
        <link rel="stylesheet" href="library/spectrum.css" type="text/css" media="screen"/>
        <style>
            div {box-sizing:border-box; padding:10px;}
            .floated {float: left;}
            .box {width: 100%; position:relative}
            .menu, .content {
                width:80%; 
                box-sizing:border-box; 
                padding:10px;
            }
            .menu {width:20%; }
            .clearfix {clear:both;}
            .sep {
                background-color:black;
                padding:0;
                border-width: 10px;
                position: absolute;
                left:20%;
                top:0;
                bottom:0;
                border-left: 1px dotted #ddd;
            }
            .dropdown.open {
                background: #fff;
            }
            .btn-hover-danger:hover {
                background-color: #d9534f;
            }
            #warpper
            {
                background-color: white;
                filter:alpha(opacity=50); /* IE */
                opacity: 0.5; /* Safari, Opera */
                -moz-opacity:0.50; /* FireFox */
                z-index: 20;
                height: 100%;
                width: 100%;
                background-repeat:no-repeat;
                background-position:center;
                position:absolute;
                top: 0px;
                left: 0px;
            }
            .btn-default:hover {
                color: #333;
                background-color: #e6e6e6;
                border-color: #8c8c8c;
            }
        </style>
        <script type="text/javascript" src="library/jquery.js"></script>
        <script type="text/javascript" src="library/jquery-ui.js"></script>
        <script type="text/javascript" src="library/jquery-timepicker/jquery.timepicker.js"></script>
        <script type="text/javascript" src="library/bootstrap-toggle.min.js"></script>
        <script type="text/javascript" src="library/spectrum.js"></script>
    </head>
    <body>
        <div id="warpper" style="display:none"></div>
        <div class="box" style="padding:0;margin:0;">
            <div class="menu" style="height:100%;background-color:#F5F5F5;float: left;">
                <div id="display_date" style="border: 1px black dotted;text-align:center;margin-right:20px;margin-bottom: 20px;">{{ "now"|date('d/m/Y H:i', timezone="Europe/Paris") }}</div>
                <ul style="list-style-type:none;">
                    <li><a href="/inbox?filtre=today"><span title="boîte de réception" class="glyphicon glyphicon-calendar"></span> Aujourd'hui</a></li>
                    <li><a href="/task"><span title="ajouter" class="glyphicon glyphicon-plus"></span> Ajouter une tâche</a></li>
                    <li><a href="/inbox"><span title="boîte de réception" class="glyphicon glyphicon-inbox"></span> Boîte de réception</a></li>
                    <li><a href="/calendrier"><span title="calendrier" class="glyphicon glyphicon-calendar"></span> Calendrier</a></li>
                    <li><a href="/day"><span title="day" class="glyphicon glyphicon-calendar"></span> Calendrier/jour</a></li>
                </ul>
                <div style="text-align:center"><span><a href="">Projets</a> / <a href="">Labels</a> / <a href="">Filtres</a></span></div>
                <div id="dialogDelete" style="display:none;"></div>
                <div id="links_project">
                    {% for project in projects %}
                        <div id="project___{{ project.id }}">
                            <div style="float:left;width:10%;padding: 0px;">
                                <button value="project___{{ project.id }}" class="btn btn-default btn-hover-danger delete" type="button" style="float:right;height: 25px;padding: 1px 6px;">x</button>
                            </div>
                            <div style="float:left;width:80%;padding: 0px;" class="projectselections">
                                <button 
                                    value="{{ project.id }}" 
                                    id="{% if project.id in projectselections and project.id not in projectselectionsdel %}del{% endif %}{{ project.id }}"
                                    {% if project.id in projectselections and project.id not in projectselectionsdel %}
                                        class="btn btn-primary projectselection"
                                    {% else %}
                                        class="btn btn-default projectselection"
                                    {% endif %} 
                                    type="button" style="width: 100%;height: 25px;padding: 1px 12px;">{{ project.name }}</button>
                            </div>
                            <div style="float:right;;width:10%;padding: 0px;height: 25px;">
                                <input value="{{ project.color }}" id="color___{{ project.id }}" type="color" class="color" style="float:left;height: 25px;padding: 1px 6px;width: 40px;border: none;" />
                            </div>
                        </div>
                    {% endfor %}
                </div>
                <footer style="border:solid 1px black;position: absolute;bottom: 0;width: 19%;"><input type="text" id="ia" name="ia" style="width: 95%;padding: 0"/><div id="iasubmit" style="width: 5%;float: right;padding: 0;padding-left: 5px;cursor: pointer;"> ></div></footer>
            </div>
            <div class="sep"></div>
            <div class="content" style="float: left;">
                {{ content|raw }} <br />
            </div>
            <div class="clearfix"></div>
        </div>
         
<script>
    $(function()
    {
        displayDate();
        
        $(document).on('click', '#iasubmit', function() {
            ajaxIa($('#ia'));
        });
        
        // Supprimer un projet
        $(document).on('click', '.delete', function() {
            deleteProject($(this));
        });
        
        // Séléction / Désélection d'un projet
        $(document).on('click', '.projectselection', function() {
            ajaxProjects($(this));
        });
        
        // Changer la colour d'un projet
        $(document).on('change', '.color', function() {
            ajaxProjectsColor($(this));
        });
    });
    
    function deleteProject(button)
    {
        var infos = button.val().split('___');
        
        $('#dialogDelete').attr('title', 'Gestion du projet : ' + infos[1]);
        $('#dialogDelete').html('<ul><li style="color:red;">La suppression du projet est définitive, les tâches et vos actions associées seront également supprimées.</li>\n\
        <li>La désactivation d\'un projet permet de ne plus le voir dans la liste des projets. Pour ré-activer un projet : selectionnez une tâche du projet dans le calendrier.</li>')
        
        $('#dialogDelete').dialog({
            resizable: false,
            draggable: false,
            height: "auto",
            width: 400,
            position: { my: "center top", at: "center top+60", of: window},
            modal: true,
            buttons : {
                "Désactiver": function() {
                    ajaxProjectsActivation(button);
                    $(this).dialog('close');
                },
                "Supprimer": function() {
                    $(this).dialog('close');

                    // redirection
                },
                "Annuler": function() {
                    $(this).dialog('close');
                }
            }
        });
    }

    function displayDate()
    {
        date = new Date;
        $('#display_date').html(date.getDay() + '/' + date.getMonth() + '/' + date.getFullYear() + ' ' + date.getHours() + ':' + (date.getMinutes() < 10?'0' : '') + date.getMinutes());
        setTimeout('displayDate();', '60000');
    }
    
    //----------------------------------AJAX------------------------------------
    
    // IA
    function ajaxIa(button)
    {
        $.ajax({
            beforeSend	: function () {
                $('#warpper').show();
            },
            url		: 'http://projetdetest.dev.s2h.corp/ia',
            async	: true,
            dataType 	: 'json',
            data 		: {
                'phrase': button.val()
            },
            success 	: function (data)
            {
                $('.content').html(data);
                $('#warpper').hide();
            },
            error		: function ( xml, status, e ) {
                alert(e);
                $('#warpper').hide();
            }
        });
    }
    // activer/desactiver un projet
    function ajaxProjectsActivation(button)
    {
        var infos = button.val().split('___');
        
        $.ajax({
            beforeSend	: function () {
                $('#warpper').show();
            },
            url		: 'http://projetdetest.dev.s2h.corp/projectsactivation',
            async	: true,
            dataType 	: 'json',
            data 		: {
                'id': infos[1]
            },
            success 	: function () {
                $('#' + button.val()).hide();
                ajaxContent();
            },
            error		: function ( xml, status, e ) {
                alert(e);
                $('#warpper').hide();
            }
        });
    }
    
    // Changer la coulour d'un projet
    function ajaxProjectsColor(button)
    {
        var infos = button.attr('id').split('___');
        
        $.ajax({
            beforeSend	: function () {
                $('#warpper').show();
            },
            url		: 'http://projetdetest.dev.s2h.corp/projectscolor',
            async		: true,
            dataType 	: 'json',
            data 		: {
                'color': button.val(),
                'id': infos[1]
            },
            success 	: function () {
                ajaxContent();
            },
            error		: function ( xml, status, e ) {
                alert(e);
                $('#warpper').hide();
            }
        });
    }
    
    // Selection / Deselection d'un projet
    function ajaxProjects(button)
    {
        var filtre = 'project';
        if (button.attr('id').substring(0, 3) === 'del') {
            filtre = 'projectdel';
        }
        
        $.ajax({
            beforeSend	: function () {
                $('#warpper').show();
            },
            url		: 'http://projetdetest.dev.s2h.corp/projects',
            async		: true,
            dataType 	: 'json',
            data 		: {
                'filtre': filtre,
                'id': button.val()
            },
            success 	: function ()
            {
                if (filtre === 'projectdel')
                {
                    button.attr('id', button.attr('id').substr(3));
                    button.attr('class', 'btn btn-default projectselection');
                }
                else
                {
                    button.attr('id', 'del' + button.attr('id'));
                    button.attr('class', 'btn btn-primary projectselection');
                }
                button.blur();
                ajaxContent();
            },
            error		: function ( xml, status, e ) {
                alert(e);
                $('#warpper').hide();
            }
        });
    }
    
    // Recharger le contenu (quelque soit la page
    function ajaxContent()
    {
        $.ajax({
            beforeSend	: function () {
                $('#warpper').show();
            },
            url		: window.location.href,
            async		: true,
            dataType 	: 'json',
            success 	: function (data) {
                $('.content').html(data);
                $('#warpper').hide();
            },
            error		: function ( xml, status, e ) {
                alert(e);
                $('#warpper').hide();
            }
        });
    }
</script>
    </body>
</html>