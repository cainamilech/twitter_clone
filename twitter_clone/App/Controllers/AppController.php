<?php

// namespace e use copiadas do index controler
namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action; //abstração do controlador
use MF\Model\Container; //instanciador de modelos

class AppController extends Action {

	//funcao que determina a action timeline
	public function timeline() {

		//testar se a autenticaçao do usuario está validada (a logica esta encapsulada em outra function abaixo (validaautenticação)) o session start tb ta aqui
		$this->validaAutenticacao();

		//recuperar os tweets que ja estao no banco
		//passar a classe do modelo que queremos instanciar,recebendo o objeto ja com a conexao com o banco, atribuindo a uma variavel
		$tweet = Container::getModel('Tweet');

		//passar um parametro para o objeto, a informação ID USUARIO, que é o id da sessao
		$tweet->__set('id_usuario', $_SESSION['id']);

		//executar getall, que é um array de tweets, atribuindo a uma nova variavel
		//FOI DESLOCADO PARA DEPOIS DAS VARIAVEIS DE PAGINAÇÃO, pra recuperar apenas um limite de tweets $tweets = $tweet->getAll();

		//criar atributo dinamico chamado tweets, que vai receber a variavel tweets, recuperados do getall
		//FOI DESLOCADO PARA DEPOIS DAS VARIAVEIS DE PAGINAÇÃO, pra recuperar apenas um limite de tweets $this->view->tweets = $tweets;

		//criar variaveis de PAGINAÇÃO dos tweets, pra nao ficar tudo na mesma pagina
		$total_registros_pagina = 10; //atributo limit
		//$deslocamento = 0; //atributo offset
		//se o indice pagina por get(url da view) estiver definida, atribuimos o valor recebido, senao, ela tem o valor 1.
		$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
		//logica para descobrir o deslocamento, ver qual pagina esta e fazer -1, e x10. deslocamento é o parametro offset do metodo getporpagina
		$deslocamento = ($pagina -1) * $total_registros_pagina;

		$tweets = $tweet->getPorPagina($total_registros_pagina, $deslocamento);
		$total_tweets = $tweet->getTotalRegistros();
		//variavel para saber qual o total de paginas, fazendo a divisao de total tweets por total de registros por pagina. (o ceil arredonda o resultado pra cima)
		$this->view->total_de_paginas = ceil($total_tweets['total'] / $total_registros_pagina);
		//criar mais uma variavel empurrada pra view, pra mostrar qual a pagina ativa
		$this->view->pagina_ativa = $pagina;

		$this->view->tweets = $tweets;


		
		//instancia feita para relacionar os metodos do getall no model que foram feitos com objetivo de fazer querys contando numero de coisas pra complementar o perfil na view
		//passar a classe do modelo que queremos instanciar,recebendo o objeto ja com a conexao com o banco, atribuindo a uma variavel
		$usuario = Container::getModel('Usuario');
		$usuario->__set('id', $_SESSION['id']);
		$this->view->info_usuario = $usuario->getInfoUsuario();
		$this->view->total_tweets = $usuario->getTotalTweets();
		$this->view->total_seguindo = $usuario->getTotalSeguindo();
		$this->view->total_seguidores = $usuario->getTotalSeguidores();


		//encaminhar para view timeline
		$this->render('timeline');


	}

	//funcao que determina a action tweet
	public function tweet() {

		//testar se a autenticaçao do usuario está validada (a logica esta encapsulada em outra function abaixo (validaautenticação)) o session start tb ta aqui
		$this->validaAutenticacao();
		
		//declarar qual o model que queremos trabalhar, essa função nativa ja recupera o objeto tweet ja com a conexao com o banco
		//atribuimos a uma variavel
		$tweet = Container::getModel('Tweet');

		//associar um valor recebido por POST ao atributo tweet
		$tweet->__set('tweet', $_POST['tweet']);

		//associar o ID USUARIO da sessao ao atributo tweet, para saber quem foi que tweetou
		$tweet->__set('id_usuario', $_SESSION['id']);

		//recuperar o objeto e salvar o registro no banco;
		//metodo salvar, criado no modelo
		$tweet->salvar();

		//recarregar a timeline
		header('Location: /timeline');

	}

