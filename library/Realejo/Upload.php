<?php
/**
 * Model para recuperar as pastas de upload e verificar se elas possuem as permissões necessárias
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo;

class Upload
{
    /**
     * Retorna a pasta de upload para o model baseado no nome da classe
     * Se a pasta não existir ela será criada
     *
     * @param string $path Nome da classe a ser usada
     *
     * @return string
     */
    static public function getUploadPath($path = '')
    {
        // Define a pasta de upload
        $path = self::getUploadRoot() . '/' . str_replace('_', '/', strtolower($path));

        // Verifica se a pasta do cache existe
        if (!file_exists($path)) {
            $oldumask = umask(0);
            mkdir($path, 0777, true);
            umask($oldumask);
        }

        // Retorna a pasta de upload
        return $path;
    }

    /**
     * Retorna a pasta de visualizacao para o model baseado no nome da classe
     * Se a pasta não existir ela será criada
     *
     * @param string $path Nome da classe a ser usada
     *
     * @return string
     */
    static public function getAssetsReservedPath($path = '')
    {
        // Define a pasta de upload
        $path = self::getAssetsReservedRoot() . '/' . str_replace('_', '/', strtolower($path));

        // Verifica se a pasta do cache existe
        if (!file_exists($path)) {
            $oldumask = umask(0);
            mkdir($path, 0777, true);
            umask($oldumask);
        }

        // Retorna a pasta de upload
        return $path;
    }

    /**
     * Retorna a pasta de visualizacao para o model baseado no nome da classe
     * Se a pasta não existir ela será criada
     *
     * @param string $path Nome da classe a ser usada
     *
     * @return string
     */
    static public function getAssetsPublicPath($path = '')
    {
        // Define a pasta de upload
        $path = self::getAssetsPublicRoot() . '/' . str_replace('_', '/', strtolower($path));

        // Verifica se a pasta do cache existe
        if (!file_exists($path)) {
            $oldumask = umask(0);
            mkdir($path, 0777, true);
            umask($oldumask);
        }

        // Retorna a pasta de upload
        return $path;
    }

    /**
     * Retorna a pasta raiz de todos os uploads
     *
     * @return string
     */
    static public function getUploadRoot()
    {
         // Verifica se a pasta de cache existe
         if ( !defined('APPLICATION_DATA')  || realpath(APPLICATION_DATA) == false )  {
             throw new \Exception('A pasta raiz do data não está definido em APPLICATION_DATA em Realejo\Upload::getUploadRoot()');
         }

         $path = APPLICATION_DATA . '/uploads';

         // Verifica se existe e se tem permissão de escrita
         if (!is_dir($path) || !is_writable($path) )  {
             throw new \Exception('A pasta raiz de upload data/uploads não existe ou não tem permissão de escrita em Realejo\Upload::getUploadRoot()');
         }

        // retorna a pasta raiz do cache
        return $path;
    }

    /**
     * Retorna a pasta raiz no data para gravar os arquivos enviados
     *
     * @return string
     */
    static public function getAssetsReservedRoot()
    {
        // Verifica se a pasta de upload existe
        if ( !defined('APPLICATION_DATA')  || realpath(APPLICATION_DATA) == false) {
            throw new \Exception('A pasta raiz do data não está definido em APPLICATION_DATA em Realejo\Upload::getAssetsReservedRoot()');
        }

        $path = APPLICATION_DATA . '/assets';

        // Verifica se existe e se tem permissão de escrita
        if (!is_dir($path) || !is_writable($path) )  {
            throw new \Exception('A pasta raiz de upload data/assets não existe ou não tem permissão de escrita em Realejo\Upload::getUploadRoot()');
        }

        // retorna a pasta raiz do cache
        return $path;
    }

    /**
     * Retorna a pasta raiz no public para gravar os arquivos enviados
     *
     * @return string
     */
    static public function getAssetsPublicRoot()
    {
        // Verifica se a pasta de upload existe
        if ( !defined('APPLICATION_HTTP')  || realpath(APPLICATION_HTTP) == false) {
            throw new \Exception('A pasta raiz do site não está definido em APPLICATION_HTTP em Realejo\Upload::getAssetsPublicRoot()');
        }

        $path = APPLICATION_HTTP . '/assets';

        // Verifica se existe e se tem permissão de escrita
        if (!is_dir($path) || !is_writable($path) )  {
            throw new \Exception("A pasta raiz de upload site/assets não existe ou não tem permissão de escrita em Realejo\Upload::getUploadRoot()'");
        }

        // retorna a pasta raiz do cache
        return $path;
    }
}