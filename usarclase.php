<?php
/**
	Ejemplo de uso de clase validEmail

*/

include("validmail.php");

$validar = new ValidEmail("correo@micorreo.com");
if($validar->validString())	 // el email tiene caracteres validos
{	
	echo "el email tiene caracteres validos";
	if($validar->validate())	// Ahora comprobamos que exista la cuenta en el servidor.
	{
		echo "el dominio es valido";
	}
}
?>