<?php
//$post = $_POST['header'];


require_once '../../../vendor/autoload.php'; 
?>

<?php
class InsertData{
	const DB_HOST = 'localhost';
	const DB_NAME = 'abcdefg_port';
	const DB_USER = 'abcdefg_luke';
	const DB_PASSWORD = 'xxxxxx';

	private $conn = null;

	/**
	 * Open the database connection
	 */
	public function __construct(){
		// open database connection
		$connectionString = sprintf("mysql:host=%s;dbname=%s",
				InsertDataDemo::DB_HOST,
				InsertDataDemo::DB_NAME);
		try {
			$this->conn = new PDO($connectionString,
					InsertDataDemo::DB_USER,
					InsertDataDemo::DB_PASSWORD);

		} catch (PDOException $pe) {
			die($pe->getMessage());
		}
	}
	
	
	public function insert($name){

		$sql = "INSERT INTO request(ID, text_log, timestamp)
				VALUES(NULL, '$name', CURRENT_TIMESTAMP)";

		return $this->conn->exec($sql);
	}
	
	public function selectTwoLastLocations(){
	    $sql = "SELECT text_log from request ORDER BY id DESC LIMIT 2";
	    $result = $this->conn->exec($sql);
	    $export = array();
	    if(mysqli_num_rows($result)>0){
	        while($row = mysqli_fetch_array($result)){
	            $export.push($row['text_log']);
	        }
	    }
	    return $export;
	    
	}
	
	public function __destruct() {
		// close the database connection
		$this->conn = null;
	}
}



$obj = new InsertData(); //insert object

$text = "";

foreach($_REQUEST as $key=>$value)
{
  $text .= "$key=$value";
}

$text .= "teste";

//Push Requests to the database
//$obj->insert(json_encode($_REQUEST));

//DialogFlow is the chatbot api
use Dialogflow\WebhookClient;


$endereco = json_decode(file_get_contents('php://input'),true)['queryResult']['queryText'];
$step = json_decode(file_get_contents('php://input'),true)['queryResult']['fulfillmentText'];


