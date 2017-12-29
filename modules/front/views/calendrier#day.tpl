{{ num_jour }}/{{ num_mois }}/{{ num_an }}

<table style="width:100%;">
        <tr style="height: 20px;">
            <td style="border:solid 1px black;width: 30px;" colspan="2">
                toute la journée
            </td>
        </tr>
        {% for i in 0..23 %}
        <tr style="height: 20px;">
            <td style="border:solid 1px black;width: 30px;" rowspan="2">
                {{ i }}
            </td>
            <td style="border:solid 1px black;">
                task 1
            </td>
        </tr>
        <tr style="height: 20px;">
            <td style="border:solid 1px black;">
                task 2
            </td>
        </tr>
        {% endfor %}
</table>