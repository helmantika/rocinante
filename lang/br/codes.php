<?php
$GLOBALS['codes']['number'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['a'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;a:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Se esse nome não for um nome próprio, isto é, se não terminar com ^M, ^F ou ^N, o artigo indefinido do português “um” ou “uma” (no inglês “a” ou “an”) será colocado antes do nome.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo indefinido, que deve ser removido. Nesse caso, o modificador do código deve ser removido.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número dos artigos indefinidos. Nesse caso, escreva o artigo antes do código e apague o modificador.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; um &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; uma &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; uns &lt;&lt;1&gt;&gt;
&lt;&lt;a:1&gt;&gt; &rArr; umas &lt;&lt;1&gt;&gt;</pre>
<p>3. O contexto é ambíguo (possui duplo sentido). Nesse caso específico, o código de diferenciação de gênero deve ser usado.</p>
<pre>&lt;&lt;a:1&gt;&gt; &rArr; &lt;&lt;1{um/uma/uns/umas}&gt;&gt; &lt;&lt;1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['ma'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;ma:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Se esse nome não for um nome próprio, isto é, se não terminar com ^M, ^F ou ^N, o adjetivo do inglês “some(algum)” será colocado antes do nome.</p>
<p><u>Transcrição</u></p>
<p>O modificador do código precisa ser apagado.</p>
<pre>&lt;&lt;ma:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['A'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;A:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Se esse nome não for um nome próprio, isto é, se não terminar com ^M, ^F ou ^N, o artigo definido do inglês “the” será colocado antes do nome.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo definido, dessa forma o modificador do código deve ser removido.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número no artigo definido. Nesse caso, escreva o artigo antes do código e apague o modificador.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; o &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; a &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; os &lt;&lt;1&gt;&gt;
&lt;&lt;A:1&gt;&gt; &rArr; as &lt;&lt;1&gt;&gt;</pre>
<p>3. O contexto é ambíguo (possui duplo sentido). Nesse caso específico, o código de diferenciação de gênero deve ser usado.</p>
<pre>&lt;&lt;A:1&gt;&gt; &rArr; &lt;&lt;1{o/a/os/as}&gt;&gt; &lt;&lt;1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['c'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;c:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra do nome será convertida para minúsculo.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;c:1&gt;&gt; &rArr; &lt;&lt;c:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['C'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;C:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra do nome será convertida para maiúscula.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;C:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['m'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;m:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A forma plural ou singular do nome será mostrada. Dependendo do modificador. Se o modificador for ^p, o nome não será modificado. Se o modificador não for ^p ou não tiver nenhum modificador, a forma plural será mostrada. As regras de plural do inglês serão mostradas.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração (*).</p>
<pre>&lt;&lt;m:1&gt;&gt; &rArr; &lt;&lt;m:1&gt;&gt;</pre>
<p>(*) A transcrição desse código ainda está sob pesquisa. Uma solução otimizada ainda não foi encontrada.</p>
EOC;

$GLOBALS['codes']['n'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;n:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um numeral cardinal do inglês escrito em minúsculo (one, two, three… twelve). Esse código só funciona para números menores que “twelve”.</p>
<p><u>Transcrição</u></p>
<p>O modificador do código deve ser apagado.</p>
<pre>&lt;&lt;n:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['N'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;N:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um numeral do inglês escrito com a inicial maiúscula (One, Two, Three… Twelve). Esse código só funciona para números menores que “Twelve”.</p>
<p><u>Transcrição</u></p>
<p>O modificador do código deve ser apagado.</p>
<pre>&lt;&lt;N:1&gt;&gt; &rArr; &lt;&lt;1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['R'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;R:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir o código com um numero romano (I, II, III, IV, e assim vai).</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;R:1&gt;&gt; &rArr; &lt;&lt;R:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['t'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;t:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra de cada palavra que possui mais de duas letras será convertida para maiúscula.</p>
<p><u>Transcrição</u></p>
<p>Esse código precisa ser alterado para converter somente a inicial de nomes para maiúscula.</p>
<pre>&lt;&lt;t:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['T'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;T:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra de cada palavra será convertida para maiúsculo.</p>
<p><u>Transcrição</u></p>
<p>Esse código precisa ser alterado para converter somente a inicial de nomes para maiúscula.</p>
<pre>&lt;&lt;T:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['X'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;X:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Se o nome tiver modificadores, ele será mostrado com eles.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;X:1&gt;&gt; &rArr; &lt;&lt;X:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['z'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;z:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Cada letra do nome será transcrita para minúscula.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;z:1&gt;&gt; &rArr; &lt;&lt;z:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['Z'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Z:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Cada letra do nome será convertida para maiúscula.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;Z:1&gt;&gt; &rArr; &lt;&lt;Z:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['Ac'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Ac:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra do nome será convertida para minúscula se o nome não for nome próprio, dessa forma, se ele não terminar com ^M, ^F, or ^N, o artigo definido do inglês “the” será colocado antes do nome.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo definido, nesse caso o modificador «A» precisa ser apagado.</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; &lt;&lt;c:1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número do artigo definido. Nesse caso escreva o artigo antes do código e apague o modificador «A».</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; o &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; a &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; os &lt;&lt;c:1&gt;&gt;
&lt;&lt;Ac:1&gt;&gt; &rArr; as &lt;&lt;c:1&gt;&gt;</pre>
<p>3. Contexto é ambíguo (possui duplo sentido). Nesse caso, a diferenciação de gênero precisa ser usada e o modificador «c» precisa ser mantido.</p>
<pre>&lt;&lt;Ac:1&gt;&gt; &rArr; &lt;&lt;1{o/a/os/as}&gt;&gt; &lt;&lt;c:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['cA'] = $GLOBALS['codes']['Ac'];

$GLOBALS['codes']['AC'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;AC:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra do nome será convertida para maiúscula e se o nome não for nome próprio, ou se não terminar com ^M, ^F, or ^N, o artigo definido do inglês "the" será colocado antes do nome.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo definido, nesse caso o modificador «A» precisa ser apagado.</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número do artigo definido. Nesse caso escreva o artigo antes do código e apague o modificador «A».</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; o &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; a &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; os &lt;&lt;C:1&gt;&gt;
&lt;&lt;AC:1&gt;&gt; &rArr; as &lt;&lt;C:1&gt;&gt;</pre>
<p>3. Contexto é ambíguo (possui duplo sentido). Nesse caso, a diferenciação de gênero precisa ser usada e o modificador «C» precisa ser mantido.</p>
<pre>&lt;&lt;AC:1&gt;&gt; &rArr; &lt;&lt;1{o/a/os/as}&gt;&gt; &lt;&lt;C:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['CA'] = $GLOBALS['codes']['AC'];

$GLOBALS['codes']['tA'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;tA:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>A primeira letra de cada palavra que possuir mais de duas letras serão convertidas em maiúscula e se o nome não for próprio, ou se não terminar com ^M, ^F, or ^N, o artigo definido do inglês "the" será colocado antes dele.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo definido, nesse caso o modificador «tA» precisa ser trocado por «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; &lt;&lt;C:1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número do artigo definido. Nesse caso escreva o artigo antes do código e troque o modificador «tA» pelo modificador «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; o &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; a &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; os &lt;&lt;C:1&gt;&gt;
&lt;&lt;tA:1&gt;&gt; &rArr; as &lt;&lt;C:1&gt;&gt;</pre>
<p>3. Contexto é ambíguo (possui duplo sentido). Nesse caso, o código de diferenciação de gênero precisa ser usado e o modificador «tA» deve ser substituído pelo modificador «C».</p>
<pre>&lt;&lt;tA:1&gt;&gt; &rArr; &lt;&lt;1{o/a/os/as}&gt;&gt; &lt;&lt;C:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['At'] = $GLOBALS['codes']['tA'];

$GLOBALS['codes']['Az'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Az:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Cada letra do nome será convertida em minúscula se o nome não for próprio, ou se não terminar com ^M, ^F, or ^N, o artigo definido do inglês "the" será colocado antes dele.</p>
<p><u>Transcrição</u></p>
<p>Existem três opções:</p>
<p>1. O contexto gera o artigo definido, nesse caso o modificador «A» precisa ser apagado.</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; &lt;&lt;z:1&gt;&gt;</pre>
<p>2. O contexto delimita gênero e número do artigo definido. Nesse caso escreva o artigo antes do código e apague o modificador «A».</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; o &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; a &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; os &lt;&lt;z:1&gt;&gt;
&lt;&lt;Az:1&gt;&gt; &rArr; as &lt;&lt;z:1&gt;&gt;</pre>
<p>6. Contexto é ambíguo (possui duplo sentido). Nesse caso, a o código de diferenciação de gênero precisa ser usado, o modificador «A» precisa ser apagado e o modificador «z» precisa ser mantido.</p>
<pre>&lt;&lt;Az:1&gt;&gt; &rArr; &lt;&lt;1{o/a/os/as}&gt;&gt; &lt;&lt;z:1&gt;&gt;</pre>     
EOC;

$GLOBALS['codes']['Za'] = $GLOBALS['codes']['Az'];

$GLOBALS['codes']['Cz'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;Cz:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Além disso, cada letra do nome será convertida para minúscula, exceto a primeira, que será convertida para maiúscula.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração.</p>
<pre>&lt;&lt;Cz:1&gt;&gt; &rArr; &lt;&lt;Cz:1&gt;&gt;</pre>
EOC;

$GLOBALS['codes']['zC'] = $GLOBALS['codes']['Cz'];

$GLOBALS['codes']['tm'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;tm:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Além disso, a primeira letra de cada palavra que tem mais de duas letras será convertida para maiúscula e a forma singular ou plural será mostrada. Isso depende do modificador do nome. Se o modificador é ^p, o nome não será modificado. Se o modificador não é ^p ou se não houver nenhum modificador, a forma plural será mostrada. As regras da forma plural em inglês serão aplicadas.</p>
<p><u>Transcrição</u></p>
<p>Este código deve ser alterado a fim de converter apenas a primeira letra do nome para maiúscula. Mantenha o modificador de código «m» (*).</p>
<pre>&lt;&lt;tm:1&gt;&gt; &rArr; &lt;&lt;Cm:1&gt;&gt;</pre>
<p>(*) A transcrição deste código ainda está sob pesquisa. Uma solução mais adequada ainda não foi encontrada.</p>
EOC;

$GLOBALS['codes']['mt'] = $GLOBALS['codes']['tm'];

$GLOBALS['codes']['mc'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;mc:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Além disso, a primeira letra do nome será convertida para a forma minúscula e a forma singular ou plural será mostrada. Isso depende do modificador do nome. Se o modificador é ^p, o nome não será modificado. Se o modificador não é ^p ou se não houver nenhum modificador, a forma plural será mostrada. As regras da forma plural em inglês serão aplicadas.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração (*).</p>
<pre>&lt;&lt;mc:1&gt;&gt; &rArr; &lt;&lt;mc:1&gt;&gt;</pre>
<p>(*) A transcrição deste código ainda está sob pesquisa. Uma solução mais adequada ainda não foi encontrada.</p>
EOC;

$GLOBALS['codes']['cm'] = $GLOBALS['codes']['mc'];

$GLOBALS['codes']['mz'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;mz:1&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo irá substituir esse código com um nome de personagem, lugar ou objeto.</p>
<p>Além disso, cada letra do nome será convertida para a forma minúscula e a forma singular ou plural será mostrada. Isso depende do modificador do nome. Se o modificador é ^p, o nome não será modificado. Se o modificador não é ^p ou se não houver nenhum modificador, a forma plural será mostrada. As regras da forma plural em inglês serão aplicadas.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da mesma forma, mantenha sem alteração (*).</p>
<pre>&lt;&lt;mz:1&gt;&gt; &rArr; &lt;&lt;mz:1&gt;&gt;</pre>
<p>(*) A transcrição deste código ainda está sob pesquisa. Uma solução mais adequada ainda não foi encontrada.</p>
EOC;

$GLOBALS['codes']['zm'] = $GLOBALS['codes']['mz'];

$GLOBALS['codes']['npc'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;npc{<em>male</em>/<em>female</em>}&gt;&gt;</pre>
<p>O jogo vai selecionar as palavras escritas no slot <em>male</em> ou <em>female</em> baseado no sexo do personagem não jogável.</p>
<p>O sexo do personagem é marcado em seu nome próprio pela utilização dos modificadores ^M ou ^F.</p>
<p><u>Transcrição</u></p>
<p>Este código não é transcrito. Ele é usado para concordar o gênero de uma palavra com o sexo de um personagem. Ele pode ser inserido em linhas onde um NPC fala sobre si mesmo ou onde o jogador fala com um NPC.</p>
<p><u>Exemplo</u></p>
<p>Um NPC diz: <em>I'm sure he's around here</em>.<br />Tradução: <strong>Estou &lt;&lt;npc{certo/certa}&gt;&gt; de que ele está por aqui</strong>.</p>
<p>O jogador diz: <em>You aren't the clevest one, are you?</em><br />Tradução: <strong>Você não é &lt;&lt;npc{o/a}&gt;&gt; mais inteligente, não é?</strong></p>
EOC;

$GLOBALS['codes']['player'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;player{<em>male</em>/<em>female</em>}&gt;&gt;</pre>
<p>O jogo vai selecionar as palavras escritas no slot <em>male</em> ou <em>female</em> baseado no sexo do personagem do jogador.</p>
<p>O sexo do personagem do jogador é escolhido na tela de criação de personagem no começo do jogo.</p>
<p><u>Transcrição</u></p>
<p>Este código não é transcrito. Ele é usado para concordar o gênero de uma palavra com o sexo do personagem do jogador. Ele pode ser inserido em linhas onde o jogador fala de si mesmo ou de um NPC falando com o jogador.</p>
<p><u>Exemplo</u></p>
<p>O jogador diz: <em>I'm sure he's around here</em>.<br />Tradução: <strong>Estou &lt;&lt;player{certo/certa}&gt;&gt; de que ele está por aqui</strong>.</p>
<p>Um NPC diz: <em>You aren't the clevest one, are you?</em><br />Tradução: <strong>Você não é &lt;&lt;player{o/a}&gt;&gt; mais inteligente, não é?</strong></p>
EOC;

$GLOBALS['codes']['number-gender'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1{<em>male</em>/<em>female</em>}&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>O jogo vai selecionar as palavras escritas no slot <em>male</em> ou <em>female</em> baseado no sexo do personagem que é representado pelo número.</p>
<p>O sexo do personagem é marcado em seu nome próprio pela utilização dos modificadores ^M ou ^F.</p>
<p><u>Transcrição</u></p>
<p>Este código não é transcrito. Ele é usado para concordar o gênero de uma palavra com o sexo de um personagem desconhecido, ou seja, um personagem que seja referido pelo código <<1>>. Ele pode ser inserido em qualquer tipo de linha.</p>
<p><u>Exemplo</u></p>
<p>O jogador ou um NPC diz: <em>&lt;&lt;1&gt;&gt; is the clevest one around here</em>.<br />Tradução: <strong>&lt;&lt;1&gt;&gt; é &lt;&lt;1{o/a}&gt;&gt; mais inteligente daqui</strong>.</p>
EOC;

$GLOBALS['codes']['numeric-two'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1[<em>one</em>/<em>some</em>]&gt;&gt;</pre><strong> onde 1 pode ser outro número</strong>
<p>Este código é usado para mostrar números juntamente com palavras.</p>
<p>Se \$d  está dentro do código, um número será mostrado junto com a palavra. Se o número for 1, palavras escritas no slot <em>one</em> serão mostradas. Se o número for maior que 1, palavras escritas no slot <em>some</em> serão mostradas.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da exata forma, com exceção dos slots. Eles precisam ser traduzidos.</p>
<p><u>Exemplo</u></p>
<p><em>&lt;&lt;1[\$d second/\$d seconds]&gt;&gt;</em><br />Tradução: <strong>&lt;&lt;1[\$d segundo/\$d segundos]&gt;&gt;</strong></p>
EOC;

$GLOBALS['codes']['numeric-three'] = <<<EOC
<pre style="font-size: 16px">&lt;&lt;1[<em>none</em>/<em>one</em>/<em>some</em>]&gt;&gt;</pre><strong>onde 1 pode ser outro número</strong>
<p>Este código é usado para mostrar números juntamente com palavras.</p>
<p>Se \$d está dentro do código, um número será considerado como argumento e será mostrado. Se o número for 0, palavras escritas no slot <em>none</em> serão mostradas. Se o número for 1, palavras escritas no slot <em>one</em> serão mostradas. Se o número for maior que 1, o mantenedor <em>some</em> será mostrado.</p>
<p><u>Transcrição</u></p>
<p>Esse código será transcrito da exata forma, com exceção dos slots. Eles precisam ser traduzidos.</p>
<p><u>Exemplo</u></p>
<p><em>&lt;&lt;1[0 seconds/1 second/\$d seconds]&gt;&gt;</em><br />Tradução: <strong>&lt;&lt;1[0 segundos/1 segundo/\$d segundos]&gt;&gt;</strong></p>
EOC;

$GLOBALS['codes']['suffix'] = <<<EOC
<pre style="font-size: 16px">Códigos ^M ^F ^N ^m ^f ^n ^s ^p ^d ^a ^z</pre>
<p>Códigos começando com acento circunflexo são, geralmente, marcadores de número e gênero. O jogo interpreta os códigos como a seguir para inglês, francês e alemão:<br />
<ul>
   <li><strong>^M</strong>, <strong>^F</strong> y <strong>^N</strong>: a primeira palavra da expressão é um nome próprio masculino, feminino ou neutro, respectivamente.</li>
   <li><strong>^m</strong>, <strong>^f</strong> y <strong>^n</strong>: a primeira palavra da expressão é um substantivo comum masculino, feminino ou neutro, respectivamente.</li>
   <li><strong>^s</strong> y <strong>^p</strong>: a primeira palavra da expressão é singular ou plural, respectivamente. O código ^s é opcional. Ele apenas é usado para impedir a pluralização de substantivos não quantificados.</li>
   <li><strong>^d</strong>: o artigo definido sempre será colocado antes da expressão.</li>
   <li><strong>^a</strong> y <strong>^z</strong>: a expressão será colocada antes e depois de outra expressão, respectivamente.</li>
</ul>   
</p>
<p><u>Transcrição</u></p>
<p>Esses códigos não podem ser transcritos. Eles devem ser adaptados à língua Portuguesa através de análise morfológica a fim de conseguir o gênero e o número da primeira palavra traduzida. Uma vez feito isso, um dos seguintes códigos deve ser aplicado:</p>
<ul>
   <li><strong>^M</strong> se a primeira palavra for um nome próprio masculino: <em>Olaf Olho-Único^M</em></li>
   <li><strong>^F</strong> se a primeira palavra for um nome próprio feminino: <em>Aela^F</em></li>
   <li><strong>^m</strong> se a primeira palavra for um substantivo singular comum masculino: <em>machado de guerra^m</em></li>
   <li><strong>^ms</strong> similar ao ^f. Isso também evita a pluralização do nome: <em>ouro^ms</em></li>
   <li><strong>^f</strong> se a primeira palavra for um substantivo singular comum feminino: <em>granada polida^f</em></li>
   <li><strong>^fs</strong> similar ao ^f. Isso também evita a pluralização do nome: <em>água^fs</em></li>
   <li><strong>^np</strong> e a primeira palavra for um substantivo plural comum masculino: <em>braceletes^np</em></li>
   <li><strong>^pf</strong> se a primeira palavra for um substantivo plural comum feminino: <em>grevas^pf</em></li>
   <li><strong>^d</strong> y <strong>^a</strong> não são usados no português.</li>     
   <li><strong>^z</strong> será usado para o código em francês. O código ^z não é compatível com gênero/número, exemplo: <em>de ferro^z</em></li>
</ul>
EOC;

