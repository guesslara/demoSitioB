<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 0);
    if($_SERVER["HTTP_REFERER"]==""){
        echo "<script type='text/javascript'> window.location.href='soporte.html'; </script>";
    }else{
        if(!isset($_POST)){
            echo "<script type='text/javascript'> alert('Acceso erroneo'); window.location.href='soporte.html'; </script>";
        }else{
            $nombre=$_POST["author"];
            $email=$_POST["email"];
            $lada=$_POST["lada"];
            $telefono=$_POST["telefono"];
            $modelo=$_POST["subject"];
            $mensaje=$_POST["text"];
            if($nombre=="" || $email=="" || $lada=="" || $telefono=="" || $modelo=="" || $mensaje==""){
                echo "<script type='text/javascript'> alert('Verifique los datos'); history.back(); </script>";
            }else{
                include("class.validmail.php");
                include('class.smtp.inc');
                $validar = new ValidEmail($email);
                if($validar->validString()){	 // el email tiene caracteres validos                                                        
		    //$sql1="INSERT INTO regs_tic (nombre,mail,lada,telefono,modelo,mensaje) VALUES ('".$nombre."','".$email."','".$lada."','".$telefono."','".$modelo."','".$mensaje."')";//se guarda en la base de datos
                    //$res1=mysql_query($sql1,conectarBd());

		    //insercion en la base de datos de tickets blusens
		    $sqlT="INSERT INTO tickets (nombre,email,telefono,modelo,fallareportada) VALUES ('".$nombre."','".$email."','".$lada."-".$telefono."','".$modelo."','".$mensaje."')";
		    $resT=mysql_query($sqlT,conectarBdTickets());

                    if($resT){
                        //se recupera el ultimo id insertado
                        $ultimoId=mysql_query("select last_insert_id() AS ultimoId",conectarBdTickets());
			$rowUltimoId=mysql_fetch_array($ultimoId);
                        //$sqlDatos="select * from regs_tic where id='".$rowUltimoId["ultimoId"]."'";//se extraen los datos
			$sqlDatos="select * from tickets where id='".$rowUltimoId["ultimoId"]."'";//se extraen los datos
                        $resDatos=mysql_query($sqlDatos,conectarBdTickets());
                        if($resDatos){
                            $rowDatos=mysql_fetch_array($resDatos);                            
                            $ticket["datos"]["nroTicket"]=$rowDatos["id"];
                            $ticket["datos"]["nombreCliente"]=$rowDatos["nombre"];
                            $ticket["datos"]["mailCliente"]=$rowDatos["email"];
                            //$ticket["datos"]["lada"]=$rowDatos["lada"];
                            $ticket["datos"]["telefono"]=$rowDatos["telefono"];
                            $ticket["datos"]["mensaje"]=$rowDatos["fallareportada"];
                            $ticket["datos"]["modelo"]=$rowDatos["modelo"];
                            $subject="Soporte en linea Blusens ticket #".$ticket["datos"]["nroTicket"];                            
                            $message.='Se ha enviado un mensaje a Soporte en linea el '.date("d/m/y")." a las ".date("H:i")."<br><br>";
                            $message.='Numero de Ticket: #'.$ticket["datos"]["nroTicket"]."<br><br>";
			    $message.='Nombre del Cliente: '.$ticket["datos"]["nombreCliente"]."<br><br>";
			    $message.="Email: ".$ticket["datos"]["mailCliente"]."<br><br>";
			    $message.="Telefono: +52 - ".$ticket["datos"]["telefono"]."<br><br>";
			    $message.="Modelo de Producto: ".$ticket["datos"]["modelo"]."<br><br>";
                            $message.="Comentarios: ".$ticket["datos"]["mensaje"]."<br><br>";
                            $message.="<strong><i>Los acentos fueron omitidos intencionalmente. Algunos caracteres especiales pueden cambiar su formato de visualizacion</i></strong><br><br>";
                            $destino="glara@iqelectronics.net";
                            enviaCorreo($subject,$message,$destino);
                            $message="";
                            //se envia notificacion al usuario
                            $message.='Estimado cliente se ha enviado su solicitud a Soporte en linea  Blusens, en breve un ejecutivo se pondra en contacto con usted por correo electronico o via telefonica,
                            el numero de soporte con el cual se registro en el sistema su solictud es el '.$rowDatos["id"].'.<br><br>Datos de la solicitud:<br><br>';
                            $message.='Numero de Ticket: #'.$ticket["datos"]["nroTicket"]."<br><br>";
                            $message.='Nombre del Cliente: '.$ticket["datos"]["nombreCliente"]."<br><br>";
                            $message.="Email: ".$ticket["datos"]["mailCliente"]."<br><br>";
                            $message.="Telefono: +52 - ".$ticket["datos"]["telefono"]."<br><br>";
                            $message.="Modelo de Producto: ".$ticket["datos"]["modelo"]."<br><br>";
                            $message.="Comentarios: ".$ticket["datos"]["fallareportada"]."<br><br>";
                            $message.="<strong>Los acentos fueron omitidos intencionalmente. Algunos caracteres especiales pueden cambiar su formato de visualizacion</strong><br><br>";
                            $subject="Seguimiento Soporte en Linea Blusens Ticket # ".$ticket["datos"]["nroTicket"];
                            enviaCorreo($subject,$message,$ticket["datos"]["mailCliente"]);
                        }else{
                            echo "<br>Ocurrio un error interno en la aplicacion";
                        }                     
                    }else{
                        echo "<script type='text/javascript'> alert('Ocurrio un error en la Aplicacion'); history.back(); </script>";
                    }
                }else{
                    echo "<script type='text/javascript'> alert('Verifique la direccion de correo electronico 2'); history.back(); </script>";
                }
            }
        }
    }

    function enviaCorreo($subject,$message,$destino){        
        $origen_nombre='Soporte';
        $origen_mail="soporte@iqelectronics.com.mx";
        $password_mail="123456";
        $subject=$subject;
        $fecha = date ("d F Y");                                        
        $params['host'] = 'mail.iqelectronics.com.mx';	// Cambiar por su nombre de dominio
        $params['port'] = 9025;			// The smtp server port
        $params['helo'] = 'mail.iqelectronics.com.mx';	// Cambiar por su nombre de dominio
        $params['auth'] = TRUE;			// Whether to use basic authentication or not
        $params['user'] = $origen_mail;	// Correo que utilizara para enviar los correos (no usar el de webmaster por seguridad)
        $params['pass'] = $password_mail;	// Password de la cta de correo. Necesaria para la autenticacion
        $send_params['recipients'] = array($destino); // The recipients (can be multiple), separados por coma.
        $send_params['headers']	   = array(
                                        'Content-Type: text/html;',
                                        'From: "'.$origen_nombre.'" <soporte@iqelectronics.com.mx>',	// Headers
                                        //'To: '.$destino,
                                        'To: '.$destino,
                                        'Subject: '.$subject,
                                        //'Disposition-Notification-To: contacto@odontologos.com.mx',
                                        //'Disposition-Notification-To: '.$origen_mail,
                                        //'Return-Receipt-To: '.$origen_mail,		
                                        'Date: '.date(DATE_RFC822),
                                        'X-Mailer: PHP/' . phpversion(),
                                        'MIME-Version: 1.0',
                                        //'Reply-To: '.$origen_mail'\r\n',
                                        'Return-Path: '.$origen_nombre.'" <sistema Interno TVO>',
                                        'Envelope-To:'.$destino 
                                    );
        $send_params['from'] = $origen_mail;	// This is used as in the MAIL FROM: cmd
        $send_params['body'] = $message;	//Message							// The body of the email
        if(is_object($smtp = smtp::connect($params)) AND $smtp->send($send_params)){
            echo "Mensaje Enviado.<br><br>";            
        }else {
            echo "Error al Enviar el correo.";                                    
        }
    }
    
    function conectarBdTickets(){
	$link=mysql_connect("localhost","root","xampp");
        if(!$link){
            echo "Error al conectar al servidor";
        }else{
            mysql_select_db("iqe_blusens");
        }
        return $link;
    }

    function conectarBd(){
        $link=mysql_connect("localhost","root","xampp");
        if(!$link){
            echo "Error al conectar al servidor";
        }else{
            mysql_select_db("2013_blusens_tic");
        }
        return $link;
    }
?>
