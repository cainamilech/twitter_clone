<?php

//defenir o namespace, constituido pelos diretorios onde o script está
namespace App\Models;

//class usuario extendida pelo arquivo vendor/MF/model, classe model, que está fazendo a conexao com o banco no framework, facilitando a codificação
use MF\Model\Model;

class Usuario extends Model {

	//criar atributos privados, para representar as colunas dos registros no DB
	private $id;
	private $nome;
	private $email;
	private $senha;

	//como os metodos sao privados, precisamos dos metodos magicos para manipular os atributos do objeto
	public function __get($atributo) {
		return $this->$atributo;
	}

	public function __set($atributo, $valor) {
		$this->$atributo = $valor;
	}

	//metodo: salvar
	public function salvar() {

		//insert, definindo parametros aos seus valores
		//substituir o parametro nome, pelo atributo nome do objeto
		//la no model do framework já está passando o PDO pro $db
		//entao recuperamos usando this e executamos o metodo prepare, passando a query
		$query = "insert into usuarios(nome, email, senha)values(:nome, :email, :senha)";
		$stmt = $this->db->prepare($query);		
		$stmt->bindValue(':nome', $this->__get('nome'));
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->bindValue(':senha', $this->__get('senha'));
		$stmt->execute();

		return $this;
	}

	//metodo: validar se o cadastro pode ser feito
	public function validarCadastro() {
		$valido = true;

		//recuperar o nome, e o srtlen e ver se é inferior a 3 caracteres e muda o estado de $valido
		//strlen retorna o numero de caracteres
		if(strlen($this->__get('nome')) < 3){
			$valido = false;
		}

		if(strlen($this->__get('email')) < 3){
			$valido = false;
		}

		if(strlen($this->__get('senha')) < 3){
			$valido = false;
		}

		return $valido;
	}

	//metodo: recuperar um usuario por email
	public function getUsuarioPorEmail() {
		//
		$query = "select nome, email from usuarios where email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();

		//retornar um array, associativo
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	//metodo para autenticar
	public function autenticar(){

		//consulta o banco
		$query = "select id, nome, email from usuarios where email = :email and senha = :senha";
		//usar prepare para iniciar a consulta
		//quando chamar o metodo autenticar no authcontroler, os atributos foram recebidos, entao recuperamos eles aqui via __get
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->bindValue(':senha', $this->__get('senha'));
		$stmt->execute();

		//retornar um array associativo
		$usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

		//se no usuario tiver o indice ID, se for diferente de vazio, e se nome for diferente de vazio
		if(!empty($usuario['id']) && !empty($usuario['nome'])){
			//seta o usuario, (atributo id, usuario id)
			$this->__set('id', $usuario['id']);
			$this->__set('nome', $usuario['nome']);
		}
		return $this;
	}

	//metodo criado para auxiliar na pesquisa dos usuarios da route quemseguir
	public function getAll() {

		//pesquisar nos nomes, o nome recebido por parametro no get
		//o like é pq pode achar algo sem ser exatamente identico, tipo pesquisar caina, e achar cainan
		//and o id nao pode ser igual ao id do usuario autenticado. (para nao achar eu mesmo na pesquisa)
		//fazer uma subconsulta, para cada um dos registros, para descobrir se algum id usuario da tabela usuariosseguidos, é igual ao idusuario da sessao,
		//fizemos um count disso, pq se for 1 é pq ja segue, se for 0 nao segue. para ter como esconder o botao de seguir, se ja estiver seguindo. la na view
		$query = "
		select 
			u.id, u.nome, u.email,

			(select
				count(*)
			from
				usuarios_seguidores as us
			where
				us.id_usuario = :id_usuario and id_usuario_seguindo = u.id
			) as seguindo_sn 

		from 
			usuarios as u
		where
			nome like :nome and id != :id_usuario
		";
		$stmt = $this->db->prepare($query);
		//como usamos o like, concatenar % que é que pode ter qualquer coisa a esquerda ou direita
		$stmt->bindValue(':nome', '%'.$this->__get('nome').'%');
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->execute();

		//retornar a pesquisa, atribuindo a um array
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	//METODO PARA A /acao de seguir usuarios do appcontroler, que recebe um parametro idusuarioseguindo
	public function seguirUsuario($id_usuario_seguindo){
		//inserir informacoes na tabela usuarios_seguidores
		$query = "insert into usuarios_seguidores(id_usuario, id_usuario_seguindo)values(:id_usuario, :id_usuario_seguindo)";

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
		$stmt->execute();

		//return true para insercao
		return true;
	}

	//METODO PARA A /acao de deixar de seguir usuarios do appcontroler, que recebe um parametro idusuarioseguindo
	public function deixarSeguirUsuario($id_usuario_seguindo){
		//deletar informacoes na tabela usuarios_seguidores
		$query = "delete from usuarios_seguidores where id_usuario = :id_usuario and id_usuario_seguindo = :id_usuario_seguindo";

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
		$stmt->execute();

		//return true para deletar
		return true;
	}

	//METODOS PARA RENDERIZAR INFORMAÇÕES NA TELA DO PERFIL MOSTRADO NA VIEW
	//informações do usuario
	public function getInfoUsuario(){

		$query = "select nome from usuarios where id = :id_usuario";
		$stmt = $this->db->prepare($query);
		//associar ao atributo id, que ja esta recebendo o id do usuario da sessao
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->execute();

		//retornar fetch (o unico registro esperado pela consulta)
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	//total de tweets
	public function getTotalTweets(){

		$query = "select count(*) as total_tweet from tweets where id_usuario = :id_usuario";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->execute();

		//retornar fetch (o unico registro esperado pela consulta)
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	//total de usuarios que o usuario da sessao esta seguindo
	public function getTotalSeguindo(){

		$query = "select count(*) as total_seguindo from usuarios_seguidores where id_usuario = :id_usuario";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->execute();

		//retornar fetch (o unico registro esperado pela consulta)
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	//total de seguidores
	public function getTotalSeguidores(){

		$query = "select count(*) as total_seguidores from usuarios_seguidores where id_usuario_seguindo = :id_usuario";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id'));
		$stmt->execute();

		//retornar fetch (o unico registro esperado pela consulta)
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

}

?>