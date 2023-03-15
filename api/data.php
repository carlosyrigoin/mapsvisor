<?php
	class Agroat{
	  	function db() {
		  	$username = "root"; $password = "";
			try {
			  	$conn = new PDO("mysql:host=localhost;dbname=agroat;charset=utf8", $username, $password);
			  	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			  	return $conn;
			}catch(PDOException $e) {
			  	echo "Connection failed: " . $e->getMessage();
			}
	  	}

	  	function get_users(){
	  		$conn = $this->db();
	  		$sql = "select * from usuario where usu_estado=1 order by usu_nombres";
			$results = $conn->query($sql)->fetchAll(); $conn = null;

			return $results;
	  	}

	  	function get_provinces($pro_dep_id){
	  		$conn = $this->db();
	  		$sql = "select * from provincia where pro_dep_id=".$pro_dep_id." order by pro_nombre";
			$results = $conn->query($sql)->fetchAll(); $conn = null;

			return $results;
	  	}

	  	function get_districts($dis_pro_id){
	  		$conn = $this->db();
	  		$sql = "select * from distrito where dis_pro_id=".$dis_pro_id." order by dis_nombre";
			$results = $conn->query($sql)->fetchAll(); $conn = null;

			return $results;
	  	}

	  	function get_agricultural_census($user, $state, $province, $district, $from, $to){
	  		$conn = $this->db(); $filter = "";
	  		if($user != ""){
	  			$filter .= "ca.cag_usu_id=".$user." and ";
	  		}
	  		if($state != "" && $state != "-"){
	  			$filter .= "ca.cag_estado=".$state." and ";
	  		}
	  		if($district != ""){
	  			$filter .= "ca.cag_dis_id_ubicacion=".$district." and ";
	  		}else{
	  			if($province != ""){
	  				$filter .= "ca.cag_dis_id_ubicacion in (select dis_id from distrito where dis_pro_id=".$province.") and ";
	  			}
	  		}
	  		if($from != ""){
	  			$filter .= "DATE_FORMAT(ca.cag_fecha_visita,'%Y-%m-%d')>='".$from."' and ";
	  		}
	  		if($to != ""){
	  			$filter .= "DATE_FORMAT(ca.cag_fecha_visita,'%Y-%m-%d')<='".$to."' and ";
	  		}
	  		
	  		$sql = "select cag_nombre as 'nombres', cag_fecha_visita as 'fecha', cag_sync_up_date as 'sincronizado', cag_localidad as 'localidad', cag_sector as 'sector', cag_natural_de as 'natural', cag_longitud as 'longitud', cag_latitud as 'latitud', cag_estado as 'estado', cag_foto as 'foto', pro_nombre as 'provincia', dis_nombre as 'distrito', u.usu_nombres as 'tecnico' from censo_agrario as ca inner join distrito as d on(ca.cag_dis_id_ubicacion = d.dis_id) inner join provincia as p on(d.dis_pro_id = p.pro_id) inner join usuario as u on(ca.cag_usu_id = u.usu_id) where ".$filter." ca.cag_id >= 0";
			$results = $conn->query($sql)->fetchAll(); $conn = null;

			return $results;
	  	}

	  	function get_crop_monitoring($user, $state, $province, $district, $from, $to){
	  		$conn = $this->db(); $filter = "";
	  		if($user != ""){
	  			$filter .= "mc.mc_usu_id=".$user." and ";
	  		}
	  		if($state != "" && $state != "-"){
	  			$filter .= "mc.mc_estado=".$state." and ";
	  		}
	  		if($district != ""){
	  			$filter .= "mc.mc_dis_id=".$district." and ";
	  		}else{
	  			if($province != ""){
	  				$filter .= "mc.mc_dis_id in (select dis_id from distrito where dis_pro_id=".$province.") and ";
	  			}
	  		}
	  		if($from != ""){
	  			$filter .= "DATE_FORMAT(mc.mc_fecha_siembra,'%Y-%m-%d')>='".$from."' and ";
	  		}
	  		if($to != ""){
	  			$filter .= "DATE_FORMAT(mc.mc_fecha_siembra,'%Y-%m-%d')<='".$to."' and ";
	  		}
	  		
	  		$sql = "select mc.mc_id as 'id', mc_nombre_productor as 'nombres', mc_create_date as 'fecha', mc_sync_up_date as 'sincronizado', mc_localidad as 'localidad', '' as 'sector', '' as 'natural', mc_longitud as 'longitud', mc_latitud as 'latitud', mc_estado as 'estado', '' as 'foto', pro_nombre as 'provincia', dis_nombre as 'distrito', u.usu_nombres as 'tecnico' from monitoreo_cultivo as mc inner join distrito as d on(mc.mc_dis_id = d.dis_id) inner join provincia as p on(d.dis_pro_id = p.pro_id) inner join usuario as u on(mc.mc_usu_id = u.usu_id) where ".$filter." mc.mc_id >= 0";
			$results = $conn->query($sql)->fetchAll();
			foreach ($results as $key => $value) {
				$sql = "select *from foto_mc where fot_mc_id=".$value["id"];
				$photos = $conn->query($sql)->fetchAll();
				if(count($photos) > 0){
					$results[$key]["foto"] = $photos[0]["fot_url"];
				}
			}
			$conn = null;

			return $results;
	  	}
	}

	if(isset($_GET["option"])){
		if($_GET["option"] == "users"){
			$agroat = new Agroat;
			$data = $agroat->get_users();

			echo json_encode($data);
		}

		if($_GET["option"] == "provinces"){
			$agroat = new Agroat;
			$data = $agroat->get_provinces($_GET["deparment"]);

			echo json_encode($data);
		}

		if($_GET["option"] == "districts"){
			$agroat = new Agroat;
			$data = $agroat->get_districts($_GET["province"]);

			echo json_encode($data);
		}

		if($_GET["option"] == "agricultural_census"){
			$agroat = new Agroat;
			$data = $agroat->get_agricultural_census($_GET["user"], $_GET["state"], $_GET["province"], $_GET["district"], $_GET["from"], $_GET["to"]);

			echo json_encode($data);
		}

		if($_GET["option"] == "crop_monitoring"){
			$agroat = new Agroat;
			$data = $agroat->get_crop_monitoring($_GET["user"], $_GET["state"], $_GET["province"], $_GET["district"], $_GET["from"], $_GET["to"]);

			echo json_encode($data);
		}
	}else{
		echo "Welcome to Api Rest AgroAt";
	}
?>