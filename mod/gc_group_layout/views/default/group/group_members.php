<?php

$items = json_decode(file_get_contents('http://192.168.1.18/gcconnex/services/api/rest/json/?method=get.group_list&api_key=4d09e3b4dd0276e9308cf88740a34d62923a55d9&offset=0'), true);
$items = $items['result'];

$num = 50;

$pages = ceil($num / 10);

$tbody = "";
foreach ($items as $item) {
    $tbody .= "
    <tr>
        <td>{$item['guid']}</td>
        <td>{$item['type']}</td>
        <td>{$item['date_created']}</td>
    </tr>";
}

$paginate = "";
for ($k=0; $k<=$pages; $k++) {
    $paginate .= "
    <a id='$k' href='#'>$k</a>
    ";
}

echo <<<___HTML



<table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Rand1</th>
                <th>Rand2</th>
                <th>Rand3</th>
            </tr>
        </thead>
        <tbody>
            $tbody
        </tbody>
    </table>

    $paginate


<script>
</script>

___HTML;


