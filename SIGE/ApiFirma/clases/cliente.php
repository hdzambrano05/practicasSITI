<?php 
class cliente{
    private $id_cli;
    private $des_cli;
    private $tip_soli;
    private $query;
    private $aut;

    public function __construct($id_cli, $des_cli, $tip_soli, $query, $aut){
		$this->id_cli = $id_cli;
		$this->des_cli = $des_cli;
		$this->tip_soli = $tip_soli;
		$this->query = $query;
		$this->aut = $aut;
	}
    public static function recuperar($con, $id_cli,$tip_soli){
		
		$query = "SELECT * FROM cliente WHERE id_cli ='$id_cli' AND tip_soli='$tip_soli';";
		try{
			$res = $con->query($query);
			if($con->num_rows($res)==0){
                return NULL;
            } 
			$fila = $con->fetch($res);
			$id_cli = $fila['id_cli'];
			$des_cli = $fila['des_cli'];
			$tip_soli = $fila['tip_soli'];
			$query = $fila['query'];
			$aut = $fila['aut'];
			return new ususis($id_cli, $des_cli, $tip_soli, $query, $aut);
		}catch(Exception $e){
			throw($e);
			return NULL;
		}
	}

}

?>