<?php
class ticket{
	public function guardar($v){
		//echo "<br>guardar($v)";
		$mv=explode("|",trim($v));
		$id_usuario=$_SESSION["usuario_id"];
		//echo "".
		$sql="INSERT INTO reg_tickets(id,fecha,hora,id_usuario,area,tema,descripcion,status,obs) 
		VALUES (NULL,'".date("Y-m-d")."','".date("H:i:s")."','".$id_usuario."','".$mv[0]."','".$mv[2]."','".$mv[3]."','NUEVO','".$mv[4]."'); ";
		require("../conf/conexion.php");
		if (mysql_db_query($db_actual,$sql,$link)){ 
			//return true;
			echo "Su n&uacute;mero de Ticket es:&nbsp;&nbsp;&nbsp; <span class='spa_no_ticket'>".mysql_insert_id($link)."<span>";
			/****************************/
			if($mv[0]==1){
				
			
			//se obtiene el ultimo id
			$sql2="SELECT LAST_INSERT_ID() AS idReg";
			$res2=mysql_db_query($db_actual,$sql2,$link);
			$row2=mysql_fetch_array($res2);
			/****************************/
			$sql3="select * from reg_tickets where id='".$row2["idReg"]."'";
			$res3=mysql_db_query($db_actual,$sql3,$link);
			$row3=mysql_fetch_array($res3);
			//se extrae la informacion del usuario
			$sql1="select * from cat_usuarios where id='".$id_usuario."'";
			$res1=mysql_db_query($db_actual,$sql1,$link);
			$row1=mysql_fetch_array($res1);		
			
			include('class.smtp.inc');
				$origen_nombre='Soporte';
				$origen_mail="soporte@iqelectronics.com.mx";
				$password_mail="123456";
				$subject="Ticket Nuevo #".$row2["idReg"];
				$fecha = date ("d F Y");
				
				$params['host'] = 'iqelectronics.com.mx';	// Cambiar por su nombre de dominio
				$params['port'] = 9025;			// The smtp server port
				$params['helo'] = 'iqelectronics.com.mx';	// Cambiar por su nombre de dominio
				$params['auth'] = TRUE;			// Whether to use basic authentication or not
				$params['user'] = $origen_mail;	// Correo que utilizara para enviar los correos (no usar el de webmaster por seguridad)
				$params['pass'] = $password_mail;	// Password de la cta de correo. Necesaria para la autenticacion
				//$destino="glara@iqelectronics.net;uvelez@iqelectronics.net;sistemas@iqelectronics.com.mx;drjuarez@iqelectronics.com.mx;hgmontoya@iqelectronics.com.mx";//$_POST['emailprueba'];consejomexicanodeendodoncia@yahoo.com.mx
				$destino="glara@iqelectronics.net,uvelez@iqelectronics.net";//$_POST['emailprueba'];consejomexicanodeendodoncia@yahoo.com.mx
					$message.='Mensaje Enviado el '.date("d/m/y")." a las ".date("H:i")."<br><br>";
					$message.='El usuario: : '.$row1["nombre"]." ".$row1["apellidos"]." genero un nuevo ticket con numero ".$row2["idReg"]."<br><br>";
					$message.="Tema / Asunto: ".$row3["tema"]."<br><br>";
					$message.="Descripci&oacute;n: ".$row3["descripcion"]."<br><br>";
					$message.="Correo generado Autom&aacute;ticamente<br><br>";
					$send_params['recipients'] = array("glara@iqelectronics.net","uvelez@iqelectronics.net","drjuarez@iqelectronics.com.mx","sistemas@iqelectronics.com.mx","hgmontoya@iqelectronics.com.mx"); // The recipients (can be multiple), separados por coma.
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
			
					$send_params['from']		= $origen_mail;	// This is used as in the MAIL FROM: cmd
																									
					$send_params['body']		= $message;	//Message							// The body of the email
			
					if(is_object($smtp = smtp::connect($params)) AND $smtp->send($send_params)){
						echo "";//
						// Any recipients that failed (relaying denied for example) will be logged in the errors variable.
						//print_r($smtp->errors);
					}else {
						//echo " - NO se envio";
						//echo "<script type='text/javascript'> alert('".$smtp->errors."'); </script>";
					}			
			}
			
			/****************************/
		} else{ 
			echo "<br>Error SQL (".mysql_error($link).").";	
			//return false; 
		}
		
	}
	public function listar(){
		//echo "<br>listar() area (".$_SESSION["usuario_grupo"].")";
		($_SESSION["usuario_grupo"]==0)? $whereX="":$whereX="WHERE area=".$_SESSION["usuario_grupo"];
		include("../conf/conexion.php");
		$sql="SELECT id,fecha,id_usuario,area,tema,status,atiende FROM reg_tickets $whereX ORDER BY id DESC; ";
		/*
		// Calculo y paginacion ...
		$paginacion_requerida=false;
		$paginacion_sql_total="SELECT count(id) FROM reg_tickets $whereX ORDER BY id DESC; ";
		$paginacion_total_resultados=$this->dame_no_resultados($paginacion_sql_total);
		$paginacion_resultados_x_pagina=15;
		$paginacion_no_paginas_resultantes=ceil(round($paginacion_total_resultados/$paginacion_resultados_x_pagina));	
		*/
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){
				?>
                <div id="div_ticket_detalle"></div>
                <div id="div_ticket_lista">
				<h3 align="center">tickets</h3>
                <table align="center" width="95%" cellspacing="0" cellpadding="3" class="tabla_bordes">
                <tr>
                    <th>#</th>
                    <th>fecha</th>
                    <th>area de servicio</th>
                    <th>usuario</th>
                    <th>tema</th>
                    <th>status</th>
                    <th>acciones</th>
                </tr><?php
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?>
					<tr onmouseover="this.style.background='#ffffff';" onmouseout="this.style.background=''">
						<td align="center"><?=$reg["id"]?></td>
						<td align="center"><?=$reg["fecha"]?></td>
						<td><?php
                        	//echo $reg["area"];
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($reg["area"]);
						?></td>
						<td><?php
                        	//echo $reg["id_usuario"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo " ".$u1->dame_nombre_usuario($reg["id_usuario"]);
						?></td>
						<td>&nbsp;<?php
                        //$reg["tema"];
						(strlen($reg["tema"])>10)? $t2=substr($reg["tema"],0,10)."..." : $t2=$reg["tema"];
						echo $t2;																	   
						?></td>
					  <td style="font-size:small;">&nbsp;<?=$reg["status"]?></td>
                        <td align="center" style="font-size:small;">
<a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=ver_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">ver</a>
<?php /*if($reg["status"]=="NUEVO"&&$_SESSION["usuario_nivel"]==1){ ?>
	| <a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=asignar_ticket&it=<?=$reg["id"]?>&ia=<?=$reg["area"]?>','div_ticket_lista')">asignar</a> 
<?php }else { echo " | asignar "; } */
/*
if($reg["status"]=="ASIGNADO"){ ?>
| <a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=procesar_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">procesar</a><?php
}else{
	echo "procesar";
}
*/

?>	</td>
                    </tr><?php					
				}
				?>
				</table>
                </div><?php
			}else{ echo "<br><div align='center'>No se encontraron resultados.</div>"; }
		} else{ echo "<br><div align='center'>Error SQL (".mysql_error($link).").</div>";	}		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function listar2($pagina,$campo,$operador,$criterio,$orden,$ad){
		//$pagina=5;
		($operador=='LIKE')? $campo_operador_criterio=" $campo LIKE '%$criterio%' ":$campo_operador_criterio=" $campo$operador'$criterio' ";
		($_SESSION["usuario_grupo"]==0)? $whereX=" WHERE $campo_operador_criterio ":$whereX="WHERE $campo_operador_criterio AND area=".$_SESSION["usuario_grupo"];
		//$whereX=" WHERE $campo_operador_criterio ";
		
		// Calculo y paginacion ...
		//echo "<br>(pagina,campo,operador,criterio,orden,ad)=($pagina,$campo,$operador,$criterio,$orden,$ad)";
		$paginacion_sql_total="SELECT count(id) FROM reg_tickets $whereX; ";
		
		$paginacion_total_resultados=$this->dame_no_resultados($paginacion_sql_total);
		$paginacion_resultados_x_pagina=20;
		$paginacion_no_paginas_resultantes=round(ceil($paginacion_total_resultados/$paginacion_resultados_x_pagina));	
		($pagina<=1)? $limite_inferior=0 : $limite_inferior=($pagina-1)*$paginacion_resultados_x_pagina;	
		($paginacion_no_paginas_resultantes<=1)?$paginacion_requerida=false:$paginacion_requerida=true;

		include("../conf/conexion.php");
		$sql="SELECT id,fecha,id_usuario,area,tema,status,atiende FROM reg_tickets $whereX ORDER BY $orden $ad LIMIT $limite_inferior,$paginacion_resultados_x_pagina; ";
		//echo "<br><hr>$paginacion_sql_total<hr>".$sql;
		//if($_SESSION["usuario_id"]==6) echo $sql."<br>".$paginacion_sql_total."<br>Paginas resultantes [$paginacion_no_paginas_resultantes=ceil(round($paginacion_total_resultados/$paginacion_resultados_x_pagina))]";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){
				?>
				                <div id="div_ticket_detalle"></div>
                <div id="div_ticket_lista">
				<div align="center" style="text-align:center; font-size:large; font-weight:bold; margin-bottom:3px;">tickets</div>
                <table align="center" width="98%" cellspacing="0" cellpadding="3" class="tabla_bordes">
                <tr>
                    <th><a href="#" onclick="mostrar_buscar_ticket()" id="a_txt_buscar_x_ndt" title="Buscar por id ( escriba el id y presione enter )" class="link_02">id</a><input type="text" id="txt_buscar_x_ndt" size="3" style="text-align:center; font-size:small; display:none;" maxlength="5" onkeyup="busca_tecla_enter(1985,event,this.value)"  /></th>
                    <th>fecha</th>
                    <th>area de servicio</th>
                    <th>usuario</th>
                    <th>tema</th>
                    <th>status</th>
                    <th>acciones</th>
                </tr><?php
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?>
					<tr onmouseover="this.style.background='#ffffff';" onmouseout="this.style.background=''">
						<td align="center"><?=$reg["id"]?></td>
						<td align="center"><?=$reg["fecha"]?></td>
						<td><?php
                        	//echo $reg["area"];
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($reg["area"]);
						?></td>
						<td><?php
                        	//echo $reg["id_usuario"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo " ".$u1->dame_nombre_usuario($reg["id_usuario"]);
						?></td>
						<td>&nbsp;<?php
                        //$reg["tema"];
						(strlen($reg["tema"])>10)? $t2=substr($reg["tema"],0,10)."..." : $t2=$reg["tema"];
						echo $t2;																	   
						?></td>
					  <td style="font-size:small;">&nbsp;<?=$reg["status"]?></td>
                        <td align="center" style="font-size:small;">
<a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=ver_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">ver</a>
<?php /*if($reg["status"]=="NUEVO"&&$_SESSION["usuario_nivel"]==1){ ?>
	| <a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=asignar_ticket&it=<?=$reg["id"]?>&ia=<?=$reg["area"]?>','div_ticket_lista')">asignar</a> 
<?php }else { echo " | asignar "; } */
/*
if($reg["status"]=="ASIGNADO"){ ?>
| <a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=procesar_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">procesar</a><?php
}else{
	echo "procesar";
}
*/

?>	</td>
                    </tr><?php					
				}
				?>
				</table>
                <?php
			}else{ /*echo "<br><div align='center'>No se encontraron resultados.</div><br>";*/ }
			?>
				<div style="text-align:center; font-size:small; margin-top:3px;">
					<?php
					echo $paginacion_total_resultados." resultado(s)  &nbsp;&nbsp;&nbsp;";
					if($paginacion_requerida){
echo " [ &nbsp;&nbsp;<b>"; 
if($pagina>1){
	?><a href="#" onclick="ajax('d221','ac=listar_tickets&pagina=1&campo=<?=$campo?>&operador=<?=$operador?>&criterio=<?=$criterio?>&orden=<?=$orden?>&ad=<?=$ad?>')" class="link_02" title="primera pagina">&nbsp;|&lt;&nbsp;</a><?php
}else{ echo "&nbsp; |&lt; &nbsp;"; }					
if($pagina>1){
	?><a href="#" onclick="ajax('d221','ac=listar_tickets&pagina=<?=$pagina-1?>&campo=<?=$campo?>&operador=<?=$operador?>&criterio=<?=$criterio?>&orden=<?=$orden?>&ad=<?=$ad?>')" class="link_02" title="pagina anterior">&nbsp;&lt;&nbsp;</a><?php }else{ echo " &nbsp;&lt;&nbsp; "; }					
echo " &nbsp;$pagina / $paginacion_no_paginas_resultantes&nbsp; ";
if($pagina<$paginacion_no_paginas_resultantes){
	?><a href="#" onclick="ajax('d221','ac=listar_tickets&pagina=<?=$pagina+1?>&campo=<?=$campo?>&operador=<?=$operador?>&criterio=<?=$criterio?>&orden=<?=$orden?>&ad=<?=$ad?>')" class="link_02" title="pagina siguiente">&nbsp;&gt;&nbsp;</a><?php }else{ echo " &nbsp;&gt;&nbsp; "; }
if($pagina<$paginacion_no_paginas_resultantes){
	?><a href="#" onclick="ajax('d221','ac=listar_tickets&pagina=<?=$paginacion_no_paginas_resultantes?>&campo=<?=$campo?>&operador=<?=$operador?>&criterio=<?=$criterio?>&orden=<?=$orden?>&ad=<?=$ad?>')" class="link_02" title="ultima pagina">&nbsp;&gt;|&nbsp;</a><?php
}else{ echo " &nbsp;&gt;|&nbsp; "; }					
echo " </b>&nbsp;&nbsp; ] &nbsp;&nbsp;&nbsp; ";
					}
					
					
					?>&nbsp;&nbsp;&nbsp;[&nbsp;
					<a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&criterio=')" class="link_02" title="mostrar todos tickets">&nbsp;todos&nbsp;</a>
					
					| <a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&operador==&criterio=nuevo')" class="link_02" title="mostrar tickets nuevos">&nbsp;nuevos&nbsp;</a>
					
					| <a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&operador==&criterio=pendiente')" class="link_02" title="mostrar tickets pendientes">&nbsp;pendientes&nbsp;</a>
					
					| <a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&operador==&criterio=en proceso')" class="link_02" title="mostrar tickets en proceso">&nbsp;en proceso&nbsp;</a>
					
					| <a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&operador==&criterio=cancelado')" class="link_02" title="mostrar tickets cancelados">&nbsp;cancelados&nbsp;</a>					
					
					| <a href="#" onclick="ajax('d221','ac=listar_tickets&campo=status&operador==&criterio=finalizado')" class="link_02" title="mostrar tickets finalizados">&nbsp;finalizados&nbsp;</a>&nbsp;]<?php
					?>	
				</div>
				</div>
			<?php
		} else{ echo "<br><div align='center'>Error SQL (".mysql_error($link).").</div>";	}		
	}


	function ver_ticket($it){
		//echo "ver_ticket($it).";
		include("../conf/conexion.php");
		$sql="SELECT * FROM reg_tickets WHERE id=$it LIMIT 1; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?>
					<h3 align="center">ticket no. <?=$it?></h3>
                    <table align="center" class="tabla_bordes" cellspacing="0" cellpadding="2" width="90%">
                    	<tr><td width="22%" class="campo_vertical">fecha</td>
                    	<td width="78%">&nbsp;<?=$reg["fecha"]." ( ".$reg["hora"]." )"?></td>
                    	</tr>
                        <tr><td class="campo_vertical">usuario</td><td>&nbsp;<?php
                        	//echo $reg["id_usuario"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo " ".$u1->dame_nombre_usuario2($reg["id_usuario"]);							
						?></td></tr>
                        <tr><td class="campo_vertical">area de servicio</td><td>&nbsp;<?php
                        	//echo $reg["area"];
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($reg["area"]);                        
						?></td></tr>
                        <tr><td class="campo_vertical">tema</td><td>&nbsp;<?=$reg["tema"]?></td></tr>
                        <tr><td class="campo_vertical">descripcion</td><td>&nbsp;<?=$reg["descripcion"]?></td></tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td class="campo_vertical">status</td>
                          <td>&nbsp;<?=$reg["status"]?></td>
                        </tr>
                       <?php if($reg["status"]=="NUEVO"){ ?>
					    <tr>
                          <td class="campo_vertical"> <a href="#" class="link_02" title="Aviso del area de servicio"> aviso ...</a> </td>
                          <td style="text-align:justify; font-size:large; color:#FF0000;">&nbsp;<?=$reg["preasignacion_aviso"]?></td>
                        </tr>                        
                        <?php 
						}
						if($reg["status"]=="EN PROCESO"){ ?>

                        <tr><td class="campo_vertical">atiende</td><td>&nbsp;<?php
                        	//$reg["atiende"]
                        	//echo $reg["atiende"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo "".$u1->dame_nombre_usuario2($reg["atiende"]);							
						?></td></tr>
                        <tr>
                          <td class="campo_vertical">fecha de inicio</td>
                          <td>&nbsp;<?=$reg["fecha_inicio"]." ( ".$reg["hora_inicio"]." )"?></td>
                        </tr>
                        <!--
						<tr>
                          <td class="campo_vertical">hora de inicio</td>
                          <td><?=$reg["hora_inicio"]?></td>
                        </tr>
						//-->
                        <?php 
						}
						if($reg["status"]=="PENDIENTE"){ 
							?>
							<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
							<tr>
							  <td class="campo_vertical">atiende</td>
							  <td>&nbsp;<?php
								//$reg["atiende"]
								echo $reg["atiende"];
								require_once("../clases/usuario.php");
								$u1=new usuario();
								echo "".$u1->dame_nombre_usuario2($reg["atiende"]);							
							?></td>
							</tr>
							<tr>
							  <td class="campo_vertical">fecha pendiente</td>
							  <td>&nbsp;<?=$reg["fecha_hora_pausa"]?></td>
							</tr>
							<tr>
							  <td class="campo_vertical">horas consumidas</td>
							  <td>&nbsp;<?=$reg["horas_consumidas"]?></td>
							</tr>
							<tr>
							  <td class="campo_vertical">acciones</td>
							  <td>&nbsp;<?=$reg["acciones"]?></td>
							</tr>							
							<?php
						}
						
						if($reg["status"]=="FINALIZADO"){ ?>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr>
                          <td class="campo_vertical">atendi&oacute;</td>
                          <td>&nbsp;<?php
                        	//echo $reg["atiende"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo "".$u1->dame_nombre_usuario2($reg["atiende"]);							
						?></td>
                        </tr>
                        <tr>
                          <td class="campo_vertical">fecha fin</td>
                          <td>&nbsp;<?=$reg["fecha_fin"]." ( ".$reg["hora_fin"]." ) "?></td>
                        </tr>
                        <!--
						<tr>
                          <td class="campo_vertical">hora fin</td>
                          <td><?=$reg["hora_fin"]?></td>
                        </tr>
						//-->
						<tr>
						  <td class="campo_vertical">horas consumidas</td>
						  <td>&nbsp;<?=$reg["horas_consumidas"]?></td>
						</tr>
                        <tr>
                          <td class="campo_vertical">tipo de ticket </td>
                          <td>&nbsp;<?php
						  	//echo $reg["tipo_ticket"];
							$tipo_ticket2=$this->dame_tipo_ticket($reg["tipo_ticket"]);
							echo " ".$tipo_ticket2;
						  ?></td>
                        </tr>
                        <tr>
                          <td class="campo_vertical">acciones</td>
                          <td>&nbsp;<?=$reg["acciones"]?></td>
                        </tr>
						<?php } ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr><td class="campo_vertical">obs</td><td>&nbsp;<?=$reg["obs"]?></td></tr>
                    </table>
<div align="center"><br>
<?php if($reg["status"]=="NUEVO"&&$_SESSION["usuario_nivel"]==1){ ?>
	<a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=asignar_ticket&it=<?=$reg["id"]?>&ia=<?=$reg["area"]?>','div_ticket_lista')">asignar</a> |  
<?php } ?>
<?php if(($reg["status"]=="NUEVO"||$reg["status"]=="EN PROCESO")&&$_SESSION["usuario_nivel"]==1){ ?>
	<script language="javascript">
	function ticket_cancelar(id_ticket){
		var obs=prompt("Observaciones : ");
		ajax('div_ticket_detalle','ac=cancelar_ticket&it='+id_ticket+'&obs='+obs,'div_ticket_lista')
	}
	</script>
	<a href="#" class="link_02" onclick="ticket_cancelar(<?=$reg["id"]?>)">cancelar</a> |  
<?php } ?>

 <a href="#" class="link_02" onClick="ocultar_mostrar_capa('div_ticket_detalle','div_ticket_lista')">cerrar</a></div>                    
					<?php
				}
			}else{ echo "<br>Sin resultados."; }
		} else{ echo "<br>Error SQL (".mysql_error($link).").";	}		
			
		
	}  
	function asignar($it,$ia){
		echo "<div id='div_asignacion_nvo_tipo_ticket' style='display:block;'></div>";
		echo "<div id='div_frm_asignacion_personal' style='display:none;'>";
		//echo "<br>asignar($it,$ia)<br>Id ticket: $it<br>Area: $ia <br> ";
		echo "<h4 align='center'>personal del area de ";
			require_once("area.php");
			$ax=new area();
		echo " ".$ax->dame_nombre_area($ia)."</h4>";


		//echo "<br>".
		$sql="SELECT id,nombre,apellidos,grupo FROM cat_usuarios WHERE grupo=$ia; ";
		include("../conf/conexion.php");
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				?>
				
				<table align="center" width="90%" cellspacing="0" cellpadding="3" class="tabla_bordes">
                <tr><th>id</th><th>grupo</th><th>nombre</th><th>acciones</th></tr><?php
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?><tr><td align="center"><?=$reg["id"]?></td><td><?php
                    	//$reg["grupo"];
                        	$area=$reg["grupo"];
							//echo $area;
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($area); 						
					?></td><td><?=$reg["nombre"]." ".$reg["apellidos"]?></td>
                  <td align="center"><a href="#" class="link_02" onclick="$('#txt_atiende_01').attr('value','<?=$reg["id"]?>'); $('#txt_atiende_02').attr('value','<?=$reg["nombre"]." ".$reg["apellidos"]?>'); ocultar_mostrar_capa('div_frm_asignacion_personal','div_frm_asignacion')">seleccionar</a></td></tr><?php
				}
				?></table><?php
			}else{ echo "<br>Sin resultados."; }
		} else{ echo "<br>Error SQL (".mysql_error($link).").";	}						
		echo "</div>";
		
		/* ajax('d121','ac=guardar_asignacion_ticket&it=<?=$it?>&iu=<?=$reg["id"]?>',''); listar_tic() */
		?>
		<div id="div_frm_asignacion">
		<form>
		<h3 align="center">asignaci&oacute;n</h3>
		<table align="center" width="60%">
		<tr>
		  <td class="campo_vertical">id ticket </td>
		  <td><label><input type="text" id="txt_asignacion_it" readonly="1" value="<?=$it?>" /></label></td>
		  </tr>
		<tr>
			<td class="campo_vertical">tipo de ticket</td>
			<td><label><select id="sel_tipo_ticket">
				<option value="">...</option>
				<?php
				include("../conf/conexion.php");
				$sql="SELECT * FROM cat_tipo_ticket WHERE id_area=$area ORDER BY id; ";
				if ($res=mysql_db_query($db_actual,$sql,$link)){ 
					$ndr=mysql_num_rows($res);
					if($ndr>0){
						while($reg=mysql_fetch_array($res)){
							//echo "<br>"; 	print_r($reg);
							/*?><option><?php print_r($reg); ?></option><?php*/
							?><option value="<?=$reg[0]?>"><?=$reg[0]." - ".$reg[2]?></option><?php
						}
					}else{ echo "<br><div align='center'>Usted no ha generado &Oacute;rdenes de Servicio.</div>"; }
				} else{ echo "<br>Error SQL (".mysql_error($link).").";	}					
				?>
			</select></label>&nbsp;<a href="#" class="link_02" onclick="agregar_tipo_ticket()">agregar</a></td>
		</tr>
		<tr>
			<td class="campo_vertical">atiende</td>
			<td><label><input type="text" id="txt_atiende_01" readonly="1" style=" cursor:pointer; font-weight:bold; text-align:center; width:50px; " onclick="ocultar_mostrar_capa('div_frm_asignacion','div_frm_asignacion_personal')" /></label><label><input type="text" id="txt_atiende_02" readonly="1" style="width:200px; cursor:pointer;" onclick="ocultar_mostrar_capa('div_frm_asignacion','div_frm_asignacion_personal')" /></label></td>
		</tr>

		<tr>
			<td class="campo_vertical">observaciones</td>
			<td><label><textarea id="txt_asignacion_obs" style="width:256px;" /></textarea></label></td>
		</tr>
		<tr><td height="39" colspan="2" align="center"><input type="button" value="Asignar" onclick="fn_asignar_ticket()" /></td></tr>
		</table>
		</form>
		<p align="center">No, el ticket NO puede iniciarse en este momento,<br /> deseo enviar <a href="#" class="link_02" onclick="asignacion_aviso()">aviso</a> al usuario solicitante.</p>
		</div>
		
		<div style="text-align:center; margin:10px; display:none; " id="div_asignacion_aviso">
			<p id="p_asignacion_aviso_resultado">&nbsp;</p>
			<p style="color:#666666; font-weight:bold; font-size:large;">Aviso</p>
			<p>No, el ticket NO puede iniciarse en este momento, deseo enviar aviso al usuario solicitante:</p>
			<p><label><textarea id="txt_asignacion_aviso" style="width:500px;" rows="5"/></textarea></label></p>
			<p><label><input type="button" value="enviar aviso" onclick="enviar_aviso_de_asignacion()" /></label></p>
		</div>
		<?php
	}  
 	function asignacion_aviso_guardar($it,$aviso){
		//echo "<br>asignacion_aviso_guardar($it,$aviso)<br>";
		$sql="UPDATE reg_tickets SET preasignacion_aviso='$aviso' WHERE id=$it LIMIT 1;";
		if($this->ejecuta_sql($sql)){
			echo "<br /><div align='center'>Se envi&oacute; el aviso correctamente.</div>";
		}else{
			echo "<br /><div align='center'>Advertencia: No se envi&oacute; el aviso.</div>";
		}
	}
	function guardar_asignar($i,$t,$a,$o){
		//echo "Guardar Asignacion ($i,$t,$a,$o)";
		//echo "<br>".
		$sql="UPDATE reg_tickets SET atiende=$a,status='EN PROCESO',tipo_ticket=$t,obs_asignacion='$o',fecha_inicio='".date("Y-m-d")."',hora_inicio='".date("H:i:s")."' WHERE id=$i LIMIT 1; ";
		
		if($this->ejecuta_sql($sql)){
			echo "<br><div align='center'>El ticket <b>$i</b> fue asignado al usuario <b>";
			require_once("../clases/usuario.php");
			$u1=new usuario();
			echo " ".$u1->dame_nombre_usuario($a)."</b>.</div>";
		}else{
			echo "<br><div align='center'>Advertencia: El ticket $i <b>NO</b> fue asignado al usuario $a.</div>";
		}
	}
	function procesar_ticket($it,$usuario){
		//echo "procesar_ticket($it)";
		$obs_jefe=$this->dame_obs_jefe($it);
		include("../conf/conexion.php");
		$sql="select reg_tickets.descripcion,reg_tickets.tema,
		cat_tipo_ticket.descripcion AS tipo_de_ticket, cat_tipo_ticket.requiere_materiales
		 from reg_tickets, cat_tipo_ticket 
		 WHERE cat_tipo_ticket.id=reg_tickets.tipo_ticket AND reg_tickets.id=$it ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					$temaX=$reg["tema"];
					$descripcionX=$reg["descripcion"];
					$tipo_de_ticket=$reg["tipo_de_ticket"];
					$requiere_materiales=$reg["requiere_materiales"];
				}
			}else{
				echo "<br><div align='center'>Sin resultados</div>";
			}
		} else{ echo "<br><div align='center'>Error SQL (".mysql_error($link).").</div>";	}				
			
		?>
        <h3 align="center">Procesando ticket no. <?=$it?></h3>
        <form>
        <table align="center" id="tbl_registro_materiales1">
        <tr>
          <td class="campo_vertical">usuario </td>
          <td>&nbsp;<?=$usuario?></td>
        </tr>
        <tr>
          <td class="campo_vertical">tema</td>
          <td>&nbsp;<?=$temaX?></td>
        </tr>
        <tr>
          <td class="campo_vertical">descripcion</td>
          <td>&nbsp;<?=$descripcionX?></td>
        </tr>
        <tr>
          <td class="campo_vertical">tipo de ticket</td>
          <td>&nbsp;<?=$tipo_de_ticket?></td>
        </tr> 
        <tr>
          <td class="campo_vertical">obs.</td>
          <td>&nbsp;<?=$obs_jefe?></td>
        </tr>        
		<tr>
          <td class="campo_vertical">requiere materiales</td>
          <td>&nbsp;<?php 
		  if($requiere_materiales){
		  	echo "<span id='spa_requiere_materiales'>SI</span>";
		  }else{
		  	echo "<span id='spa_requiere_materiales'>NO</span>";
		  }?></td>
        </tr> 		       
        <?php if($requiere_materiales){ ?>
        <tr>
          <td colspan="2">
				<div align="center" style="margin-bottom:3px;">
					<u>registro de materiales consumidos.</u><!--&nbsp;&nbsp;&nbsp;-->
					<br />
					<a href="#" class="link_02" >guardar</a> | <a href="../../inventario_2011/consulta" class="link_02" target="_blank" >catalogo </a> | <a href="#"  class="link_02" onclick="registro_materiales_buscar_colocar_productos()" >buscar</a>
					<span id="spa_registro_materiales_buscar_colocar_productos"></span>
				</div>
				<table width="100%" cellspacing="0" cellpadding="1" class="tabla_bordes" id="tab_mat_consumidosX" >
				<tr>
					<th>id producto</th>
					<th>descripcion</th>
					<th>especificacion</th>
					<th>unidad</th>
					<th>cantidad</th>
					<th>obs</th>
					<th><span id="hid_consumos_num_filas" style="display:none;">1</span><a href="#" onclick="consumo_materiales_agregar_fila()" title="agregar otra fila" class="link_02">&nbsp;+&nbsp;</a></th>					
				</tr>
				<tr align="center">
					<!--<td><input type="checkbox" title="Seleccione esta casilla cuando este para buscar el producto y" /></td>-->
					<td align="center"><input type="text" id="txt_consumo_materiales_idp1" class="txt_campo_chico" /></td>
					<td><input type="text" id="txt_consumo_materiales_des1" class="txt_campo_mediano" readonly="1" /></td>
					<td><input type="text" id="txt_consumo_materiales_esp1" class="txt_campo_mediano" readonly="1"  /></td>
					<td><input type="text" id="txt_consumo_materiales_uni1" class="txt_campo_chico" readonly="1"  /></td>
					<td><input type="text" id="txt_consumo_materiales_can1" class="txt_campo_chico"  /></td>
					<td><input type="text" id="txt_consumo_materiales_obs1" class="txt_campo_mediano"  /></td>
					<td>&nbsp;</td>
				</tr>				
				</table>		  
		  </td>
        </tr> 		
		<?php } ?>
        <tr>
          <td class="campo_vertical"><label><input type="hidden" value="<?=$it?>" id="hid_it_procesar" /></label>acciones</td>
          <td><label><textarea cols="40" rows="4" id="txt_it_procesar"></textarea></label></td>
        </tr>
        <tr>
          <td class="campo_vertical">status</td>
          <td><label><select id="sel_it_procesar">
          	<option value="">...</option>
            <option value="PENDIENTE">PENDIENTE</option>
            <option value="FINALIZADO">FINALIZADO</option>
          </select></label></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><br /><input type="button" value="Guardar" onclick="procesar_guardar()" /> <input type="reset" value="Restablecer" /><input type="button" value="Cancelar" onclick="ocultar_mostrar_capa('','')" /></td>
		</tr>
        </table>
