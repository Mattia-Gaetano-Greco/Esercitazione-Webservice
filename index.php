<?php
/*header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

foreach($_SERVER as $chiave=>$valore){
    echo $chiave."-->".$valore."\n<br>";
}

*/

//elabora header
$metodo=$_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

//legge il tipo di contenuto di ritorno richiesto dal client
$retct=$_SERVER["HTTP_ACCEPT"];
$ret=explode("/",$retct);
//print_r($uri);
//echo "metodo-->".$metodo;

//connette al database
$servername = "localhost";
$username = "root";
$password = "";

$connessione = new mysqli("localhost", "root", "", "cap_comuni");
if ($connessione->connect_errno) {
    echo("Connessione fallita: ".$conn->connect_error.".");
    exit();
}

// controlla l'URL della richiesta
if (array_key_exists(3, $uri) && $uri[3] == "cap") {


    if ($metodo=="GET"){
        $extra = "";
        if (array_key_exists(4, $uri)) {
            if (is_numeric($uri[4])) {
                $extra = " WHERE cap = " . $uri[4];
            }
        }

        if(!$risultati=$connessione->query("SELECT * FROM cap_comuni" . $extra)) {
            http_response_code(500);
            exit();
        } else {
            if($risultati->num_rows>0) {
                $r = "";
                if ($ret[1]=="xml"){
                    $r = "<?xml version=\"1.0\"?><root>";
                }
                else { // if is json or something else
                    $r = "{";
                }
                while($record = $risultati->fetch_array(MYSQLI_ASSOC)) {        
                    if ($ret[1]=="xml"){
                        $r = $r .'<comune><cap>'.$record["cap"].'</cap><nome>'.$record["nome"].'</cap></comune>';
                    }
                    else { // if is json or something else
                        $r = $r . "{\"cap\":".$record["cap"].", \"nome\":\"".$record["nome"]."\"};";
                    }
                }
                if ($ret[1]=="xml") {
                    $r = $r . "</root>";
                }
                else { // if is json or something else
                    $r = substr($r, 0, -1) . "}";
                }
                echo $r;
                $risultati->close();
            }
            else {
                if (array_key_exists(4, $uri)) {
                    http_response_code(400);
                    exit();
                }
            }
        }

        http_response_code(200);
        exit();

    }


    if ($metodo=="POST"){
        //legge il tipo di contenuto inviato dal client
        $ct=$_SERVER["CONTENT_TYPE"];
        $type=explode("/",$ct);

        //recupera i dati dall'header
       $body=file_get_contents('php://input');
       // echo $body
       
       //converte in array associativo
        if ($type[1]=="json"){
            $data = json_decode($body,true);
        }
        if ($type[1]=="xml"){
            $xml = simplexml_load_string($body);
            $json = json_encode($xml);
            $data = json_decode($json, true);
        }
        
        // controlla che siano stati specificati tutti i campi
        if (!array_key_exists("nome", $data)) {
            http_response_code(400);
            exit();
            //echo "Non è stato specificato il campo \"nome\"!";
            if (!is_string($data["nome"])) {
                http_response_code(400);
                exit();
                //echo "Il campo \"nome\" non è di tipo string!";
            }
        }
        if (!array_key_exists("cap", $data)) {
            http_response_code(400);
            exit();
            //echo "Non è stato specificato il campo \"cap\"!";
            if (!is_numeric($data["cap"])) {
                http_response_code(400);
                exit();
                //echo "Il campo \"cap\" non è di tipo string!";
            } else if (strlen(strval($data["cap"])) != 5) {
                http_response_code(400);
                exit();
                //echo "Il camp \"cap\" deve contenere esattamente 5 cifre!";
            }
        }

        // controlla se esiste già un comune con lo stesso CAP
        if(!$risultati=$connessione->query("SELECT * FROM cap_comuni WHERE cap = " . $data["cap"])) {
            http_response_code(500);
            exit();
        } else {
            if($risultati->num_rows>0) {
                http_response_code(403);
                exit();
            }
        }
        if(!$risultati=$connessione->query("SELECT * FROM cap_comuni WHERE nome = ('" . $data["nome"] . "')")) {
            http_response_code(500);
            exit();
        } else {
            if($risultati->num_rows>0) {
                http_response_code(403);
                exit();
            }
        }

        // effettua la query per salvare i dati nel database
        if (!$risultati=$connessione->query("INSERT INTO cap_comuni (nome, cap) VALUES ('" . $data["nome"] . "', " . $data["cap"] . ");")) {
            http_response_code(500);
            exit();
        }

        http_response_code(200);
        exit();
       
    }


    if ($metodo=="PUT"){
        //legge il tipo di contenuto inviato dal client
        $ct=$_SERVER["CONTENT_TYPE"];
        $type=explode("/",$ct);

        //recupera i dati dall'header
       $body=file_get_contents('php://input');
       // echo $body
       
       //converte in array associativo
        if ($type[1]=="json"){
            $data = json_decode($body,true);
        }
        if ($type[1]=="xml"){
            $xml = simplexml_load_string($body);
            $json = json_encode($xml);
            $data = json_decode($json, true);
        }
        
        // controlla che siano stati specificati tutti i campi
        if (!array_key_exists("nome", $data)) {
            http_response_code(400);
            exit();
            //echo "Non è stato specificato il campo \"nome\"!";
            if (!is_string($data["nome"])) {
                http_response_code(400);
                exit();
                //echo "Il campo \"nome\" non è di tipo string!";
            }
        }
        if (!array_key_exists(4, $uri)) {
            http_response_code(400);
            exit();
            if (!is_numeric($uri[4])) {
                http_response_code(400);
                exit();
            } else if (strlen(strval($uri[4])) != 5) {
                http_response_code(400);
                exit();
            }
        }

        // controlla se esiste un comune con il CAP dato
        if(!$risultati=$connessione->query("SELECT * FROM cap_comuni WHERE cap = " . $uri[4])) {
            http_response_code(500);
            exit();
        } else {
            if($risultati->num_rows<1) {
                http_response_code(403);
                exit();
            }
        }
        if(!$risultati=$connessione->query("SELECT * FROM cap_comuni WHERE nome = '(" . $data["nome"] . ")'")) {
            http_response_code(500);
            exit();
        } else {
            if($risultati->num_rows>0) {
                http_response_code(403);
                exit();
            }
        }

        // effettua la query per salvare i dati nel database
        if (!$risultati=$connessione->query("UPDATE cap_comuni SET nome = '" . $data["nome"] . "' WHERE cap = " . $uri[4] . ";")) {
            http_response_code(500);
            exit();
        }

        http_response_code(200);
        exit();



        







    }


    if ($metodo=="DELETE"){
        if (array_key_exists(4, $uri)) {            
            // controlla che il CAP sia ben formato
            if (!is_numeric($uri[4])) {
                http_response_code(400);
                exit();
            } else if (strlen(strval($uri[4])) != 5) {
                http_response_code(400);
                exit();
            }
            // controlla se esiste un comune con il CAP dato
            if(!$risultati=$connessione->query("SELECT * FROM cap_comuni WHERE cap = " . $uri[4])) {
                http_response_code(500);
                exit();
            } else {
                if($risultati->num_rows<1) {
                    http_response_code(403);
                    exit();
                }
            }
            if(!$risultati=$connessione->query("DELETE FROM cap_comuni WHERE cap = " . $uri[4])) {
                //echo("Errore nell'esecuzione della query: ".$connessione->error.".");
                http_response_code(500);
                exit();
            } else {
                http_response_code(200);
                exit();
            }
        } else {
            http_response_code(400);
            exit();
        }

        http_response_code(200);
        exit();

    }

} else {
    http_response_code(404);
    exit();
}

?>