$agent = new WebhookClient(json_decode(file_get_contents('php://input'),true));
if($step == "partida"){
    $test = file_get_contents('php://input');
    $obj->insert($test);
    $response_text = '"localização recebida"
É aqui que você está?
'.$endereco.'?'; 
    $text = \Dialogflow\RichMessage\Text::create()
        ->text('This is text aa ll')
        ->ssml('<speak>This is <say-as interpret-as="characters">ssml</say-as></speak>');
    $agent->reply($response_text);
    header('Content-type: application/json');
    echo json_encode($agent->render());
}
elseif($step == "destino"){
    $test = file_get_contents('php://input');
    $obj->insert($test);
    $response_text = '"Localização Recebida:"
Seria esse seu destino?
'.$endereco.'?'; 
    $text = \Dialogflow\RichMessage\Text::create()
        ->text('This is text aa ll')
        ->ssml('<speak>This is <say-as interpret-as="characters">ssml</say-as></speak>');
    $agent->reply($response_text);
    header('Content-type: application/json');
    echo json_encode($agent->render());
}
elseif($step == "calcular"){
    
    //SQL Queries
    $link = mysqli_connect("localhost", "u540903123_luke", "eV3BuvCV7CIM", "u540903123_port");
    $sql = "SELECT text_log from request ORDER BY id DESC";
    $locations = array();
    
    if($res = mysqli_query($link, $sql)){
        if(mysqli_num_rows($res) > 0){
            while($row = mysqli_fetch_array($res)){
                
                $data = json_decode($row['text_log'], true);
                //echo $data['queryResult']['queryText'];
                array_push($locations, $data['queryResult']['queryText']);
            }
        }
    }
    
    $content = "";
    
    $googleQuery = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(" ","+", $locations[0])."&destination=".str_replace(" ","+", $locations[1])."&mode=transit&key=AIzaSyAhPLk_deyxWvGeWa_F8hCLRhsKyq_TFtY";
    $response = file_get_contents($googleQuery);
    //echo $response;
    
    $json_response = json_decode($response, true);
    
    
    
    $content.= "Ok, vamos mostrar algumas opções de trajetos:

Transporte público:";
    $content.= "
Preço: ".$json_response['routes'][0]['fare']['text'];
    $content.= "
Tempo estimado: ".$json_response['routes'][0]['legs'][0]['duration']['text'];
    $content.= "
Distância: ".$json_response['routes'][0]['legs'][0]['distance']['text'];
    $content.= "
    ";
    
    $googleQuery = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(" ","+", $locations[0])."&destination=".str_replace(" ","+", $locations[1])."&mode=walking&key=AIzaSyAhPLk_deyxWvGeWa_F8hCLRhsKyq_TFtY";
    $response = file_get_contents($googleQuery);
    //echo $response;
    
    $json_response = json_decode($response, true);
    
    $content.= "
A pé:";
    $content.= "
Tempo estimado: ".$json_response['routes'][0]['legs'][0]['duration']['text'];
    $content.= "
Distância: ".$json_response['routes'][0]['legs'][0]['distance']['text'];
    $content.= "
    ";
    
    $googleQuery = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(" ","+", $locations[0])."&destination=".str_replace(" ","+", $locations[1])."&mode=bicycling&key=AIzaSyAhPLk_deyxWvGeWa_F8hCLRhsKyq_TFtY";
    $response = file_get_contents($googleQuery);
    //echo $response;
    
    $json_response = json_decode($response, true);
    
    $content.= "
Bicicleta:";
    $content.= "
Tempo estimado: ".$json_response['routes'][0]['legs'][0]['duration']['text'];
    $content.= "
Distância: ".$json_response['routes'][0]['legs'][0]['distance']['text'];
    $content.= "
    ";
    
    
    $googleQuery = "https://maps.googleapis.com/maps/api/directions/json?origin=".str_replace(" ","+", $locations[0])."&destination=".str_replace(" ","+", $locations[1])."&mode=driving&key=AIzaSyAhPLk_deyxWvGeWa_F8hCLRhsKyq_TFtY";
    $response = file_get_contents($googleQuery);
    //echo $response;
    
    $json_response = json_decode($response, true);
    
    $content.= "
de Carro:";
    $content.= "
Tempo estimado: ".$json_response['routes'][0]['legs'][0]['duration']['text'];
    $content.= "
Distância: ".$json_response['routes'][0]['legs'][0]['distance']['text'];
    /*
    $content.= "
    ".$json_response['routes'][0]['bounds']['northeast']['lat'];
    $content.= "
    ".$json_response['routes'][0]['bounds']['northeast']['lng'];
    $content.= "
    ".$json_response['routes'][0]['bounds']['southwest']['lat'];
    $content.= "
    ".$json_response['routes'][0]['bounds']['southwest']['lng'];
    */
    
    $content = str_replace("min", "minuto", str_replace("hour", "hora e", $content));
    
    $content .= "
Você também pode ir de uber, clique no link abaixo: 
";
    
    $content.= "
    https://m.uber.com/ul/?action=setPickup&pickup[latitude]=".$json_response['routes'][0]['bounds']['northeast']['lat']."&pickup[longitude]=".$json_response['routes'][0]['bounds']['northeast']['lng']."&pickup[nickname]=UberHQ&pickup[formatted_address]=".str_replace(" ", "%20", $locations[0])."&dropoff[latitude]=".$json_response['routes'][0]['bounds']['southwest']['lat']."&dropoff[longitude]=".$json_response['routes'][0]['bounds']['southwest']['lng']."&dropoff[formatted_address]=".str_replace(" ", "%20", $locations[1])."";
    
    
    
    $agent->reply($content);
    header('Content-type: application/json');
    echo json_encode($agent->render());
    
}

?>