</form>
		<?php
		
	}
	function dame_obs_jefe($it){
		include("../conf/conexion.php");
		$sql="SELECT obs_asignacion FROM reg_tickets WHERE id=$it LIMIT 1; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					return $reg[0];
				}
			}else{ return " "; }
		} else{ return "<br>Error SQL (".mysql_error($link).").";	}		
	}
	function procesar_guardar($it,$ac,$st){
		//echo "procesar_guardar($it,$a,$s)";	
		// determinar tiempos ...
		$fecha_inicio=$this->dame_dato_fecha($it, 'fecha_inicio');
		$hora_inicio=$this->dame_dato_fecha($it, 'hora_inicio');
		$fecha_inicio_absoluta=$fecha_inicio." ".$hora_inicio;
		
		$fecha_actual=date("Y-m-d");
		$hora_actual=date("H:i:s");
		$fecha_actual_absoluta=$fecha_actual." ".$hora_actual;
			$s = strtotime($fecha_actual_absoluta)-strtotime($fecha_inicio_absoluta);
			$d = intval($s/86400);
			$s -= $d*86400;
			$h = intval($s/3600);
			$s -= $h*3600;
			$m = intval($s/60);
			$s -= $m*60;
			
			$dif= (($d*24)+$h).hrs." ".$m."min";
			$dif2= $d.$space.dias." ".$h.hrs." ".$m."min";
			
			$horas_netas=$d*24+$h;
			if ($horas_netas<1) $horas_netas=1;
		// revisar los posibles status para colocar o no la fecha fin [PENDIENTE/FINALIZADO]...
		($st=="FINALIZADO")? $ff=",fecha_fin='".date("Y-m-d")."',hora_fin='".date("H:i:s")."',horas_consumidas='$horas_netas'":$ff=",fecha_hora_pausa='$fecha_actual_absoluta',horas_consumidas='$horas_netas'";
		
		// --------- SQL -----------------
		//echo "<small>FI ($fecha_inicio) HI ($hora_inicio) === FA ($fecha_actual) HA ($hora_actual) === Dias ($dif2) Horas ($horas_netas)</small>";
		//echo "<br>".
		$sql="UPDATE reg_tickets SET acciones='$ac',status='$st'$ff WHERE id=$it LIMIT 1; ";
		
		if($this->ejecuta_sql($sql)){
			echo "<br><div align='center'>Ticket $it: datos guardados correctamente.</div>";
			?><script language="javascript"> listar_tic(); </script><?php
		}
	}
	function dame_dato_fecha($it,$par1){
		include("../conf/conexion.php");
		$sql="SELECT fecha_inicio,hora_inicio FROM reg_tickets WHERE id=$it LIMIT 1; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					//return $reg[0]." ".$reg[1];
					if($par1=="fecha_inicio") return $reg[0];
					if($par1=="hora_inicio") return $reg[1];
				}
			}else{ return " "; }
		} else{ return "<br>Error SQL (".mysql_error($link).").";	}		
	}
	function dame_tipo_ticket($tipo_ticket){
		include("../conf/conexion.php");
		$sql="SELECT descripcion FROM cat_tipo_ticket WHERE id=$tipo_ticket LIMIT 1; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					return $reg[0];
				}
			}else{ return " "; }
		} else{ return "<br>Error SQL (".mysql_error($link).").";	}	
	}
	function ver_mis_tickets(){
		//print_r($_SESSION);
		include("../conf/conexion.php");
		
		$sql="SELECT id,fecha,id_usuario,area,tema,status,atiende,evaluado FROM reg_tickets WHERE id_usuario='".$_SESSION['usuario_id']."' ORDER BY id DESC; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){
				?>
                <div id="div_ticket_detalle"></div>
                <div id="div_ticket_lista">
                <h3 align='center'>mis tickets</h3>
                <table align="center" width="95%" cellspacing="0" cellpadding="3" class="tabla_bordes">
                <tr>
                    <th>#</th>
                    <th>fecha</th>
                    <th>area de servicio</th>
                    <th>usuario</th>
                    <th>tema</th>
                    <th>status</th>
                    <th>acciones</th>
                </tr><?php
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?>
					<tr>
						<td align="center"><?=$reg["id"]?></td>
						<td align="center"><?=$reg["fecha"]?></td>
						<td><?php
                        	//echo $reg["area"];
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($reg["area"]);
						?></td>
						<td><?php
                        	//echo $reg["id_usuario"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							echo " ".$u1->dame_nombre_usuario($reg["id_usuario"]);
						?></td>
						<td>&nbsp;<?php
                        //$reg["tema"];
						(strlen($reg["tema"])>10)? $t2=substr($reg["tema"],0,10)."..." : $t2=$reg["tema"];
						echo $t2;																	   
						?></td>
					  <td style="font-size:small;">&nbsp;<?=$reg["status"]?></td>
                        <td align="center" style="font-size:small;">
                        	<a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=ver_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">ver</a> 
							<?php if($reg["evaluado"]==0&&$reg["status"]=='FINALIZADO'){ ?>
							| <a href="#" class="link_02" onclick="fn_evaluar_ticket(<?=$reg["id"]?>)">evaluar</a>
							<?php } ?>
                        </td>
                    </tr><?php					
				}
				?></table></div><?php
			}else{ echo "<br><div align='center'>Usted no ha generado &Oacute;rdenes de Servicio.</div>"; }
		} else{ echo "<br>Error SQL (".mysql_error($link).").";	}		
		
	}
	function ver_mis_tickets_atender(){
		//echo "<br>ver_mis_tickets_atender()<br>";
		include("../conf/conexion.php");
		$sql="SELECT id,fecha,id_usuario,area,tema,status,atiende,descripcion FROM reg_tickets WHERE atiende='".$_SESSION["usuario_id"]."' ORDER BY id DESC; ";
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){
				?>
                <div id="div_ticket_detalle"></div>
                <div id="div_ticket_lista">
                <h3 align='center'>mis tickets asignados</h3>
                <table align="center" width="95%" cellspacing="0" cellpadding="3" class="tabla_bordes">
                <tr>
                    <th>#</th>
                    <th>fecha</th>
                    <th>area de servicio</th>
                    <th>usuario</th>
                    <th>tema</th>
                    <th>status</th>
                    <th>acciones</th>
                </tr><?php
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					?>
					<tr>
						<td align="center"><?=$reg["id"]?></td>
						<td align="center"><?=$reg["fecha"]?></td>
						<td><?php
                        	//echo $reg["area"];
							require_once("area.php");
							$ax=new area();
							echo " ".$ax->dame_nombre_area($reg["area"]);
						?></td>
						<td><?php
                        	//echo $reg["id_usuario"];
							require_once("../clases/usuario.php");
							$u1=new usuario();
							$usuario_solicita=$u1->dame_nombre_usuario($reg["id_usuario"]);
							echo " ".$usuario_solicita;
						?></td>
						<td>&nbsp;<?php
                        //$reg["tema"];
						(strlen($reg["tema"])>10)? $t2=substr($reg["tema"],0,10)."..." : $t2=$reg["tema"];
						echo $t2;																	   
						?></td>
					  <td style="font-size:small;">&nbsp;<?=$reg["status"]?></td>
                        <td align="center" style="font-size:small;">
<a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=ver_ticket&it=<?=$reg["id"]?>','div_ticket_lista')">ver</a> | 
<? if($reg["status"]=="EN PROCESO"||$reg["status"]=="PENDIENTE"){ ?>
 <a href="#" class="link_02" onclick="ajax('div_ticket_detalle','ac=procesar_ticket&it=<?=$reg["id"]?>&usuario=<?=$usuario_solicita?>','div_ticket_lista')">procesar</a><?php
}else{ echo "procesar"; }
?>	</td>
                    </tr><?php					
				}
				?></table></div><?php
			}else{ echo "<br><div align='center'>Usted no tiene &Oacute;rdenes de Servicio asignadas.</div>"; }
		} else{ echo "<br>Error SQL (".mysql_error($link).").";	}		
		
	}
	function nuevo_tipo_ticket($tipo,$horas,$requiere_materiales){
		//echo "<br>".
		$sql="INSERT INTO cat_tipo_ticket (id,id_area,descripcion,horas_definidas,requiere_materiales,obs) VALUES (null,'".$_SESSION["usuario_grupo"]."','$tipo','$horas','$requiere_materiales','--'); ";
		include("../conf/conexion.php");
		if (mysql_db_query($db_actual,$sql,$link)){ 
			$id_insertado=mysql_insert_id($link);
			//echo "<div class='mensaje_01'>Datos guardados correctamente ($id_insertado).</div>";
			?>
			<script language="javascript">
				$("#sel_tipo_ticket").append("<option value='<?=$id_insertado?>'><?=$id_insertado." - ".$tipo?></option>");
			</script>
			<?php
		} else{ 
			echo "<br><div class='mensaje_01'>Error SQL (".mysql_error($link).").</div>";	
		}
	}
	function ticket_cancelar($it,$obs){
		$sql="UPDATE reg_tickets SET status='CANCELADO',fecha_fin='".date("Y-m-d")."',hora_fin='".date("H:i:s")."',obs='$obs' WHERE id=$it LIMIT 1; ";
		//echo "<br>ticket_cancelar($it) [$sql]";
		if($this->ejecuta_sql($sql)){
			echo "<h4 align='center'>El ticket $it fue CANCELADO.</h4>";
		}else{
			echo "<h4 align='center'>ADVERTENCIA : El ticket $it NO fue CANCELADO.</h4>";
		}
	}
	protected function dame_no_resultados($sql){
		include("../conf/conexion.php");
		if ($res=mysql_db_query($db_actual,$sql,$link)){ 
			$ndr=mysql_num_rows($res);
			if($ndr>0){	
				while($reg=mysql_fetch_array($res)){
					//echo "<br>"; 	print_r($reg);
					return $reg[0];
				}
			}else{ return " "; }
		} else{ return "<br>Error SQL (".mysql_error($link).").";	}		
	}	
	protected function ejecuta_sql($sql){
		include("../conf/conexion.php");
		if (mysql_db_query($db_actual,$sql,$link)){ return true;
		} else{ echo "<br>Error SQL (".mysql_error($link).").";	return false; }
	}  
}
?>