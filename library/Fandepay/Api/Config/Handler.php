<?php
namespace Fandepay\Api\Config;

class Handler extends \ArrayObject
{
    public function __construct()
    {
        //$this->loadConfig(APPLICATION_PATH.'/configs/application.ini', 'fandepay');
	    $this->loadConfig(__DIR__.DIRECTORY_SEPARATOR.'default.php');
    }

    /**
     * Konfigurációs fájl betöltése
     * @param string $path
     * @param string $section Ha a config fájlban más is van, melyik kulcs alól szedje az infokat?
     * @throws \InvalidArgumentException
     * @return array
     */
    public function loadConfig($path, $section = null)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);

        if (!is_readable($path)) {
            throw new \InvalidArgumentException('A config fajl nem letezik, vagy nincs az olvasasahoz megfelelo jogosultsag: ' . $path);
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'php':
                $data = require $path;
                break;
            case 'ini':
                $data = parse_ini_file($path, !is_null($section));
                break;
            case 'yml':
                if (!class_exists('Symfony\Component\Yaml\Yaml')) {
                    throw new \InvalidArgumentException('yml tipusu config fajl betoltesehez szukseg van a "symfony/yaml" komponensre');
                }
                $data = \Symfony\Component\Yaml\Yaml::parse($path);
                break;
            default:
                throw new \InvalidArgumentException('Nem tamogatott config fajl: ' . $path);
        }

        if (!is_null($section)) {
            if (!array_key_exists($section, $data)) {
                throw new \InvalidArgumentException('A config fajlban nem letezik ez a kulcs: ' . $section);
            }
            $data = $data[$section];
        }

        $this->exchangeArray(array_merge($this->getArrayCopy(), $data));

        return $data;
    }

    /**
     * Több lehetőség beállítása egyszerre
     * @param array $data
     * @return \Fandepay\Api\Config\Handler
     */
    public function setArray(array $data)
    {
        foreach ($data as $key => $val) {
            $this[$key] = $val;
        }

        return $this;
    }
}
