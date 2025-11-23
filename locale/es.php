<?php

$langArray = array(

    "header" => "Ejercicios TCP",
    "notes" => "
			<h3>Notas importantes:</h3>
			<ol>
				<li>Esta web está en fase de pruebas y puede contener errores. Si se detecta algún error, enviar un correo a <a href=\"mailto:jaime.garciareinoso@uah.es\">jaime.garciareinoso@uah.es</a></li>
				<li>Se recomienda hacer el ejercicio en papel y luego pasarlo al formulario del ejercicio.</li>
				<li>Si algún campo de la cabecera TCP no se envía en un segmento, dejar el campo vacío</li>
			  <li>Una vez enviada tu respuesta, se marca en verde los campos correctos y en rojo los incorrectos. Si tienes tres errores o más, se dejará de corregir la respuesta y se marcará en amarillo las respuestas no corregidas.</li>
			  <li>Si tienes algún error, se recomienda darle al botón de \"atrás\" de tu navegador y corregir los campos incorrectos.</li>
              <li>En los ejercicios con control de congestión, sólo rellenar los valores de CWND y Modo cuando alguno de estos cambie.</li>
              <li>El formato de estos ejercicios pueden no estar relacionados con el de los exámenes.</li>
			  <li>Recuerda que tienes el Mapa Mental Aumentado de TCP <a href=\"https://mapamentalar1.web.uah.es/02-canvas/tcp.html\">aquí</a> por si algo no te queda claro.</li>
			</ol>


	 ",
    "enun" => "
           <ul>
        <li>Ejercicios SIN control de congestión</li>

		<ul>
			<li>Ejercicio 1 - Nivel fácil</li>
				<ul>
					<li><a href=\"form.php?langID=es&id=1\">Parte 1</a></li>
					<li><a href=\"form.php?langID=es&id=2\">Parte 2</a></li>
					<li><a href=\"form.php?langID=es&id=3\">Parte 3</a></li>
					<li><a href=\"form.php?langID=es&id=4\">Parte 4</a></li>
					<li><a href=\"form.php?langID=es&id=5\">Parte 5</a></li>
                  <li><a href=\"form.php?langID=es&id=6\">Parte 6</a></li>
				</ul>
			<li>Ejercicio 2 - Nivel medio (Ejercicio 1 de la PP3 del curso 2023-24)</li>
          		<ul>
          			<li><a href=\"form.php?langID=es&id=7\">Parte 1</a></li>
                  <li><a href=\"form.php?langID=es&id=8\">Parte 2</a></li>
                  <li><a href=\"form.php?langID=es&id=9\">Parte 3</a></li>
                  <li><a href=\"form.php?langID=es&id=10\">Parte 4</a></li>
                </ul>
          <li>Ejercicio 3 - Nivel medio (Ejercicio 1 de la PEI2 del curso 2022-23)</li>
          		<ul>
                    <li><a href=\"form.php?langID=es&id=11\">Parte 1</a></li>
                    <li><a href=\"form.php?langID=es&id=12\">Parte 2</a></li>
                    <li><a href=\"form.php?langID=es&id=13\">Parte 3</a></li>
                    <li><a href=\"form.php?langID=es&id=14\">Parte 4</a></li>                
          		</ul>
           <li>Ejercicio 6 - Nivel medio (GP3.3-3.4)</li>
          		<ul>
                    <li><a href=\"form.php?langID=es&id=25\">Parte 1</a></li>
                    <li><a href=\"form.php?langID=es&id=26\">Parte 2</a></li>
                    <li><a href=\"form.php?langID=es&id=27\">Parte 3</a></li>
                    <li><a href=\"form.php?langID=es&id=28\">Parte 4</a></li>                
          			<li><a href=\"form.php?langID=es&id=29\">Parte 5</a></li> 
                </ul>
            <li>Ejercicio 7 - Nivel medio (Ejercicio 1 de la PEI2 del curso 2025-26)</li>
          		<ul>
                    <li><a href=\"form.php?langID=es&id=30\">Parte 1</a></li>
                    <li><a href=\"form.php?langID=es&id=31\">Parte 2</a></li>
                    <li><a href=\"form.php?langID=es&id=32\">Parte 3</a></li>
                    <li><a href=\"form.php?langID=es&id=33\">Parte 4</a></li>                
                </ul>
		</ul>
        
        <li>Ejercicios CON control de congestión</li>

		<ul>
			<li>Ejercicio 4 - Nivel difícil </li>
				<ul>
					<li><a href=\"form.php?langID=es&id=15\">Parte 1</a></li>
					<li><a href=\"form.php?langID=es&id=16\">Parte 2</a></li>
					<li><a href=\"form.php?langID=es&id=17\">Parte 3</a></li>
					<li><a href=\"form.php?langID=es&id=18\">Parte 4</a></li>
					<li><a href=\"form.php?langID=es&id=19\">Parte 5</a></li>
              
          		</ul>
          <li>Ejercicio 5 - Nivel dios de Arquitectura de Redes I (HTTP+TCP con control de congestión)</li>
          	<ul>
					<li><a href=\"form.php?langID=es&id=20\">Parte 1</a></li>
					<li><a href=\"form.php?langID=es&id=21\">Parte 2</a></li>
					<li><a href=\"form.php?langID=es&id=22\">Parte 3</a></li>
					<li><a href=\"form.php?langID=es&id=23\">Parte 4</a></li>
            
          	</ul>
        </ul>

      </ul>",
    "authors" => "<h3>Autores</h3>		  <ul>
			  <li>Jaime García Reinoso: idea original e implementación de la funcionalidad básica.</li>
               <li>Enrique de la Hoz de la Hoz: implementación del control de congestión, panel de administración, ideas de mejora y seguridad.</li>
			  <li>Marino Tejedor Romero: ideas de mejora, javascript, CSS y pruebas.</li>
			  <li>Luis de la Cruz Piris: ideas de mejora.</li>
			  <li>Joaquín Álvarez Horcajo: pruebas.</li>
		  </ul>",

    "checkOK" => "<h2>¡Felicidades! ¡Tu respuesta es correcta!</h2>",
    "check1error" => "<h3>Lo siento, tienes un error. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.</h3>",
    "checkXerror1" => "<h3>Lo siento, tienes ",
    "checkXerror2" => " errores. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.</h3>",
    "check3error" => "<h3>Tienes tres errores o más y por lo tanto no se ha corregido completamente tu respuesta. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.</h3>",
    "back" => "Para volver al índice principal pulsar <a href=\"index.php\">aquí</a>.",

    // --- CLAVES DE MENÚ ---
    "part" => "Parte",
    "exercise" => "Ejercicio",
    "ex_no_congestion_title" => "Ejercicios SIN control de congestión",
    "ex_1_title" => "Ejercicio 1 - Nivel fácil",
    "ex_2_title" => "Ejercicio 2 - Nivel medio (Ejercicio 1 de la PP3 del curso 2023-24)",
    "ex_3_title" => "Ejercicio 3 - Nivel medio (Ejercicio 1 de la PEI2 del curso 2022-23)",
    "ex_7_title" => "Ejercicio 6 - Nivel medio (GP3.4)",
    "ex_8_title" => "Ejercicio 7 -=> Nivel medio (Ejercicio 1 de la PEI2 del curso 2024-25)",
    "ex_with_congestion_title" => "Ejercicios CON control de congestión",
    "ex_4_title" => "Ejercicio 4 - Nivel difícil",
    "ex_5_title" => "Ejercicio 5 - Nivel dios de Arquitectura de Redes I (HTTP+TCP con control de congestión)",

    // --- RESULTADOS ---
    "correct_answer" => "¡Felicidades! ¡Tu respuesta es correcta!",
    "one_error" => "Lo siento, tienes un error. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.",
    "n_errors" => "Lo siento, tienes {n} errores. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.",
    "many_errors" => "Tienes tres errores o más y por lo tanto no se ha corregido completamente tu respuesta. <br>Inténtalo otra vez dándole al botón de ir atrás en tu navegador.",
    "back_to_index" => "Volver al índice",
);

?>
