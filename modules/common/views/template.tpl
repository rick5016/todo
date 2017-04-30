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
            .container {width: 100%; position:relative}
            .menu, .content {
                width:60%; 
                box-sizing:border-box; 
                padding:10px;
            }
            .menu {width:20%; }
            .clearfix {clear:both;}
            .sep {
                position: absolute;
                left:20%;
                top:0;
                bottom:0;
                border-left: 1px dotted #ddd;
            }
        </style>
        <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <!--<script type="text/javascript" src="daterangepicker/moment.min.js"></script>-->
        <script type="text/javascript" src="library/jquery-timepicker/jquery.timepicker.js"></script>
        <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="floated menu">
                <div style="border: 1px black dotted;text-align:center;margin-right:20px;margin-bottom: 20px;">{{ "now"|date('d/m/Y H:i', timezone="Europe/Paris") }}</div>
                <ul style="list-style-type:none;">
                    <li><a href="index.php?page=task"><span title="ajouter" class="glyphicon glyphicon-plus"></span> add</a></li>
                    <li><a href="index.php?page=inbox"><span title="boîte de réception" class="glyphicon glyphicon-inbox"></span> Inbox</a></li>
                </ul>
                Project / Labels / Filtres <br />

                - project 1
            </div>
            <div class="sep"></div>
            <div class="floated content">
                {% if page is not defined %}
                    Acceuil
                {% elseif page == 'del' and id != null %}
                    <script>window.location="index.php?page=inbox";</script>
                {% elseif page == 'done' and id != null %}
                    <script>window.location="index.php?page=inbox";</script>
                {% elseif page == 'inbox' %}
                    {% include('/front/views/inbox.tpl') %}
                {% elseif page == 'task' %}
                    {% include('/front/views/task.tpl') %}
                {% endif %}
            </div>
            <div class="clearfix"></div>
        </div>
    </body>
</html>