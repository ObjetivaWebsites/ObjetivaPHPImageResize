<?

// FAZ THUMB DAS IMAGENS AUToMATICAMENTE E REDIMENSIONA
class Painel_Imagens{

	protected $_largura = 160;

	protected $_altura 	= 160;

	function __construct(){

		if(isset($_GET['l']) and isset($_GET['a'])){

			if($_GET['l'] > 7 and $_GET['a'] > 7){
				$this->_largura = $_GET['l'];
				$this->_altura 	= $_GET['a'];
			}

		}

	}

	protected function _file_url($url){
		$parts 		= parse_url($url);
		$path_parts	= array_map('rawurldecode', explode('/', $parts['path']));
		return $parts['scheme'].'://'.$parts['host'].implode('/', array_map('rawurlencode', $path_parts));

	}

	function set_config($config){
		$this->_config = $config;
	}

	function set_tamanho($l, $a){

		$this->_largura = $l;
		$this->_altura = $a;		

	}

	function verificaCriaThumb($destino, $ver, $sub_pasta, $a, $qualidade_jpg){

		// CRIA PASTA SE AINDA NÃO EXISTIR
		if(!is_dir($destino.$sub_pasta.'thumb')){
			mkdir($destino.$sub_pasta.'thumb');
		}

		// VERIFICA SE O THUMB EXISTE
		if(!is_file($destino.$sub_pasta.'thumb/'.$a)){
			$this->thumb($destino, $sub_pasta, $a, $qualidade_jpg);
		}

		return preg_replace(array('/.gif$/', '/.png$/'), '.jpg', $ver.$sub_pasta.'thumb/'.$a);

	}

	function thumb($destino, $sub_pasta, $a, $qualidade_jpg){

		$getimagem 			= file_get_contents($destino.$sub_pasta.$a);

		$imagem 			= imagecreatefromstring($getimagem);

		$larguraOriginal	= imagesx($imagem);
		$alturaOriginal		= imagesY($imagem);

		$redimensionarLargura	= $this->_largura;
		$redimensionarAltura	= $this->_altura;

		if($imagem === false){

			header('Content-type: image/jpg');
			echo $getimagem;
			exit();

		}

		//PAISAGEM

		if($larguraOriginal > $alturaOriginal){

			$proporcao			= $larguraOriginal / $alturaOriginal;
			$corteLargura		= (int) $redimensionarLargura * $proporcao;
			$corteAltura		= $redimensionarAltura;		
			$moverx				= ((int)(($corteLargura-$redimensionarLargura) / 2)) * -1;
			$meverY				= 0;

		}

		//RETRATO
		if($larguraOriginal < $alturaOriginal){

			$proporcao			= $alturaOriginal / $larguraOriginal;
			$corteLargura		= $redimensionarLargura;
			$corteAltura		= (int) $redimensionarAltura * $proporcao;		
			$moverx				= 0;
			$meverY				= ((int)(($corteAltura-$redimensionarLargura) / 2)) * -1;
		}

		//QUADRADA
		if($larguraOriginal == $alturaOriginal){

			$corteLargura		= $redimensionarLargura;
			$corteAltura		= $redimensionarAltura;		
			$moverx				= 0;
			$meverY				= 0;

		}

		$image_p 			= imagecreatetruecolor($redimensionarLargura, $redimensionarAltura);
		$branco				= imagecolorallocate($image_p, 255, 255, 255);
		imagefill($image_p, 0, 0, $branco);
		imagecopyresampled($image_p, $imagem, $moverx, $meverY, 0, 0, $corteLargura, $corteAltura, $larguraOriginal, $alturaOriginal);
		imagejpeg($image_p, $destino.$sub_pasta.'thumb/'.preg_replace(array('/.gif$/', '/.png$/'), '.jpg', $a), $qualidade_jpg);
	}

	function proporcao($imagem, $largura, $altura){

		$retorna = array();

		list($larguraOriginal, $alturaOriginal) = getimagesize($imagem);

		// NÃO REDIMENSIONA IMAGENS PEGUENAS
		if($larguraOriginal < $largura and $alturaOriginal < $altura){
			return false;
		}

		$proporcao		= $larguraOriginal / $alturaOriginal;
		$novaProporcao	= $largura / $altura;

		if($proporcao > $novaProporcao){

			$novaLargura	= $largura;
			$novaAltura		= (int) ($largura / $proporcao);

		}elseif($proporcao < $novaProporcao){

			$novaLargura	= (int) ($altura * $proporcao);
			$novaAltura		= $altura;

		}else{

			$novaLargura	= $largura;
			$novaAltura		= $altura;
		}

		$retorna['novaLargura'] = $novaLargura;
		$retorna['novaAltura'] = $novaAltura;

		return $retorna;
	}

	function redimensiona($nome_imagem, $mime, $qualidade = 85){

		list($larguraOriginal, $alturaOriginal) = getimagesize($nome_imagem);

		$retorna 		= $this->proporcao($nome_imagem, $this->_largura, $this->_altura);

		// SE PRECISAR REDIMENSIONAR
		if(is_array($retorna)){

			$novaLargura 	= $retorna['novaLargura'];

			$novaAltura 	= $retorna['novaAltura'];

			if($mime == 'image/jpeg'){
				$source 	= imagecreatefromjpeg($nome_imagem);
			}

			if($mime == 'image/png'){
				$source 	= imagecreatefrompng($nome_imagem);
			}

			if($mime == 'image/gif'){
				$source 	= imagecreatefromgif($nome_imagem);
			}


			$temp = imagecreatetruecolor($novaLargura, $novaAltura);
			imagealphablending($temp, false);
			imagesavealpha($temp, true);
			imagecopyresampled($temp, $source, 0, 0, 0, 0, $novaLargura, $novaAltura, $larguraOriginal, $alturaOriginal);

			if($mime == 'image/jpeg'){
				imagejpeg($temp, $nome_imagem, $this->_config['qualidade']);
			}

			if($mime == 'image/png'){
				imagepng($temp, $nome_imagem);
			}

			if($mime == 'image/gif'){
				imagegif($temp, $nome_imagem);
			}

			imagedestroy($temp);

		}

	}

}
