<?php

//defenir o namespace, constituido pelos diretorios onde o script está
namespace App\Models;

//class usuario extendida pelo arquivo vendor/MF/model, classe model, que está fazendo a conexao com o banco no framework, facilitando a codificação
use MF\Model\Model;

class Tweet extends Model {

	//criar atributos privados, para representar as colunas dos registros no DB
	private $id;
	private $id_usuario;
	private $tweet;
	private $data;

	//como os metodos sao privados, precisamos dos metodos magicos para manipular os atributos do objeto
	public function __get($atributo) {
		return $this->$atributo;
	}

	public function __set($atributo, $valor) {
		$this->$atributo = $valor;
	}
	
	//metodo salvar, que faz um insert do tweet no banco, com os dados necessarios conforme o appcontroler declarou
	public function salvar(){

		$query = "insert into tweets(id_usuario, tweet)value(:id_usuario, :tweet)";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id_usuario'));
		$stmt->bindValue(':tweet', $this->__get('tweet'));
		$stmt->execute();

		//retornar o proprio objeto
		return $this;

	}

	//metodo recuperar (o tweet), select dos dados da tabela tweet
	public function getAll() {

		//fazer left join, juntando id_usuario e o nome na table usuarios. para saber o nome quando mostrar o tweet	temos q fazer isso pq na table tweets nao tem nome, só id_usuario
		//o date_formate é nativo,formata a data,senao o padrao fica default(americano), definindo como as data(para poder utilizar no array)
		//order by desc, ordenando de forma decrescente, pela data

		//comentarios ate aqui, é pra recuperar tweets de usuarios autenticados, mas vamos incluir no where uma nova condicao
		//que confere a relação da table usuariosseguidores. se tem tweets de usuario seguindo
		$query = "
			select 
				t.id, t.id_usuario, u.nome, t.tweet, DATE_FORMAT(t.data, '%d/%m/%Y %H:%i') as data
			from
				tweets as t
				left join usuarios as u on (t.id_usuario = u.id)
			where
				id_usuario = :id_usuario
				or t.id_usuario in (select id_usuario_seguindo from usuarios_seguidores where id_usuario = :id_usuario)
			order by
				t.data desc

		"; //apelidei os tweets como T, usuario como U
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id_usuario'));
		$stmt->execute();

		//retornar um array com os tweets
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}
	//metodo para PAGINAÇÃO
	//metodo recuperar POR PAGINA, passando os atributos de limit e offset, select dos dados da tabela tweet
	public function getPorPagina($limit, $offset) {

		//fazer left join, juntando id_usuario e o nome na table usuarios. para saber o nome quando mostrar o tweet	temos q fazer isso pq na table tweets nao tem nome, só id_usuario
		//o date_formate é nativo,formata a data,senao o padrao fica default(americano), definindo como as data(para poder utilizar no array)
		//order by desc, ordenando de forma decrescente, pela data

		//comentarios ate aqui, é pra recuperar tweets de usuarios autenticados, mas vamos incluir no where uma nova condicao
		//que confere a relação da table usuariosseguidores. se tem tweets de usuario seguindo
		$query = "
			select 
				t.id, t.id_usuario, u.nome, t.tweet, DATE_FORMAT(t.data, '%d/%m/%Y %H:%i') as data
			from
				tweets as t
				left join usuarios as u on (t.id_usuario = u.id)
			where
				id_usuario = :id_usuario
				or t.id_usuario in (select id_usuario_seguindo from usuarios_seguidores where id_usuario = :id_usuario)
			order by
				t.data desc
			limit
				$limit
			offset
				$offset

		"; //apelidei os tweets como T, usuario como U
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id_usuario'));
		$stmt->execute();

		//retornar um array com os tweets
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

	//metodo para PAGINAÇÃO
	//metodo recuperar total de tweets, cout dos dados da tabela tweet
	public function getTotalRegistros() {

		$query = "
			select 
				count(*) as total
			from
				tweets as t
				left join usuarios as u on (t.id_usuario = u.id)
			where
				id_usuario = :id_usuario
				or t.id_usuario in (select id_usuario_seguindo from usuarios_seguidores where id_usuario = :id_usuario)
			
		"; //apelidei os tweets como T, usuario como U
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id_usuario', $this->__get('id_usuario'));
		$stmt->execute();

		//retornar o total de tweets da consulta
		return $stmt->fetch(\PDO::FETCH_ASSOC);

	}
}

?>