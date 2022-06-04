<?php
$GLOBALS['codes']['number'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['a'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;a:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, antepondrá el artículo indefinido inglés "a" o "an" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo indefinido es innecesario: suprimir el modificador.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
<p>2. El contexto determina el género y número del artículo indefinido: anteponerlo y suprimir el modificador.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; un &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; una &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; unos &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; unas &lt;&lt;1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; &lt;&lt;1{un/una/unos/unas}&gt;&gt; &lt;&lt;1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['ma'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;ma:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, antepondrá el adjetivo inglés "some" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>El modificador debe suprimirse.</p>
<pre>&lt;&lt;ma:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['A'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;A:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, antepondrá el artículo definido inglés "the" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo definido es innecesario: suprimir el modificador.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
<p>2. El contexto determina el género y número del artículo definido: anteponerlo y suprimir el modificador.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; el &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; la &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; los &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; las &lt;&lt;1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; &lt;&lt;1{el/la/los/las}&gt;&gt; &lt;&lt;1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['c'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;c:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá la primera letra del nombre en minúscula.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;c:1&gt;&gt; &rArr; &lt;&lt;c:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['C'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;C:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá la primera letra del nombre en mayúscula.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;C:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['m'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;m:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, mostrará la forma singular o plural del nombre dependiendo del sufijo. Si el sufijo es ^p, no hará nada y si el sufijo no es ^p o carece de sufijo, mostrará el plural. Aplica las reglas de formación del plural del inglés.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse (*).</p>
<pre>&lt;&lt;m:1&gt;&gt; &rArr; &lt;&lt;m:1&gt;&gt;</pre>
<p>(*) Se sigue estudiando cómo traducir esté código. Hasta el momento no se ha encontrado una solución óptima. </p>
EOC;

$GLOBALS['codes']['n'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;n:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código por un número cardinal en inglés escrito con minúscula (one, two, three, ..., twelve). No funciona para número mayores que doce.</p>
<p><u>Transcripción</u></p>
<p>El modificador debe suprimirse.</p>
<pre>&lt;&lt;n:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['N'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;N:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código por un número cardinal en inglés escrito con mayúscula (One, Two, Three, ..., Twelve). No funciona para número mayores de doce.</p>
<p><u>Transcripción</u></p>
<p>El modificador debe suprimirse.</p>
<pre>&lt;&lt;N:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['R'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;R:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código por un número cardinal romano (I, II, III, IV, etc).</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;R:1&gt;&gt; &rArr; &lt;&lt;R:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['t'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;t:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá en mayúscula la primera letra de todas las palabras del nombre que tengan dos o más letras.</p>
<p><u>Transcripción</u></p>
<p>El código debe cambiarse para convertir en mayúscula únicamente la primera letra de la primera palabra del nombre.</p>
<pre>&lt;&lt;t:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['T'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;T:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá en mayúscula la primera letra de todas las palabras del nombre.</p>
<p><u>Transcripción</u></p>
<p>El código debe cambiarse para convertir en mayúscula únicamente la primera letra de la primera palabra del nombre.</p>
<pre>&lt;&lt;T:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['X'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;X:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, mostrará el nombre con los sufijos que pudiera tener.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;X:1&gt;&gt; &rArr; &lt;&lt;X:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['z'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;z:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá todas las letras del nombre en minúsculas.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;z:1&gt;&gt; &rArr; &lt;&lt;z:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['Z'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Z:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá todas las letras del nombre en mayúsculas.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;Z:1&gt;&gt; &rArr; &lt;&lt;Z:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['Ac'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Ac:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá la primera letra del nombre en minúscula y antepondrá el artículo definido inglés "the" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo definido es innecesario: suprimir el modificador «A».</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; &lt;&lt;c:1&gt;&gt;</pre>
<p>2. El contexto determina el género y número del artículo definido: anteponerlo y suprimir el modificador «A».</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; el &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; la &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; los &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; las &lt;&lt;c:1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género conservando el modificador «c».</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; &lt;&lt;1{el/la/los/las}&gt;&gt; &lt;&lt;c:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['cA'] = $GLOBALS['codes']['Ac'];

$GLOBALS['codes']['AC'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;AC:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá la primera letra del nombre en mayúscula y antepondrá el artículo definido inglés "the" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo definido es innecesario: suprimir el modificador «A».</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
<p>2. El contexto determina el género y número del artículo definido: anteponerlo y suprimir el modificador «A».</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; el &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; la &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; los &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; las &lt;&lt;C:1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género conservando el modificador «C».</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; &lt;&lt;1{el/la/los/las}&gt;&gt; &lt;&lt;C:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['CA'] = $GLOBALS['codes']['AC'];

$GLOBALS['codes']['tA'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;tA:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá en mayúscula la primera letra de todas las palabras del nombre que tengan dos o más letras, y antepondrá el artículo definido inglés "the" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo definido es innecesario: suprimir el modificador «A» y usar el modificador «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
<p>2. El contexo determina el género y número del artículo definido: anteponerlo, suprimir el modificador «A» y usar el modificador «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; el &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; la &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; los &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; las &lt;&lt;C:1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género y usar el modificador «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; &lt;&lt;1{el/la/los/las}&gt;&gt; &lt;&lt;C:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['At'] = $GLOBALS['codes']['tA'];

$GLOBALS['codes']['Az'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Az:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá todas las letras del nombre en minúsculas y antepondrá el artículo definido inglés "the" si el nombre no es un nombre propio, es decir, no tiene el sufijo ^M, ^F o ^N.</p>
<p><u>Transcripción</u></p>
<p>Existen varias opciones:</p>
<p>1. El contexto determina que el artículo definido es innecesario: suprimir el modificador «A».</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; &lt;&lt;z:1&gt;&gt;</pre>
<p>2. El contexo determina el género y número del artículo definido: anteponerlo y suprimir el modificador «A».</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; el &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; la &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; los &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; las &lt;&lt;z:1&gt;&gt;</pre>
<p>3. El contexto es ambiguo: emplear el código de distinción de género conservando el modificador «z».</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; &lt;&lt;1{el/la/los/las}&gt;&gt; &lt;&lt;z:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['Za'] = $GLOBALS['codes']['Az'];

$GLOBALS['codes']['Cz'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Cz:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá todas las letras del nombre en minúsculas excepto la primera que la convertirá en mayúscula.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse.</p>
<pre>&lt;&lt;Cz:1&gt;&gt; &rArr; &lt;&lt;Cz:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['zC'] = $GLOBALS['codes']['Cz'];

$GLOBALS['codes']['tm'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;tm:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá en mayúscula la primera letra de todas las palabras del nombre que tengan dos o más letras, y mostrará la forma singular o plural del nombre dependiendo del sufijo. Si el sufijo es ^p, no hará nada y si el sufijo no es ^p o carece de sufijo, mostrará el plural. Aplica las reglas de formación del plural del inglés.</p>
<p><u>Transcripción</u></p>
<p>El modificador «t» debe cambiarse para convertir en mayúscula únicamente la primera letra de la primera palabra del nombre. El modificador «m» debe respetarse (*).</p>
<pre>&lt;&lt;tm:1&gt;&gt; &rArr; &lt;&lt;Cm:1&gt;&gt;</pre>
<p>(*) Se sigue estudiando cómo traducir el modificador «m». Hasta el momento no se ha encontrado una solución óptima. </p>
EOC;

$GLOBALS['codes']['mt'] = $GLOBALS['codes']['tm'];

$GLOBALS['codes']['mc'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;mc:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá la primera letra del nombre en minúscula y mostrará la forma singular o plural del nombre dependiendo del sufijo. Si el sufijo es ^p, no hará nada y si el sufijo no es ^p o carece de sufijo, mostrará el plural. Aplica las reglas de formación del plural del inglés.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse (*).</p>
<pre>&lt;&lt;mc:1&gt;&gt; &rArr; &lt;&lt;mc:1&gt;&gt;</pre>
<p>(*) Se sigue estudiando cómo traducir el modificador «m». Hasta el momento no se ha encontrado una solución óptima. </p>
EOC;

$GLOBALS['codes']['cm'] = $GLOBALS['codes']['mc'];

$GLOBALS['codes']['mz'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;mz:1&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego reemplazará este código con el nombre de un personaje, un lugar o un objeto.</p>
<p>Además, convertirá todas las letras del nombre en minúsculas y mostrará la forma singular o plural del nombre dependiendo del sufijo. Si el sufijo es ^p, no hará nada y si el sufijo no es ^p o carece de sufijo, mostrará el plural. Aplica las reglas de formación del plural del inglés.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse (*).</p>
<pre>&lt;&lt;mz:1&gt;&gt; &rArr; &lt;&lt;mz:1&gt;&gt;</pre>
<p>(*) Se sigue estudiando cómo traducir el modificador «m». Hasta el momento no se ha encontrado una solución óptima. </p>
EOC;

$GLOBALS['codes']['zm'] = $GLOBALS['codes']['mz'];

$GLOBALS['codes']['npc'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;npc{<em>masculino</em>/<em>femenino</em>}&gt;&gt;</pre>
<p>El juego seleccionará y presentará la palabra o grupo de palabras de <em>masculino</em> o <em>femenino</em> en función del sexo del personaje.</p>
<p>El sexo del personaje viene determinado por el sufijo ^M o ^F de su nombre propio.</p>
<p><u>Transcripción</u></p>
<p>Este código no se traduce, se usa. Debe hacerse siempre que haya que adecuar el género al sexo de un personaje. Puede usarse tanto en los textos de los personajes cuando hablan de sí mismos, como en los textos del jugador cuando hace referencia al personaje con el que está dialogando.</p>
<p><u>Ejemplos</u></p>
<p>Un personaje dice: <em>I'm sure he's around here</em>.<br />Traducción: <strong>Estoy &lt;&lt;npc{seguro/segura}&gt;&gt; de que está por aquí</strong>.</p>
<p>El jugador dice: <em>You aren't the clevest one, are you?</em><br />Traducción: <strong>No eres &lt;&lt;npc{el más listo/la más lista}&gt;&gt;, ¿verdad?</strong></p>
EOC;

$GLOBALS['codes']['player'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;player{<em>masculino</em>/<em>femenino</em>}&gt;&gt;</pre>
<p>El juego seleccionará y presentará la palabra o grupo de palabras de <em>masculino</em> o <em>femenino</em> en función del sexo del jugador (del personaje que maneja el jugador).</p>
<p>El sexo del jugador se determina en la pantalla de creación de personajes al iniciar una partida.</p>
<p><u>Transcripción</u></p>
<p>Este código no se traduce, se usa. Debe hacerse siempre que haya que adecuar el género al sexo del jugador. Puede usarse tanto en los textos del jugador cuando habla de sí mismo, como en los textos de los personajes cuando hacen referencia al jugador.</p>
<p><u>Ejemplos</u></p>
<p>El jugador dice: <em>I'm sure he's around here</em>.<br />Traducción: <strong>Estoy &lt;&lt;player{seguro/segura}&gt;&gt; de que está por aquí</strong>.</p>
<p>Un personaje dice: <em>You aren't the clevest one, are you?</em><br />Traducción: <strong>No eres &lt;&lt;player{el más listo/la más lista}&gt;&gt;, ¿verdad?</strong></p>
EOC;

$GLOBALS['codes']['number-gender'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1{<em>masculino</em>/<em>femenino</em>}&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>El juego seleccionará y presentará la palabra o grupo de palabras de <em>masculino</em> o <em>femenino</em> en función del sexo del personaje al que se hace referencia.</p>
<p>El sexo del personaje viene determinado por el sufijo ^M o ^F de su nombre propio.</p>
<p><u>Transcripción</u></p>
<p>Este código no se traduce, se usa. Debe hacerse siempre que haya que adecuar el género al sexo de un personaje sin especificar, es decir, al que se hace referencia mediante un código <<1>>. Puede usarse tanto en los textos del jugador como en los textos de los personajes.</p>
<p><u>Ejemplo</u></p>
<p>El jugador o un personaje dice: <em>&lt;&lt;1&gt;&gt; is the clevest one around here</em>.<br />Traducción: <strong>&lt;&lt;1&gt;&gt; es &lt;&lt;1{el más listo/la más lista}&gt;&gt; de por aquí</strong>.</p>
EOC;

$GLOBALS['codes']['numeric-two'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1[<em>uno</em>/<em>varios</em>]&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>Este código se emplea para presentar cantidades y adecuar el número de las palabras.</p>
<p>El código toma como argumento un número que puede aparecer en pantalla si \$d está dentro del código. Si dicho número es 1, se presentará el contenido de <em>uno</em>; si dicho número es mayor que 1, se presentará el contenido de <em>varios</em>.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse. Además, debe traducirse el contenido de <em>uno</em> y <em>varios</em>.</p>
<p><u>Ejemplo</u></p>
<p><em>&lt;&lt;1[\$d second/\$d seconds]&gt;&gt;</em><br />Traducción: <strong>&lt;&lt;1[\$d segundo/\$d segundos]&gt;&gt;</strong></p>
EOC;

$GLOBALS['codes']['numeric-three'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1[<em>ninguno</em>/<em>uno</em>/<em>varios</em>]&gt;&gt;</pre><strong>donde 1 puede ser cualquier otro número</strong>
<p>Este código se emplea para presentar cantidades y adecuar el número de las palabras.</p>
<p>El código toma como argumento un número que puede aparecer en pantalla si \$d está dentro del código. Si dicho número es 0, se presentaré el contenido de <em>ninguno</em>; si dicho número es 1, se presentará el contenido de <em>uno</em>; si dicho número es mayor que 1, se presentará el contenido de <em>varios</em>.</p>
<p><u>Transcripción</u></p>
<p>El código debe respetarse. Además, debe traducirse el contenido de <em>ninguno</em>, <em>uno</em> y <em>varios</em>.</p>
<p><u>Ejemplo</u></p>
<p><em>&lt;&lt;1[0 seconds/1 second/\$d seconds]&gt;&gt;</em><br />Traducción: <strong>&lt;&lt;1[0 segundos/1 segundo/\$d segundos]&gt;&gt;</strong></p>
EOC;

$GLOBALS['codes']['suffix'] = <<<EOC
<pre style="font-size: 16px">Códigos ^M ^F ^N ^m ^f ^n ^s ^p ^d ^a ^z</pre>
<p>Los códigos que comienza con el acento circunflejo son, principalmente aunque no sólo, marcas de género y número. Para el inglés, francés y alemán, el juego interpreta los códigos de la siguiente forma:<br />
<ul>
   <li><strong>^M</strong>, <strong>^F</strong> y <strong>^N</strong>: la primera palabra de la expresión es un nombre propio masculino, femenino o neutro, respectivamente.</li>
   <li><strong>^m</strong>, <strong>^f</strong> y <strong>^n</strong>: la primera palabra de la expresión es un nombre común masculino, femenino o neutro, respectivamente.</li>
   <li><strong>^s</strong> y <strong>^p</strong>: la primera palabra de la expresión es singular o plural, respectivamente. El código ^s es opcional, se usa únicamente cuando se desea evitar la pluralización de nombres incontables.</li>
   <li><strong>^d</strong>: la expresión se presentará siempre con el artículo determinado.</li>
   <li><strong>^a</strong> y <strong>^z</strong>: la expresión se situará, respectivamente, al principio o al final de otra expresión con la que se combina.</li>
</ul>   
</p>
<p><u>Transcripción</u></p>
<p>Estos códigos no pueden traducirse, deben adaptarse a la lengua española. Para hacerlo, debe realizarse un análisis morfológico de la traducción para determinar el género y número de la primera palabra. Una vez realizado, se añadirá alguno de los siguientes códigos:</p>
<ul>
   <li><strong>^M</strong> o <strong>^F</strong> si la primera palabra de la traducción es un nombre propio masculino o femenino, respectivamente: <em>Olaf Ojo-Único^M, Aela^F</em></li>
   <li><strong>^m</strong> si la primera palabra de la traducción es un nombre común masculino singular: <em>granate pulido^m</em></li>
   <li><strong>^ms</strong> como ^m y además evita la pluralización del nombre: <em>oro^ms</em></li>
   <li><strong>^f</strong> si la primera palabra de la traducción es un nombre común femenino singular: <em>hacha de guerra^f</em></li>
   <li><strong>^fs</strong> como ^f y además evita la pluralización del nombre: <em>agua^fs</em></li>
   <li><strong>^np</strong> si la primera palabra de la traducción es un nombre común masculino plural: <em>guanteletes^np</em></li>
   <li><strong>^pf</strong> si la primera palabra de la traducción es un nombre común femenino plural: <em>grebas^pf</em></li>
   <li><strong>^d</strong> y <strong>^a</strong> no se usan en español.</li>     
   <li><strong>^z</strong> se utilizará en la traducción si aparece en el original en <u>francés</u>. El código ^z es incompatible con marcas de género y número: <em>de hierro^z</em></li>
</ul>
EOC;

