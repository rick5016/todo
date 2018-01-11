<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table style="width:100%;">{{ xdebug_message|raw }}</table>
        <h1>Query</h1>
        {% for q in query %}
        {{ q }}<br /><br />
        {% endfor %}
    </body>
</html>
