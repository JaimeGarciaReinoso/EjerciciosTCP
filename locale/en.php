<?php

$langArray = array(

    "header" => "TCP Exercises",
    "notes" => "<h3>Important Notes:</h3>
  <ol>
    <li>This website is in a testing phase and may contain errors. If any error is detected, please send an email to <a href=\"mailto:jaime.garciareinoso@uah.es\">jaime.garciareinoso@uah.es</a></li>
    <li>It is recommended to complete the exercise on paper and then pass it through the exercise form.</li>
    <li>If a field in the TCP header is not sent in a segment, leave the field empty.</li>
    <li>Once you submit your response, the correct fields will be marked green and incorrect fields red. If you have three or more errors, the correction of your response will stop and uncorrected responses will be marked yellow.</li>
    <li>If you have any error, it is recommended to click on the  \"back\" button in your browser and correct the incorrect fields.</li>
    <li>In the exercises with congestion control, only fill in the CWND and Mode values when any of them change.</li>
    <li>The format of these exercises may not be related to that of the exams.</li>
    <li>Remember that you have the Enhanced Mental Map of TCP <a href=\"https://mapamentalar1.web.uah.es/02-canvas/tcp-English.html\">here</a> if anything remains unclear.</li>
  </ol>",

  "authors" => "<h3>Authors</h3><ul>
      <li>      Jaime García Reinoso: original idea and basic functionality implementation.</li>
      <li>Enrique de la Hoz de la Hoz: congestion control implementation, admin environment, improvement ideas and security.</li>
      <li>Marino Tejedor Romero: improvement ideas, javascript, CSS and testing</li>
      <li>Luis de la Cruz Piris: improvement ideas.</li>
      <li>Joaquín Álvarez Horcajo: testing.</li></ul>",
  "enun" => "<ul>
    <li>TCP Exercises without congestion control</li>
      <ul>
      <li>Exercise 1 - Easy Level</li>
        <ul>
          <li><a href=\"form.php?langID=en&id=1\">Part 1</a></li>
          <li><a href=\"form.php?langID=en&id=2\">Part 2</a></li>
          <li><a href=\"form.php?langID=en&id=3\">Part 3</a></li>
          <li><a href=\"form.php?langID=en&id=4\">Part 4</a></li>
          <li><a href=\"form.php?langID=en&id=5\">Part 5</a></li>
          <li><a href=\"form.php?langID=en&id=6\">Part 6</a></li>
        </ul>
      <li>Exercise 2 - Medium Level (Exercise 1 of the PP3 course for the 2023-24 academic year)</li>
        <ul>
          <li><a href=\"form.php?langID=en&id=7\">Part 1</a></li>
          <li><a href=\"form.php?langID=en&id=8\">Part 2</a></li>
          <li><a href=\"form.php?langID=en&id=9\">Part 3</a></li>
          <li><a href=\"form.php?langID=en&id=10\">Part 4</a></li>
        </ul>
      <li>Exercise 3 - Medium Level (Exercise 1 of the PEI2 course for the 2022-23 academic year)</li>
        <ul>
          <li><a href=\"form.php?langID=en&id=11\">Part 1</a></li>
          <li><a href=\"form.php?langID=en&id=12\">Part 2</a></li>
          <li><a href=\"form.php?langID=en&id=13\">Part 3</a></li>
          <li><a href=\"form.php?langID=en&id=14\">Part 4</a></li>
        </ul>
      
      <li>Exercise 6 - Medium level (GP3.3-3.4)</li>
          		<ul>
                    <li><a href=\"form.php?langID=en&id=25\">Parte 1</a></li>
                    <li><a href=\"form.php?langID=en&id=26\">Parte 2</a></li>
                    <li><a href=\"form.php?langID=en&id=27\">Parte 3</a></li>
                    <li><a href=\"form.php?langID=en&id=28\">Parte 4</a></li> 
                    <li><a href=\"form.php?langID=en&id=29\">Parte 5</a></li> 

          		</ul>
       <li>Ejercicio 7 - Medium level (Exercise 1 of the PEI2 exam for the 2025-26 academic year)</li>
          		<ul>
                    <li><a href=\"form.php?langID=en&id=30\">Parte 1</a></li>
                    <li><a href=\"form.php?langID=en&id=31\">Parte 2</a></li>
                    <li><a href=\"form.php?langID=en&id=32\">Parte 3</a></li>
                    <li><a href=\"form.php?langID=en&id=33\">Parte 4</a></li>                
		</ul>
    </ul>

    <li>TCP Exercises with congestion control</li>

    <ul>
      <li>Exercise 4 - Hard Level</li>
        <ul>
          <li><a href=\"form.php?langID=en&id=15\">Part 1</a></li>
          <li><a href=\"form.php?langID=en&id=16\">Part 2</a></li>
          <li><a href=\"form.php?langID=en&id=17\">Part 3</a></li>
          <li><a href=\"form.php?langID=en&id=18\">Part 4</a></li>
          <li><a href=\"form.php?langID=en&id=19\">Part 5</a></li>
        </ul>
      <li>Exercise 5 - Network Architecture god Level (HTTP+TCP with congestion control for the Architecture of Computer Networks I course)</li>
        <ul>
          <li><a href=\"form.php?langID=en&id=20\">Part 1</a></li>
          <li><a href=\"form.php?langID=en&id=21\">Part 2</a></li>
          <li><a href=\"form.php?langID=en&id=22\">Part 3</a></li>
          <li><a href=\"form.php?langID=en&id=23\">Part 4</a></li>
        </ul>
    </ul>",
  'exercise' => "Exercise",
  'part' => "Part",
  'checkOK' => "<h2>Congratulations! Everything is correct!</h2>",
  "check1error" => "<h3>Sorry, there is one error. <br>Try it again by clicking the back button in your browser.</h3>",
  'checkXerror1' => "<h3>Sorry, you have ",
  'checkXerror2' => " error(s). <br>Try it again going back to the previous screen.</h3>",
  'check3error' => "<h3>You have three or more errors, so your answer is not completely reviewed. <br>Try it again going back to the previous screen.</h3>",
  'back' => "To go back to the main page click <a href=\"index.php\">here</a>",

    "part" => "Part",
    "exercise" => "Exercise",
    "ex_no_congestion_title" => "Exercises WITHOUT congestion control",
    "ex_1_title" => "Exercise 1 - Easy level",
    "ex_2_title" => "Exercise 2 - Medium level (Exercise 1 from PP3 course 2023-24)",
    "ex_3_title" => "Exercise 3 - Medium level (Exercise 1 from PEI2 course 2022-23)",
    "ex_7_title" => "Exercise 6 - Medium level (GP3.4)",
    "ex_8_title" => "Exercise 7 -=> Medium level (Exercise 1 from PEI2 course 2024-25)",
    "ex_with_congestion_title" => "Exercises WITH congestion control",
    "ex_4_title" => "Exercise 4 - Hard level",
    "ex_5_title" => "Exercise 5 - Network Architecture god Level (HTTP+TCP from the Architecture of Computer Networks I course)",

    // --- RESULTS ---
    "correct_answer" => "Congratulations! Everything is correct!",
    "one_error" => "Sorry, there is one error. <br>Try it again by clicking the back button in your browser.",
    "n_errors" => "Sorry, you have {n} error(s). <br>Try it again going back to the previous screen.",
    "many_errors" => "You have three or more errors, so your answer is not completely reviewed. <br>Try it again going back to the previous screen.",
    "back_to_index" => "Back to index",
);
?>