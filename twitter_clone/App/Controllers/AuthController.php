<?php

// namespace e use copiadas do index controler
namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action; //abstração do controlador
use MF\Model\Container; //instanciador de modelos

class AuthController extends Action {

	public function autenticar(){

		//atraves do container, instanciar um objeto do tipo usuario
		$usuario = Container::getModel('Usuario');
		//passar seus valores recebidos via post
		$usuario->__set('email', $_POST['email']);
		$usuario->__set('senha', md5($_POST['senha'])); //o md5 é nativo para conversao de senha, foi declarado na funcao registrar do indexcontroler, entao temos que colocar aqui tb.

		//metodo responsavel por checar no db se o usuario existe
		$usuario->autenticar();


		//logica para autorizar o usuario a entrar em rotas protegidas, que apenas usuarios registrados podem entrar
		if($usuario->__get('id') != '' && $usuario->__get('nome') != '') {
			
			//start na sessao pro usuario
			session_start();

			//setar a session com os indices id e nome
			$_SESSION['id'] = $usuario->__get('id');
			$_SESSION['nome'] = $usuario->__get('nome');

			//podemos redirecionar para uma pagina protegida
			header('Location: /timeline');



		} else {
			//senao existir, redirecionado para o index, passando um parametro na url, para mostrar o erro na view, (logia na fucition index() indexcontroler)
			header('Location: /?login=erro');
		}
		
	}

	//criar um metodo para sair.
	//onde tiver uma rota /sair, sera destruida a sessao e encaminhada para index
	public function sair() {

		session_start();
		session_destroy();
		header('Location: /');
	}

}


?>