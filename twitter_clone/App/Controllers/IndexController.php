<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action; //abstração do controlador
use MF\Model\Container; //instanciador de modelos

class IndexController extends Action {

	public function index() {

		//erro de autenticação, se o erro=login estiver na url
		//se get login for um parametro definido, ou seja, se estivar na url, nos vamos receber o valor que ele passa, que no caso de erro, é o login=erro dele. (passado no authcontroler)
		//senao tiver na url, o valor continua vazio.
		$this->view->login = isset($_GET['login']) ? $_GET['login'] : '';


		$this->render('index');
	}

	//abrir a pagina increverse
	public function inscreverse() {

		//isso só foi declarado aqui como valores vazio, pq usamos essa mesma instrução la no else, caso desse erro no preenchimento, entao manteria os mesmos valores ja digitado
		//mas causaria um bug se acessacemos a tela diretamente via url, entao declamos os valores vazios
		$this->view->usuario = array(
				'nome' => '',
				'email' => '',
				'senha' => '',
			);

		//declaramos a principio como falso, pra poder virar pra true no else, caso ocorra erros de preenchimento
		$this->view->erroCadastro = false;

		//renderiza o inscreverse
		$this->render('inscreverse');
	}

	//
	public function registrar() {

		//receber os dados do formulario
		//criar uma variavel e atribuir a classe container, que foi importada do framework, que ja faz a conexao com o db
		//o metodo getmodel, passa uma string, fazendo a instancia do usuario com o db
		$usuario = Container::getModel('Usuario');
		//pegar o objeto estanciado e setar os atributos, com o valor recebidos via post
		$usuario->__set('nome', $_POST['nome']);
		$usuario->__set('email', $_POST['email']);
		//md5, funcao nativa para esconder senha em hash de 32caracteres
		$usuario->__set('senha', md5($_POST['senha'])); 

		//executar metodo get usuario que tem como objetivo ver se o usuario ja nao ta com email no banco
		//contando, se a quantidd no bnco for 0, ele executa o salvar
		if($usuario->validarCadastro() && count($usuario->getUsuarioPorEmail()) == 0) {
					
			//executar o metodo salvar
			$usuario->salvar();
			//renderizar a view cadastro
			$this->render('cadastro');
			

		} else { //caso dê erro nos formularios, algum campo nao atender o que pede

			//manter os valores dos campos para que o usuario nao tenha que digitar novamente
			//associar ao objeto view, um atributo chamado usuario, que vai receber um array, que vai ter os valores dos campos
			$this->view->usuario = array(
				'nome' => $_POST['nome'],
				'email' => $_POST['email'],
				'senha' => $_POST['senha'],
			);

			//mudar o estado, que ja havia sido coloado como false la em cima
			$this->view->erroCadastro = true;
			//renderizar a view increverse
			$this->render('inscreverse');
		}

		


		//sucesso


		//erro
	}

}


?>