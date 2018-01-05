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
                width:85%; 
                box-sizing:border-box; 
                padding:10px;
            }
            .menu {
                width:15%; 
                float: left;
            }
            .clearfix {clear:both; padding:0}
            .sep {
                background-color:black;
                padding:0;
                border-width: 10px;
                position: absolute;
                left:15%;
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
            .colorProject {
                float:left;height: 25px;padding: 1px 6px;width: 25px;border: 1px #2e6da4 solid;border-radius: 0 15px 15px 0;
            }
            .colorProject:hover {
                opacity: 0.8;
                cursor: pointer;
            }
            input[type="color"]::-webkit-color-swatch {
                border:none;
            }
            input[type="color"]::-moz-color-swatch {
                border:none;
            }
            .deleteTask:hover {
                opacity: 0.8;
                cursor: pointer;
            }
            @media screen and (max-width: 800px)
            {
                .menu {
                    width:100%;
                    float: none;
                }
                .sep {
                    opacity: 0;
                }
                
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
            <div class="menu" style="height:100%;background-color:#F5F5F5;">
                <div id="display_date" style="border: 1px black dotted;text-align:center;margin-right:20px;margin-bottom: 20px;"></div>
                <ul style="list-style-type:none;">
                    <li><a href="/inbox?filtre=today"><span title="boîte de réception" class="glyphicon glyphicon-calendar"></span> Aujourd'hui</a></li>
                    <li><a href="/task"><span title="ajouter" class="glyphicon glyphicon-plus"></span> Ajouter une tâche</a></li>
                    <li><a href="/inbox"><span title="boîte de réception" class="glyphicon glyphicon-inbox"></span> Boîte de réception</a></li>
                    <li><a href="/calendrier"><span title="calendrier" class="glyphicon glyphicon-calendar"></span> Calendrier</a></li>
                    <li><a href="/day"><span title="day" class="glyphicon glyphicon-calendar"></span> Calendrier/jour</a></li>
                    <li><a href="/logout"><span title="day" class="glyphicon glyphicon-log-out"></span> Déconnexion</a></li>
                </ul>
                <div style="text-align:center"><span><a href="">Projets</a> / Labels / Filtres</span></div>
                <div id="dialogDelete" style="display:none;"></div>
                <div id="links_project">
                    {% for project in projects %}
                        <div id="project___{{ project.id }}">
                            <div style="float:left;width:10%;padding: 0px;">
                                <button value="project___{{ project.id }}" class="btn btn-default btn-hover-danger delete" type="button" style="float:right;height: 25px;padding: 1px 6px;border: 1px #2e6da4 solid;border-radius: 4px 0 0 4px;">x</button>
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
                                    type="button" style="width: 100%;height: 25px;padding: 1px 12px;border-radius: 0px;">{{ project.name }}</button>
                            </div>
                            <div style="float:right;;width:10%;padding: 0px;height: 25px;">
                                <input value="{{ project.color }}" id="color___{{ project.id }}" type="color" class="colorProject" style="background-color: {{ project.color }};" />
                            </div>
                        </div>
                    {% endfor %}
                </div>
                <div class="clearfix" style="padding: 5px;"></div>
                <div id="tchat" style="border:solid 1px black;"></div>
                <div class="clearfix"></div>
                <div style="padding:0;margin:10px;">
                    <input type="text" id="ia" name="ia" style="width: 89%;padding: 0; height:26px; float:left;"/><input type="submit" id="iasubmit" value=">" style="float:left;width:10%;" />
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="sep"></div>
            <div class="content" style="float: left;">
                {{ content|raw }} <br />
            </div>
            <div class="clearfix"></div>
        </div>
         
<script>
    $(document).keypress(function(e) {
        if (e.which === 13 && $('#ia').is(":focus")) {
            ajaxIa($('#ia'));
        } 
    });
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
        $(document).on('change', '.colorProject', function() {
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
                $('#tchat').html(data);
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
                window.location.reload();
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