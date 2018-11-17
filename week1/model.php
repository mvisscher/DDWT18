<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Setup a connection with the database
 *
 */
function connect_db(){
    $host = "localhost";
    $database = "ddwt18_week1";
    $username = "ddwt18";
    $password = "ddwt18";

    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    return $conn;
}

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pritty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creats HTML alert code with information about the success or failure
 * @param bool $type True if success, False if failure
 * @param string $message Error/Success message
 * @return string
 */
function get_error($feedback){
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}

/**
 * Count series
 * Counts the amount of series in the database
 */
function count_series($conn){
    $stmt = $conn->prepare("SELECT COUNT(ID) FROM series");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result["COUNT(ID)"];

}

function get_series($conn){
    $stmt = $conn->prepare("SELECT id, name FROM series");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $series = [];
    foreach($result as $key => $value){
        $series[$key]["name"]   = htmlspecialchars($value["name"]);
        $series[$key]["id"]     = htmlspecialchars($value["id"]);
    }
    return $series;
}

function get_series_table($series){
    $table_head = '<table class="table table-hover">
        <thead>
        <tr>
            <th scope="col">Series</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>';
    $table_bottom = '</tbody>
    </table>';
    foreach($series as $serie){
        $table_head = $table_head.
        '<tr>
            <th scope="row">'.$serie["name"].'</th>
<td><a href="/DDWT18/week1/serie/?serie_id='.$serie["id"].'" role="button" class="btn btn-primary">More info</a></td>
</tr>';
    }
    $table = $table_head.$table_bottom;
    return $table;
}

function get_series_info($id, $conn){
    $stmt = $conn->prepare("SELECT * FROM series WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $series_info = [];
    foreach($result as $key => $value){
        $series_info["name"]        = htmlspecialchars($value["name"]);
        $series_info["abstract"]    = htmlspecialchars($value["abstract"]);
        $series_info["seasons"]     = $value["seasons"];
        $series_info["creator"]     = htmlspecialchars($value["creator"]);
        $series_info["id"]          = $value["id"];
    }
    return $series_info;
}

function add_series($serie_info, $conn){
    $error = false;
    $errormessage = "";

    if(empty($serie_info["Name"])){
        $error = true;
        $errormessage = $errormessage."Name is empty. ";
    }
    if(empty($serie_info["Creator"])){
        $error = true;
        $errormessage = $errormessage."Creator is empty. ";
    }
    if(empty($serie_info["Abstract"])){
        $error = true;
        $errormessage = $errormessage."Abstract is empty. ";
    }
    if(empty($serie_info["Seasons"])){
        $error = true;
        $errormessage = $errormessage."Seasons is empty. ";
    }
    if(!is_numeric($serie_info["Seasons"])){
        $error = true;
        $errormessage = $errormessage."Seasons is not numeric. ";
    }
    /* Check if serie already exists */
    $stmt = $conn->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->rowCount();
    if ($serie){
        $error = true;
        $errormessage = $errormessage."Series was already added. ";

    }
    if($error){
        return[
        'type' => 'danger',
        'message' => 'Series wasn’t added. There was an error: '.$errormessage
        ];
    }
    /* Add Serie */
    $stmt = $conn->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

function update_series($serie_info, $conn){
    $error = false;
    $errormessage = "";

    if(empty($serie_info["Name"])){
        $error = true;
        $errormessage = $errormessage."Name is empty. ";
    }
    if(empty($serie_info["Creator"])){
        $error = true;
        $errormessage = $errormessage."Creator is empty. ";
    }
    if(empty($serie_info["Abstract"])){
        $error = true;
        $errormessage = $errormessage."Abstract is empty. ";
    }
    if(empty($serie_info["Seasons"])){
        $error = true;
        $errormessage = $errormessage."Seasons is empty. ";
    }
    if(!is_numeric($serie_info["Seasons"])){
        $error = true;
        $errormessage = $errormessage."Seasons is not numeric. ";
    }
    /* Check if serie already exists */
    $stmt = $conn->prepare('SELECT * FROM series WHERE name = ? AND NOT id = ?');
    $stmt->execute([$serie_info['Name'], $serie_info['Id']]);
    $serie = $stmt->rowCount();
    if ($serie){
        $error = true;
        $errormessage = $errormessage."There is another serie with this name. ";

    }
    if($error){
        return[
            'type' => 'danger',
            'message' => 'Series wasn’t updated. There was an error: '.$errormessage
        ];
    }
    /* Edit Serie */
    $stmt = $conn->prepare('UPDATE series SET name=?, creator=?, seasons=?, abstract=?  WHERE id = ?');
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $serie_info['Id']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' updated.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not updated. Try it again.'
        ];
    }
}

function remove_serie($serie_id, $conn){
    /* Delete Serie */
    $stmt = $conn->prepare('DELETE FROM series WHERE id = ?');
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series deleted.")
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not deleted. Try it again.'
        ];
    }
}