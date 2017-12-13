<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="library/bootstrap/dist/css/bootstrap.css">
        <link rel="stylesheet" href="library/jquery-timepicker/jquery.timepicker.css">
        <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
        <style>
            div {box-sizing:border-box; padding:10px;}
            .floated {float: left;}
            .box {width: 100%; position:relative}
            .menu, .content {
                width:60%; 
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
        </style>
        <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="library/jquery-timepicker/jquery.timepicker.js"></script>
        <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    </head>
    <body>
        <div class="box" style="padding:0;margin:0;">
            <div class="floated menu" style="height:100%;background-color:#F5F5F5">
                <div id="display_date" style="border: 1px black dotted;text-align:center;margin-right:20px;margin-bottom: 20px;">{{ "now"|date('d/m/Y H:i', timezone="Europe/Paris") }}</div>
                <ul style="list-style-type:none;">
                    <li><a href="/inbox?filtre=today"><span title="boîte de réception" class="glyphicon glyphicon-calendar"></span> Aujourd'hui</a></li>
                    <li><a href="/task"><span title="ajouter" class="glyphicon glyphicon-plus"></span> Ajouter une tâche</a></li>
                    <li><a href="/inbox"><span title="boîte de réception" class="glyphicon glyphicon-inbox"></span> Boîte de réception</a></li>
                    <li><a href="/calendrier"><span title="calendrier" class="glyphicon glyphicon-calendar"></span> Calendrier</a></li>
                </ul>
                <div style="text-align:center"><span><a href="">Projets</a> / <a href="">Labels</a> / <a href="">Filtres</a></span></div>
                <div id="links_project">
                    <ul>
                    {% for project in projects %}
                        <li><a href="?page=inbox&filtre=project&id={{ project.id }}">{{ project.name }}</a></li>
                    {% endfor %}
                    </ul>
                </div>
            </div>
            <div class="sep"></div>
            <div class="floated content">
                {{ content|raw }} <br />
            </div>
            <div class="clearfix"></div>
        </div>
            
<script>
    $(function() {
        display_date();
    });

    function display_date()
    {
        date = new Date;
        $('#display_date').html(date.getDay() + '/' + date.getMonth() + '/' + date.getFullYear() + ' ' + date.getHours() + ':' + date.getMinutes());
        setTimeout('display_date();', '1000');
    }
</script>
    </body>
</html>