	//metodo escapsulado, para eu poder usar em outros, para conferir se o usuario esta autenticado, para poder dar continuidade
	public function validaAutenticacao() {

		session_start();

		//senao tiver setado, ou for vazio, redirecionamos para index. passando login=erro
		//e tb caso a pessoa tente entrar na timeline direto via url
		if(!isset($_SESSION['id']) || $_SESSION['id'] == '' || !isset($_SESSION['nome']) || $_SESSION['nome'] == '') {
			header('Location: /?login=erro');
		}

	}

	//metodo, que vai renderizar a pagina quemseguir, com o objetivo de pesquisar por um nome no form, e quando disparado, buscar no banco, retornando na view os resultados
	public function quemSeguir() {

		//testar se a autenticaçao do usuario está validada (a logica esta encapsulada em outra function(validaautenticação)) o session start tb ta aqui
		$this->validaAutenticacao();

		//se o indice pesquisarPor _get (que é o name do input na view) estiver setado, vamos setar o valor recebido no proprio indice, caso contrario, será vazio
		$pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';

		//declarar por padrao que $usuarios é um array, pq senao dentrar no if a seguir, o array é apresentado vazio mesmo na view
		//assim a propria view sabera que nao foi encontrado nninguem
		$usuarios = array();

		//se essa variavel, que é a pesquisa, nao tiver vazio, vamos procurar no modelo usuarios(q ja existe)
		if ($pesquisarPor != '') {
			//declarar qual o model que queremos trabalhar, essa função nativa ja recupera o objeto tweet ja com a conexao com o banco
			//atribuimos a uma variavel
			$usuario = Container::getModel('Usuario');
			//setar o atributo nome com o valor recebido por get
			$usuario->__set('nome', $pesquisarPor);
			//PARA NAO PROCURAR PELO PROPRIO USUARIO, ex, eu caina pesquiso caina e me acho na lista,
			//setando o atributo id do objeto usuario, como sendo o id da sessao, depois tratar na query da função getAll no model usuario.php
			$usuario->__set('id', $_SESSION['id']);
			//executar o metodo getAll, que está la no model usuario.php trazendo um array pela query
			//atribuir a uma variavel
			$usuarios = $usuario->getAll();
		}

		//mostrar o array de usuarios encontrados para view
		$this->view->usuarios = $usuarios;

		//renderizar a view quemseguir
		$this->render('quemSeguir');
	}

	//metodo acao, que vai seguir ou parar de seguir um usuario
	public function acao(){

		//testar se a autenticaçao do usuario está validada (a logica esta encapsulada em outra function(validaautenticação)) o session start tb ta aqui
		$this->validaAutenticacao();

		//criar uma variavel com a acao e uma com o idusuarioseguindo (QUE QUEREMOS SEGUIR)
		//estamos passando esse id la na view por parametro via url (get)
		//apenas se esse indice existir, entao o valor sera atribuido, caso o contrario deixar vazio
		$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
		$id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

		//passar a classe do modelo que queremos instanciar,recebendo o objeto ja com a conexao com o banco, atribuindo a uma variavel
		$usuario = Container::getModel('Usuario');
		//setar o id, com o id da session
		$usuario->__set('id', $_SESSION['id']);

		//TOMAR UMA DECISAO
		if($acao == 'seguir'){
			//recuperar a instancia do objeto usuario e disparar o metodo seguir usuario passando o id do usuario que queremos seguir
			$usuario->seguirUsuario($id_usuario_seguindo);
		} else if($acao == 'deixar_de_seguir'){
			//recuperar a instancia do objeto usuario e disparar o metodo deixar de seguir usuario passando o id do usuario que queremos deixar de seguir
			$usuario->deixarSeguirUsuario($id_usuario_seguindo);
		}

		//apos o processo, atualizar a pagina
		header('Location: /quem_seguir');
	}
}

?>
