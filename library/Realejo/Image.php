<?php
/**
 * Classe com funções comuns para tratamento de imagens
 *
 * @link      http://github.com/realejo/libraray-zf1
 * @copyright Copyright (c) 2011-2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo;

class Image
{
    /**
     * Imagem carregada
     * @var binary image
     */
    protected  $_image = null;

    /**
     * Mime types válidos
     * @var array
     */
    protected $_fileTypes = array(
        'image/gif',
        'image/jpeg',
        'image/png'
    );

    /**
     * Compressão padrão das imagens
     * @var array
     */
    protected $_imageQuality = array(
        'png'   =>  9,
        'jpeg'  =>  100,
        'gif'   =>  100
    );

    /**
     * Path original da imagem
     * @var string
     */
    private $_path;

    /**
     * Tipo imagem
     * @var string
     */
    public $mimeType;

    /**
     * Carrega aimagem ao criar a classe
     *
     * @param string $image Endereço da imagem
     */
    public function __construct($image = null)
    {
        if (!is_null($image)) $this->open($image);
    }

    /**
     * Verifica se é uma imagem válida abrindo com função correta
     * @todo deve criar a imagem quando não existir
     *
     * @param string $arquivo
     * @return bool
     */
    public function open($file)
    {
        $this->close();
        // Verifica se o arquivo existe
        if (!file_exists($file)) {
            //throw new Exception("Arquivo $file não existe");
            return false;
        }

        /**
         * Tenta identificar o formato e abrir a imagem
         */
        switch (exif_imagetype($file)){
        	case IMAGETYPE_JPEG:
		        $im = imagecreatefromjpeg($file);
				if ($im !== false) {
					$this->mimeType = 'jpeg';
					$this->_image 	= $im;
					$this->_path 	= $file;
					return true;
				}
		        break;

		     case IMAGETYPE_GIF:
		        $im = imagecreatefromgif($file);
				if ($im !== false) {
					$this->mimeType = 'gif';
					$this->_image 	= $im;
					$this->_path 	= $file;
					return true;
				}
		        break;

	        case IMAGETYPE_PNG:
	        	$im = imagecreatefrompng($file);
				if ($im !== false) {
					$this->mimeType = 'png';
					$this->_image 	= $im;
					$this->_path 	= $file;
					return true;
				}
				break;
        }

        // Tipo não identificado ou não valido
        return false;
    }

    /**
     * Retira a imagem da memória
     * @return void
     */
    public function close()
    {
        // Verifica se a imagem está definida
        if ( isset ($this->_image)) {
            if ( is_resource($this->_image) ) {
                imagedestroy($this->_image);
            }
            $this->_image = null;
            return true;
        }

        return false;
    }

    /**
     * Verifica se tem alguma imagem carregada
     * @return boolean
     */
    public function isLoaded()
    {
        return !is_null($this->_image);
    }

    /**
     * Salva a imagem que está carregada na memória
     *
     * @param string  $file  Endereço do arquivo para salvar a imagem
     * @param boolean $close Fecha o arquivo ou mantem na memória
     * @return boolean
     */
    public function save($file = null, $close = false)
    {
        // Verifica se tem imagem carregada
        if (!$this->isLoaded()) {
            throw new Exception('Imagem não carregada em RW_Image::save();');
        }

        if ($file === true) {
            $file  = null;
            $close = true;
        }

        if (is_null($file)) $file = $this->_path;

        // Salva a transparencia (alpha channel) dos PNGs
        if ($this->mimeType == 'png')
            imagesavealpha( $this->_image, true );

        // Define a função de acordo com o file type
        $imageFunction = "image" . $this->mimeType;

        // Salva a imagem
        $ok = $imageFunction($this->_image, $file, $this->_imageQuality[$this->mimeType]);

        // Verifica se deve fechar
        if ($close && $ok) $this->close();

        return $ok;
    }

    /**
     * Salva a imagem que está carregada na memória
     *
     * @param string $file
     * @param boolean $close fecha o arquivo ou mantem na memoria
     *
     * @codeCoverageIgnore
     */
    public function sendScreen($close = true)
    {
        // Verifica se tem imagem carregada
        if (!$this->isLoaded()) {
            throw new Exception('Imagem não carregada em RW_Image::sendScreen();');
        }

       // @codeCoverageIgnoreStart
       // Define o header de acordo com o file type
       header('Content-Type: image/'. $this->mimeType);
       // @codeCoverageIgnoreEnd

        // Salva a transparencia (alpha channel) dos PNGs
        if ($this->mimeType == 'png') imagesavealpha( $this->_image, true );

        // Define a função de acordo com o file type
        $imageFunction = "image" . $this->mimeType;

        // @codeCoverageIgnoreStart
        // Envia para o browser
        $imageFunction($this->_image);
        // @codeCoverageIgnoreEnd

        // fecha o arquivo
        if ($close) $this->close();


        return true;
    }

    /**
     * Redimenciona a imagem. Retorna se a imagem foi redimensionada
     *
     * @param int $w largura da imagem
     * @param int $h altura da imagem
     * @param boolean $crop idica se a imagem deve se cortada para o tamanho
     * @param boolean $force aumenta a imagem caso ela seja menor
     *
     * @return boolean
     */
    public function resize($w, $h, $crop = false, $force = false)
    {
        // Verifica se tem imagem carregada
        if (!$this->isLoaded()) {
            throw new Exception('Imagem não carregada em RW_Image::resize();');
        }

        // Recupera os tamanhos da imagem
        $newwidth  = $width  = imagesx($this->_image);
        $newheight = $height = imagesy($this->_image);

        // Verifica se é para fazer o crop
        if ($crop) {

            // Redimenciona a imagem se necessário
            if ( ($width > $w) || ($height > $h) || $force ) {

                // Calcula o novo tamanho
                if ( ($width/$w) > ($height/$h) ) {
                    $newheight = $h;
                    $newwidth = ($width * $h) / $height;
                } else {
                    $newwidth = $w;
                    $newheight = ($height * $w) / $width;
                }

                // Cria a imagem temporária
                $tmp = imagecreatetruecolor($newwidth, $newheight);

                // Verifica se é um PNG para manter a transparencia
                if ($this->mimeType == 'png') {
                    imagealphablending($tmp, false);
                    imagesavealpha($tmp, true);
                    imagealphablending($this->_image, true);
                }

                // Redimenciona
                imagecopyresampled($tmp, $this->_image, 0,0, 0,0, $newwidth,$newheight, $width,$height);

                // Destroi a imagem original
                imagedestroy($this->_image);

                // Passa a usar a imagem temporaria
                $this->_image = $tmp;
            }

            /**
             * FAZ O CROP
             */

            // Cria a imagem temporária
            $tmp = imagecreatetruecolor($w, $h);

            // Verifica se é um PNG para manter a transparencia
            if ($this->mimeType == 'png') {
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                imagealphablending($this->_image, true);
            }

            // Define o tamanho
            $x = ($newwidth>$w)  ? $newwidth/2  - $w/2 : 0;
            $y = ($newheight>$h) ? $newheight/2 - $h/2 : 0;

            // Faz o crop
            imagecopyresampled( $tmp, $this->_image, 0,0, $x,$y, $w,$h, $w,$h );

            // Destroi a imagem original
            imagedestroy($this->_image);

            // Passa a usar a imagem temporaria
            $this->_image = $tmp;

            // Finaliza com sucesso
            return true;

        } else {

            // Define os novos tamanhos
            if ( ($width > $w) || ($height > $h) || $force  ) {
                if ( ($width/$w) > ($height/$h) ) {
                    $newwidth = $w;
                    $newheight = round(($height * $w) / $width);
                } else {
                    $newheight = $h;
                    $newwidth = round(($width * $h) / $height);
                }
            }

            // Verifica se o tamamnho mudou
            if ( ($newheight != $height) || ($newwidth != $width)) {

                // Cria a imagem temporária
                $tmp = imagecreatetruecolor($newwidth, $newheight);

                // Verifica se é um PNG para manter a transparencia
                if ($this->mimeType == 'png') {
                    imagealphablending($tmp, false);
                    imagesavealpha($tmp, true);
                    imagealphablending($this->_image, true);
                }

                // Faz o redimencionamento
                imagecopyresampled($tmp, $this->_image, 0,0, 0,0, $newwidth,$newheight, $width,$height);

                // Destroi a imagem original
                imagedestroy($this->_image);

                // Passa a usar a imagem temporaria
                $this->_image = $tmp;

                // Finaliza com sucesso
                return true;
            }
        }
        return false;
    }

    /**
     * Remove as informações extra das imagens (EXIF)
     * Para isso ele redimenciona para o mesmo tamanho pois não copia o EXIF
     */
    public function removeMetadata()
    {
        // Verifica se tem imagem carregada
        if (!$this->isLoaded()) {
            throw new Exception('Imagem não carregada em RW_Image::removeMetadata();');
        }

        $width  = imagesx($this->_image);
        $height = imagesy($this->_image);
        $this->resize($width, $height, false, true);
        return true;
    }

    /**
     * Altera a qualidade padrão das imagens (100%).
     *
     * @param int    $quality Qualidade da imagem de 0 a 100
     * @param string $format  OPCIONAL Formato a ser definido a nova qualidade (png, jpg ou gif)
     *
     * @return RW_Image
     */
    public function setImageQuality($quality, $format = null)
    {
        // Passa o formato para minusculo se existir
        if (!is_null($format)) $format = strtolower($format);

        // Verifica se foi informado um formato específico
        if (!is_null($format)) {
            // Verifica se o formato é valido
            if (array_key_exists($format, $this->_imageQuality)) {
                if ($format === 'png') $quality /= 10;
                $this->_imageQuality = $quality;
            } else {
                throw new Exception("Formato de imagem $format inválido");
            }

        // Altera todos os formatos
        } else {
           $this->_imageQuality['jpg'] = $quality;
           $this->_imageQuality['gif'] = $quality;
           $this->_imageQuality['png'] = $quality/10;
        }

        // Mantem a cadeia
        return $this;
    }
}
