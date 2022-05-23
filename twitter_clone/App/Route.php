<?php

namespace App;

use MF\Init\Bootstrap;

class Route extends Bootstrap {

	protected function initRoutes() {

		$routes['home'] = array(
			'route' => '/',
			'controller' => 'indexController',
			'action' => 'index'
		);

		//rota adicionada, ativando o index controler para disparar a action inscreverse
		$routes['inscreverse'] = array(
			'route' => '/inscreverse',
			'controller' => 'indexController',
			'action' => 'inscreverse'
		);

		//rota adicionada, ativando o index controler para disparar a action registrar
		$routes['registrar'] = array(
			'route' => '/registrar',
			'controller' => 'indexController',
			'action' => 'registrar'
		);

		//rota adicionada, ativando o authControler, controlador criado para essa autenticacao e para disparar a action autenticar
		$routes['autenticar'] = array(
			'route' => '/autenticar',
			'controller' => 'AuthController',
			'action' => 'autenticar'
		);

		//rota timeline adicionada, ativando o appControler, controlador criado para controlar as paginas restritas a usuarios e para disparar a action timeline
		$routes['timeline'] = array(
			'route' => '/timeline',
			'controller' => 'AppController',
			'action' => 'timeline'
		);

		//rota sair adicionada, disparar o controlador AuthControler, pois se trata de um processo de controle de usuario
		//chamar a action sair
		$routes['sair'] = array(
			'route' => '/sair',
			'controller' => 'AuthController',
			'action' => 'sair'
		);

		//rota tweet adicionada, disparar o controlador AppControler, pois se trata de uma action no app
		//chamar a action tweet
		$routes['tweet'] = array(
			'route' => '/tweet',
			'controller' => 'AppController',
			'action' => 'tweet'
		);

		//rota quem_seguir adicionada, disparar o controlador AppControler, pois se trata de uma action no app
		//chamar a action quemSeguir
		$routes['quem_seguir'] = array(
			'route' => '/quem_seguir',
			'controller' => 'AppController',
			'action' => 'quemSeguir'
		);

		//rota que manda para acao, que vai seguir ou parar de seguir um usuario
		//disparar no appcontroler, pq ta autenticado, e chama a action acao
		$routes['acao'] = array(
			'route' => '/acao',
			'controller' => 'AppController',
			'action' => 'acao'
		);


		$this->setRoutes($routes);
	}

}

?>