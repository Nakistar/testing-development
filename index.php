<?php
require_once('php/cms.php');
require('php/libreria.php');
session_start();
global $html;
$html="";
$option="";
$dati=array();
 $dati['nome']='';
$dati['cognome']='';
 $dati['email']='';
 $dati['codice']='';
 $file_name="tmp/log.txt";
if(!isset($_SESSION['count']))
$_SESSION['count']=0;

if(!isset($_GET['code']))
{
 if(isset($_POST['submit']))
	{
		//controllo vari sui campi della form
		 $illegal="/[0-9A-Za-z]/";
		 $giorno=date("d");
		 $mese=date("m");
		 $anno=date("Y");
		 if(!empty($_POST['data']))
		 {
		$birthday=explode("-",$_POST['data']);
		 $year=$anno-$birthday[0];
		 $month=$mese-$birthday[1];
		 $day=$giorno-$birthday[2];
		 }//if empty data
		 
		 if(!isset($_POST['nome']) || strlen($_POST['nome']) < 2)
		  $html.="Il campo nome è obbligatorio e minimo 2 caratteri <br>";
		 elseif(preg_match($illegal,$_POST['nome'])==0)
		 $html.="Il nome contiene caratteri non ammessi <br>";
		  if(!isset($_POST['cognome']) || strlen($_POST['cognome']) < 2)
		  $html.="IL campo cognome è obbligatorio e minimo due caratteri <br>";
		  elseif(preg_match($illegal,str_replace(' ','',$_POST['cognome']))==0)
		   $html.="Il cognome contiene caratteri non ammessi <br>";
		  if(!isset($_POST['email']))
		  $html.="Il campo email è obbligatorio";
		  elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
		  $html.="Il campo email ha un formato non valido <br>";
		  if(empty($_POST['data']))
		    $html.="il campo data è obbligatorio <br>";
		else{
		  if($month < 0 || ($month===0 && $day<0))
		    $year--;
		if($year < 18)
		$html.="Sei minorenne non puoi iscriverti!<br>";
		}//else data maggiorenne
	    if(empty($_POST['username']) || strlen($_POST['username'])<6)
			   $html.="Il campo username è obbligatorio e deve essere minimo 6 caratteri <br>";
		elseif(preg_match($illegal,$_POST['username'])==0)
		 $html.="Username contiene caratteri non ammessi <br>";
		if(empty($_POST['password']) || strlen($_POST['username'])<6)
			   $html.="Il campo password è obbligatorio e deve essere minimo 6 caratteri <br>";
		elseif(preg_match($illegal,$_POST['password'])==0)
		 $html.="La password contiene caratteri non ammessi <br>";
		  $_SESSION['nome']=addslashes($_POST['nome']);
				 $_SESSION['cognome']=addslashes($_POST['cognome']);
				 $_SESSION['email']=addslashes($_POST['email']);
		if($html=="")
			 {
				 $dati['nome']=addslashes($_POST['nome']);
				 $dati['cognome']=addslashes($_POST['cognome']);
				 $dati['email']=addslashes($_POST['email']);
				 $dati['data']=$_POST['data'];
				 $dati['codice']=$_POST['codice'];
				 $dati['puntovendita']=$_POST['puntovendita'];
				 if(!empty($_POST['regione']))
				 {
					 $dati['regione']=$_POST['regione'];
					 $dati['provincia']=$_POST['provincia'];
				 }//if empty regione
				 else
				 {
					 $dati['regione']="";
					 $dati['provincia']="";
				 }//else empty regione
				 $dati['username']=addslashes($_POST['username']);
				 $dati['password']=addslashes(sha1($_POST['password']));
				$html.=registra_utente($dati); 
				
            }//if html vuoto
	}
	
if(isset($_POST['login']))
	{
		$_SESSION['count']++;
		$ip=$_SERVER['REMOTE_ADDR'];
		if(file_exists($file_name))
		{
			$puntatore=fopen($file_name,"r");
				while($riga=fgets($puntatore,1024))
				{
					$array=explode(";",$riga);
					if($array[0]==$ip && (time()-$array[1]) < 250)
					{
					  echo '<script>alert("Non puoi piu effettuare prove di accesso \n Usa funzionae recupera password \n Oppure prova piu\' tardi");</script>';
						unset($_POST['user']);
						unset($_POST['psw']);
						break;
					}
				}
			fclose($puntatore);
		}
		if(!isset($_POST['user']))
		 $html."Username Obbligatoria <br>";
		elseif(!isset($_POST['psw']))
		$html.="Password Obbligatoria <br>";
		else
		{
			$web_username=addslashes($_POST['user']);
			$web_password=addslashes(sha1($_POST['psw']));
	        list($controllo,$id,$stato,$accesso,$puntovendita)=controllo_credendiali($web_username,$web_password);
	  if($controllo)
			   {
				 
				if($stato==0)
				{
					$html.="Lei non ha confermato l'iscrizione <br> Le è stata inviata email per confermare";
					$html.=invia_conferma($id);
				}
				else
				{
				  $_SESSION['username']=$web_username;
				 $_SESSION['id_user']=$id;
				  $_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
				  $_SESSION['accesso']=$accesso;
				  $_SESSION['puntovendita']=$puntovendita;
				 inizia_sessione();
				 header("Location:pages/admin.php");
				}
			 }
			 else
			  {
				  if($_SESSION['count']==3)
				  {
					  $puntatore=fopen($file_name,"a");
					  fwrite($puntatore,$ip.";".time()."\r\n");
					  fclose($puntatore);
					  $html.="Stai provando troppe volte.Usa la funzione recupera password ";
					  $_SESSION['count']=0;
					
				  }
				  else
				  {
				  $html.="Username e/o Password errati !";
				  sleep(3);
				 
				  }
				  
			  }
        }
	}
}
	else
{
	$code=addslashes($_GET['code']);
	$msg=controllo_codice($code);
	echo "<script> alert('".$msg."')</script>";
	header("Refresh:1;url=index.php");

}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Videoteca</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-theme.css" rel="stylesheet">
    <link rel="shortcut icon" href="Images/apple-touch-icon.png">
    <link href="css/myStyle.css" rel="stylesheet" type="text/css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!------librerie jquery e bootstrap.js------->
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
  
  </head>
<body>
<?php echo head() ?>
<?php echo section() ?>
<?php
if(!empty($html))
{
?>
<script>

	 $('#msg').html("<p class='text-danger'><?=$html?></p>");
		$('#modalRegistrazione').modal();
</script>
<?php		
}
?>
<?php echo footer() ?>
</body>
</html>